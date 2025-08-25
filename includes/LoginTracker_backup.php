<?php
/**
 * Login Tracker and MFA Management Class
 * Handles login tracking, environment fingerprinting, and MFA suppression
 */
class LoginTracker {
    private $db;
    private $logger;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->logger = Logger::getInstance();
    }
    
    /**
     * Generate device fingerprint from environment data
     */
    public function generateDeviceFingerprint($userAgent, $ipAddress, $additionalData = []) {
        $fingerprintData = [
            'user_agent' => $userAgent,
            'ip_address' => $ipAddress,
            'screen_resolution' => $_POST['screen_resolution'] ?? null,
            'timezone' => $_POST['timezone'] ?? null,
            'language' => $_POST['language'] ?? null,
            'platform' => $_POST['platform'] ?? null,
            'cookie_enabled' => $_POST['cookie_enabled'] ?? null,
            'canvas_fingerprint' => $_POST['canvas_fingerprint'] ?? null,
            'webgl_fingerprint' => $_POST['webgl_fingerprint'] ?? null,
            'fonts' => $_POST['fonts'] ?? null,
            'plugins' => $_POST['plugins'] ?? null
        ];
        
        // Merge additional data
        $fingerprintData = array_merge($fingerprintData, $additionalData);
        
        // Remove null values and create consistent fingerprint
        $fingerprintData = array_filter($fingerprintData, function($value) {
            return $value !== null && $value !== '';
        });
        
        // Create hash of fingerprint data
        return hash('sha256', json_encode($fingerprintData));
    }
    
    /**
     * Get location data from IP address
     */
    public function getLocationData($ipAddress) {
        try {
            // Use a free IP geolocation service (you can upgrade to paid services for better accuracy)
            $url = "http://ip-api.com/json/{$ipAddress}?fields=status,message,country,countryCode,region,regionName,city,zip,lat,lon,timezone,isp,org,as,mobile,proxy,hosting,query";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200 && $response) {
                $data = json_decode($response, true);
                if ($data && $data['status'] === 'success') {
                    return [
                        'country' => $data['country'] ?? null,
                        'country_code' => $data['countryCode'] ?? null,
                        'region' => $data['regionName'] ?? null,
                        'city' => $data['city'] ?? null,
                        'zip' => $data['zip'] ?? null,
                        'latitude' => $data['lat'] ?? null,
                        'longitude' => $data['lon'] ?? null,
                        'timezone' => $data['timezone'] ?? null,
                        'isp' => $data['isp'] ?? null,
                        'organization' => $data['org'] ?? null,
                        'mobile' => $data['mobile'] ?? false,
                        'proxy' => $data['proxy'] ?? false,
                        'hosting' => $data['hosting'] ?? false
                    ];
                }
            }
        } catch (Exception $e) {
            $this->logger->warning('Failed to get location data', [
                'ip_address' => $ipAddress,
                'error' => $e->getMessage()
            ]);
        }
        
        return null;
    }
    
    /**
     * Record login attempt
     */
    public function recordLoginAttempt($email, $userAgent, $ipAddress, $deviceFingerprint, $locationData = null, $userId = null) {
        try {
            $data = [
                'user_id' => $userId,
                'email' => $email,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'device_fingerprint' => $deviceFingerprint,
                'location_data' => $locationData ? json_encode($locationData) : null,
                'success' => false,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $attemptId = $this->db->insert('login_attempts', $data);
            
            $this->logger->info('Login attempt recorded', [
                'attempt_id' => $attemptId,
                'email' => $email,
                'ip_address' => $ipAddress,
                'device_fingerprint' => $deviceFingerprint
            ]);
            
            return $attemptId;
        } catch (Exception $e) {
            $this->logger->error('Failed to record login attempt', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Update login attempt with result
     */
    public function updateLoginAttempt($attemptId, $success, $failureReason = null, $mfaRequired = false, $sessionId = null) {
        try {
            $data = [
                'success' => $success,
                'failure_reason' => $failureReason,
                'mfa_required' => $mfaRequired,
                'session_id' => $sessionId,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->update('login_attempts', $data, ['id' => $attemptId]);
            
            $this->logger->info('Login attempt updated', [
                'attempt_id' => $attemptId,
                'success' => $success,
                'mfa_required' => $mfaRequired
            ]);
            
            return true;
        } catch (Exception $e) {
            $this->logger->error('Failed to update login attempt', [
                'attempt_id' => $attemptId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Check if device is trusted for MFA suppression
     */
    public function isDeviceTrusted($userId, $deviceFingerprint) {
        try {
            $trustedDevice = $this->db->fetch(
                "SELECT * FROM trusted_devices 
                 WHERE user_id = ? AND device_fingerprint = ? AND is_active = 1 
                 AND mfa_suppressed_until > NOW()",
                [$userId, $deviceFingerprint]
            );
            
            return $trustedDevice !== false;
        } catch (Exception $e) {
            $this->logger->error('Failed to check trusted device', [
                'user_id' => $userId,
                'device_fingerprint' => $deviceFingerprint,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Add device as trusted for MFA suppression
     */
    public function addTrustedDevice($userId, $deviceFingerprint, $deviceName, $ipAddress, $userAgent, $locationData = null, $durationDays = 30) {
        try {
            $suppressedUntil = date('Y-m-d H:i:s', strtotime("+{$durationDays} days"));
            
            $data = [
                'user_id' => $userId,
                'device_fingerprint' => $deviceFingerprint,
                'device_name' => $deviceName,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'location_data' => $locationData ? json_encode($locationData) : null,
                'mfa_suppressed_until' => $suppressedUntil,
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->insert('trusted_devices', $data);
            
            $this->logger->info('Trusted device added', [
                'user_id' => $userId,
                'device_fingerprint' => $deviceFingerprint,
                'device_name' => $deviceName,
                'suppressed_until' => $suppressedUntil
            ]);
            
            return true;
        } catch (Exception $e) {
            $this->logger->error('Failed to add trusted device', [
                'user_id' => $userId,
                'device_fingerprint' => $deviceFingerprint,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Remove trusted device
     */
    public function removeTrustedDevice($userId, $deviceFingerprint) {
        try {
            $this->db->update('trusted_devices', 
                ['is_active' => false, 'updated_at' => date('Y-m-d H:i:s')], 
                ['user_id' => $userId, 'device_fingerprint' => $deviceFingerprint]
            );
            
            $this->logger->info('Trusted device removed', [
                'user_id' => $userId,
                'device_fingerprint' => $deviceFingerprint
            ]);
            
            return true;
        } catch (Exception $e) {
            $this->logger->error('Failed to remove trusted device', [
                'user_id' => $userId,
                'device_fingerprint' => $deviceFingerprint,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Get user's trusted devices
     */
    public function getTrustedDevices($userId) {
        try {
            return $this->db->fetchAll(
                "SELECT * FROM trusted_devices 
                 WHERE user_id = ? AND is_active = 1 
                 ORDER BY created_at DESC",
                [$userId]
            );
        } catch (Exception $e) {
            $this->logger->error('Failed to get trusted devices', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * Check if account is locked due to failed attempts
     */
    public function isAccountLocked($email, $ipAddress, $deviceFingerprint) {
        try {
            $failedAttempts = $this->db->fetch(
                "SELECT * FROM failed_login_attempts 
                 WHERE email = ? AND ip_address = ? AND device_fingerprint = ?",
                [$email, $ipAddress, $deviceFingerprint]
            );
            
            if ($failedAttempts && $failedAttempts['locked_until'] && $failedAttempts['locked_until'] > date('Y-m-d H:i:s')) {
                return [
                    'locked' => true,
                    'locked_until' => $failedAttempts['locked_until'],
                    'attempt_count' => $failedAttempts['attempt_count']
                ];
            }
            
            return ['locked' => false];
        } catch (Exception $e) {
            $this->logger->error('Failed to check account lock status', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            return ['locked' => false];
        }
    }
    
    /**
     * Record failed login attempt and potentially lock account
     */
    public function recordFailedAttempt($email, $ipAddress, $deviceFingerprint, $maxAttempts = 5, $lockoutMinutes = 15) {
        try {
            $this->db->beginTransaction();
            
            // Check if record exists
            $existing = $this->db->fetch(
                "SELECT * FROM failed_login_attempts 
                 WHERE email = ? AND ip_address = ? AND device_fingerprint = ?",
                [$email, $ipAddress, $deviceFingerprint]
            );
            
            if ($existing) {
                // Update existing record
                $attemptCount = $existing['attempt_count'] + 1;
                $lockedUntil = null;
                
                if ($attemptCount >= $maxAttempts) {
                    $lockedUntil = date('Y-m-d H:i:s', strtotime("+{$lockoutMinutes} minutes"));
                }
                
                $this->db->update('failed_login_attempts', [
                    'attempt_count' => $attemptCount,
                    'last_attempt_at' => date('Y-m-d H:i:s'),
                    'locked_until' => $lockedUntil
                ], [
                    'email' => $email,
                    'ip_address' => $ipAddress,
                    'device_fingerprint' => $deviceFingerprint
                ]);
            } else {
                // Create new record
                $this->db->insert('failed_login_attempts', [
                    'email' => $email,
                    'ip_address' => $ipAddress,
                    'device_fingerprint' => $deviceFingerprint,
                    'attempt_count' => 1,
                    'first_attempt_at' => date('Y-m-d H:i:s'),
                    'last_attempt_at' => date('Y-m-d H:i:s')
                ]);
            }
            
            $this->db->commit();
            
            $this->logger->warning('Failed login attempt recorded', [
                'email' => $email,
                'ip_address' => $ipAddress,
                'device_fingerprint' => $deviceFingerprint,
                'attempt_count' => $attemptCount ?? 1
            ]);
            
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            $this->logger->error('Failed to record failed login attempt', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Clear failed attempts after successful login
     */
    public function clearFailedAttempts($email, $ipAddress, $deviceFingerprint) {
        try {
            $this->db->delete('failed_login_attempts', [
                'email' => $email,
                'ip_address' => $ipAddress,
                'device_fingerprint' => $deviceFingerprint
            ]);
            
            $this->logger->info('Failed login attempts cleared', [
                'email' => $email,
                'ip_address' => $ipAddress,
                'device_fingerprint' => $deviceFingerprint
            ]);
            
            return true;
        } catch (Exception $e) {
            $this->logger->error('Failed to clear failed attempts', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Get user login preferences
     */
    public function getUserLoginPreferences($userId) {
        try {
            $preferences = $this->db->fetch(
                "SELECT * FROM user_login_preferences WHERE user_id = ?",
                [$userId]
            );
            
            if (!$preferences) {
                // Create default preferences
                $preferences = [
                    'mfa_enabled' => true,
                    'mfa_method' => 'email',
                    'allow_trusted_devices' => true,
                    'trusted_device_duration_days' => 30,
                    'require_mfa_on_new_device' => true,
                    'notify_on_new_login' => true
                ];
                
                $this->db->insert('user_login_preferences', array_merge($preferences, ['user_id' => $userId]));
            }
            
            return $preferences;
        } catch (Exception $e) {
            $this->logger->error('Failed to get user login preferences', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * Update user login preferences
     */
    public function updateUserLoginPreferences($userId, $preferences) {
        try {
            $this->db->update('user_login_preferences', $preferences, ['user_id' => $userId]);
            
            $this->logger->info('User login preferences updated', [
                'user_id' => $userId,
                'preferences' => $preferences
            ]);
            
            return true;
        } catch (Exception $e) {
            $this->logger->error('Failed to update user login preferences', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Get login history for user
     */
    public function getLoginHistory($userId, $limit = 20) {
        try {
            return $this->db->fetchAll(
                "SELECT * FROM login_attempts 
                 WHERE user_id = ? 
                 ORDER BY created_at DESC 
                 LIMIT ?",
                [$userId, $limit]
            );
        } catch (Exception $e) {
            $this->logger->error('Failed to get login history', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * Detect suspicious login activity
     */
    public function detectSuspiciousActivity($userId, $ipAddress, $locationData) {
        try {
            // Get recent successful logins
            $recentLogins = $this->db->fetchAll(
                "SELECT * FROM login_attempts 
                 WHERE user_id = ? AND success = 1 
                 AND created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
                 ORDER BY created_at DESC",
                [$userId]
            );
            
            if (empty($recentLogins)) {
                return ['suspicious' => false, 'reason' => null];
            }
            
            // Check for location changes
            foreach ($recentLogins as $login) {
                if ($login['location_data']) {
                    $previousLocation = json_decode($login['location_data'], true);
                    
                    // If location data exists and is significantly different
                    if ($locationData && $previousLocation) {
                        // Simple distance calculation (you might want to use a more sophisticated approach)
                        if ($locationData['country'] !== $previousLocation['country']) {
                            return [
                                'suspicious' => true,
                                'reason' => 'Login from different country',
                                'previous_location' => $previousLocation,
                                'current_location' => $locationData
                            ];
                        }
                    }
                }
            }
            
            return ['suspicious' => false, 'reason' => null];
        } catch (Exception $e) {
            $this->logger->error('Failed to detect suspicious activity', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return ['suspicious' => false, 'reason' => null];
        }
    }
}
?>


    /**
     * Clean up expired trusted devices
     */
    public function cleanupExpiredTrustedDevices() {
        try {
            $sql = "UPDATE trusted_devices SET is_active = 0 WHERE mfa_suppressed_until < NOW()";
            $stmt = $this->db->query($sql);
            $affected = $stmt->rowCount();
            
            if ($affected > 0) {
                $this->logger->info('Expired trusted devices cleaned up', [
                    'affected_count' => $affected
                ]);
            }
            
            return $affected;
        } catch (Exception $e) {
            $this->logger->error('Failed to cleanup expired trusted devices', [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }
}
?>
