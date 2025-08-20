<?php
// Check MailHog API to see what emails are stored
echo "<h1>MailHog Email Check</h1>";

// Get emails from MailHog API
$url = 'http://mailhog:8025/api/v2/messages';
$response = file_get_contents($url);

if ($response === false) {
    echo "<p style='color: red;'>❌ Could not connect to MailHog API</p>";
    exit;
}

$emails = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "<p style='color: red;'>❌ JSON decode error: " . json_last_error_msg() . "</p>";
    exit;
}

if (!$emails) {
    echo "<p style='color: red;'>❌ No emails found or invalid response</p>";
    exit;
}

if (!isset($emails['items'])) {
    echo "<p style='color: red;'>❌ No 'items' key found in response</p>";
    echo "<p>Available keys: " . implode(', ', array_keys($emails)) . "</p>";
    exit;
}

$messageCount = count($emails['items']);
echo "<p style='color: green;'>✅ Found {$messageCount} emails in MailHog</p>";

echo "<h2>Recent Emails:</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #f0f0f0;'>";
echo "<th>From</th>";
echo "<th>To</th>";
echo "<th>Subject</th>";
echo "<th>Date</th>";
echo "<th>Size</th>";
echo "</tr>";

foreach (array_slice($emails['items'], 0, 10) as $email) {
    $from = $email['From']['Mailbox'] . '@' . $email['From']['Domain'];
    $to = $email['To'][0]['Mailbox'] . '@' . $email['To'][0]['Domain'];
    $subject = $email['Content']['Headers']['Subject'][0] ?? 'No Subject';
    $date = $email['Created'] ?? 'Unknown';
    $size = $email['Content']['Size'] ?? 'Unknown';
    
    echo "<tr>";
    echo "<td>" . htmlspecialchars($from) . "</td>";
    echo "<td>" . htmlspecialchars($to) . "</td>";
    echo "<td>" . htmlspecialchars($subject) . "</td>";
    echo "<td>" . htmlspecialchars($date) . "</td>";
    echo "<td>" . htmlspecialchars($size) . " bytes</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2>MailHog Web Interface</h2>";
echo "<p><a href='http://localhost:8025' target='_blank'>Open MailHog Web Interface</a></p>";

echo "<h2>Recent Email Details</h2>";
if (!empty($emails['items'])) {
    $latestEmail = $emails['items'][0];
    echo "<h3>Latest Email:</h3>";
    echo "<p><strong>From:</strong> " . htmlspecialchars($latestEmail['From']['Mailbox'] . '@' . $latestEmail['From']['Domain']) . "</p>";
    echo "<p><strong>To:</strong> " . htmlspecialchars($latestEmail['To'][0]['Mailbox'] . '@' . $latestEmail['To'][0]['Domain']) . "</p>";
    echo "<p><strong>Subject:</strong> " . htmlspecialchars($latestEmail['Content']['Headers']['Subject'][0] ?? 'No Subject') . "</p>";
    echo "<p><strong>Date:</strong> " . htmlspecialchars($latestEmail['Created'] ?? 'Unknown') . "</p>";
    
    // Show email content preview
    $body = $latestEmail['Content']['Body'] ?? '';
    $preview = substr(strip_tags($body), 0, 200) . '...';
    echo "<p><strong>Preview:</strong> " . htmlspecialchars($preview) . "</p>";
}
?>
