<?php
class LoginTracker {
    private $db;
    private $logger;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->logger = Logger::getInstance();
    }

    public function generateDeviceFingerprint($userAgent, $ipAddress, $postData = []) {
        $fingerprintData = [
            'user_agent' => $userAgent,
            'ip_address' => $ipAddress,
            'screen_resolution' => $postData['screen_resolution'] ?? '',
            'timezone' => $postData['timezone'] ?? '',
            'language' => $postData['language'] ?? '',
            'platform' => $postData['platform'] ?? '',
            'cookies_enabled' => $postData['cookies_enabled'] ?? false,
            'do_not_track' => $postData['do_not_track'] ?? false
        ];

        $fingerprint = hash('sha256', json_encode($fingerprintData));
        return $fingerprint;
    }

    public function getLocationData($ipAddress) {
        try {
            $url = "http://ip-api.com/json/{$ipAddress}";
            $response = file_get_contents($url);
            
            if ($response === false) {
                return null;
            }

            $data = json_decode($response, true);
            
            if ($data && $data['status'] === 'success') {
                return [
                    'country' => $data['country'] ?? '',
                    'region' => $data['regionName'] ?? '',
                    'city' => $data['city'] ?? '',
                    'lat' => $data['lat'] ?? 0,
                    'lon' => $data['lon'] ?? 0,
                    'isp' => $data['isp'] ?? '',
                    'timezone' => $data['timezone'] ?? ''
                ];
            }
        } catch (Exception $e) {
            // Log error silently for now
        }

        return null;
    }

    public function recordLoginAttempt($email, $userAgent, $ipAddress, $deviceFingerprint, $locationData = null) {
        try {
            $data = [
                'email' => $email,
                'user_agent' => $userAgent,
                'ip_address' => $ipAddress,
                'device_fingerprint' => $deviceFingerprint,
                'location_data' => $locationData ? json_encode($locationData) : null,
                'success' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $attemptId = $this->db->insert('login_attempts', $data);
            return $attemptId;
        } catch (Exception $e) {
            return false;
        }
    }

    public function updateLoginAttempt($attemptId, $success, $failureReason = null, $mfaRequired = false, $sessionId = null, $userId = null) {
        try {
            $data = [
                'success' => $success ? 1 : 0,
                'mfa_required' => $mfaRequired ? 1 : 0,
                'session_id' => $sessionId,
                'user_id' => $userId,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->db->update('login_attempts', $data, 'id = ?', [$attemptId]);
        } catch (Exception $e) {
            // Log error silently
        }
    }

    public function isAccountLocked($email, $ipAddress, $deviceFingerprint) {
        try {
            $sql = "SELECT attempt_count FROM failed_login_attempts WHERE email = ? AND last_attempt_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)";
            $result = $this->db->fetch($sql, [$email]);
            $failedCount = $result['attempt_count'] ?? 0;

            if ($failedCount >= 5) {
                $lockUntil = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                return [
                    'locked' => true,
                    'locked_until' => $lockUntil,
                    'remaining_attempts' => 0
                ];
            }

            return [
                'locked' => false,
                'remaining_attempts' => 5 - $failedCount
            ];
        } catch (Exception $e) {
            return [
                'locked' => false,
                'remaining_attempts' => 5
            ];
        }
    }

    public function recordFailedAttempt($email, $ipAddress, $deviceFingerprint) {
        try {
            $sql = "INSERT INTO failed_login_attempts (email, ip_address, device_fingerprint, attempt_count, last_attempt_at) 
                    VALUES (?, ?, ?, 1, NOW()) 
                    ON DUPLICATE KEY UPDATE 
                    attempt_count = attempt_count + 1, 
                    last_attempt_at = NOW()";
            
            $this->db->query($sql, [$email, $ipAddress, $deviceFingerprint]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function clearFailedAttempts($email, $ipAddress, $deviceFingerprint) {
        try {
            $sql = "DELETE FROM failed_login_attempts WHERE email = ? AND ip_address = ? AND device_fingerprint = ?";
            $this->db->query($sql, [$email, $ipAddress, $deviceFingerprint]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function isTrustedDevice($userId, $deviceFingerprint) {
        try {
            $sql = "SELECT * FROM trusted_devices WHERE user_id = ? AND device_fingerprint = ? AND mfa_suppressed_until > NOW() AND is_active = 1";
            $result = $this->db->fetch($sql, [$userId, $deviceFingerprint]);
            return $result !== false;
        } catch (Exception $e) {
            return false;
        }
    }

    public function addTrustedDevice($userId, $deviceFingerprint, $deviceName, $userAgent, $ipAddress, $locationData = null, $trustDays = 30) {
        try {
            $trustedUntil = date('Y-m-d H:i:s', strtotime("+{$trustDays} days"));
            
            $data = [
                'user_id' => $userId,
                'device_fingerprint' => $deviceFingerprint,
                'device_name' => $deviceName,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'location_data' => $locationData ? json_encode($locationData) : null,
                'mfa_suppressed_until' => $trustedUntil,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $trustedDeviceId = $this->db->insert('trusted_devices', $data);
            return $trustedDeviceId;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getLoginHistory($userId, $limit = 10) {
        try {
            $sql = "SELECT * FROM login_attempts WHERE user_id = ? AND success = 1 ORDER BY updated_at DESC LIMIT ?";
            return $this->db->fetchAll($sql, [$userId, $limit]);
        } catch (Exception $e) {
            return [];
        }
    }

    public function detectSuspiciousActivity($userId, $ipAddress, $locationData) {
        try {
            $recentLogins = $this->getLoginHistory($userId, 5);
            
            if (empty($recentLogins)) {
                return ['suspicious' => false, 'reason' => null];
            }

            if ($locationData && !empty($recentLogins)) {
                $lastLogin = $recentLogins[0];
                $lastLocationData = json_decode($lastLogin['location_data'] ?? '{}', true);
                
                if ($lastLocationData && isset($lastLocationData['country']) && isset($locationData['country']) && $lastLocationData['country'] !== $locationData['country']) {
                    return [
                        'suspicious' => true, 
                        'reason' => 'Login from different country',
                        'previous_country' => $lastLocationData['country'],
                        'current_country' => $locationData['country']
                    ];
                }
            }

            return ['suspicious' => false, 'reason' => null];
        } catch (Exception $e) {
            return ['suspicious' => false, 'reason' => null];
        }
    }

    public function removeTrustedDevice($userId, $deviceFingerprint) {
        try {
            $sql = "DELETE FROM trusted_devices WHERE user_id = ? AND device_fingerprint = ?";
            $stmt = $this->db->query($sql, [$userId, $deviceFingerprint]);
            return $stmt->rowCount();
        } catch (Exception $e) {
            return 0;
        }
    }

    public function getTrustedDevices($userId) {
        try {
            $sql = "SELECT * FROM trusted_devices WHERE user_id = ? AND mfa_suppressed_until > NOW() AND is_active = 1 ORDER BY created_at DESC";
            return $this->db->fetchAll($sql, [$userId]);
        } catch (Exception $e) {
            return [];
        }
    }

    public function updateUserLoginPreferences($userId, $preferences) {
        try {
            $data = [
                'mfa_enabled' => $preferences['mfa_enabled'] ?? 1,
                'mfa_method' => $preferences['mfa_method'] ?? 'email',
                'trust_device_days' => $preferences['trust_device_days'] ?? 30,
                'notify_on_suspicious_login' => $preferences['notify_on_suspicious_login'] ?? 1,
                'notify_on_new_device' => $preferences['notify_on_new_device'] ?? 1,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->db->update('user_login_preferences', $data, 'user_id = ?', [$userId]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function cleanupExpiredTrustedDevices() {
        try {
            $sql = "UPDATE trusted_devices SET is_active = 0 WHERE mfa_suppressed_until < NOW()";
            $stmt = $this->db->query($sql);
            return $stmt->rowCount();
        } catch (Exception $e) {
            return 0;
        }
    }

    public function getUserLoginPreferences($userId) {
        try {
            $sql = "SELECT * FROM user_login_preferences WHERE user_id = ?";
            $preferences = $this->db->fetch($sql, [$userId]);

            if (!$preferences) {
                return [
                    'mfa_enabled' => 1,
                    'mfa_method' => 'email',
                    'trust_device_days' => 30,
                    'require_mfa_on_new_device' => 1,
                    'allow_trusted_devices' => 1,
                    'trusted_device_duration_days' => 30,
                    'notify_on_suspicious_login' => 1,
                    'notify_on_new_device' => 1,
                    'notify_on_new_login' => 1
                ];
            }

            return $preferences;
        } catch (Exception $e) {
            return [
                'mfa_enabled' => 1,
                'mfa_method' => 'email',
                'trust_device_days' => 30,
                'require_mfa_on_new_device' => 1,
                'allow_trusted_devices' => 1,
                'trusted_device_duration_days' => 30,
                'notify_on_suspicious_login' => 1,
                'notify_on_new_device' => 1,
                'notify_on_new_login' => 1
            ];
        }
    }

    public function isDeviceTrusted($userId, $deviceFingerprint) {
        return $this->isTrustedDevice($userId, $deviceFingerprint);
    }
}
