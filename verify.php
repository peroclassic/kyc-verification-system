<?php
// verify.php (on popup server)
$token = $_GET['token'] ?? '';
$secret = 'my-super-secret-key';

function decrypt_token($token, $secret) {
    $decoded = base64_decode($token);
    if (!$decoded || strlen($decoded) < 17) return null;
    $iv = substr($decoded, 0, 16);
    $ciphertext = substr($decoded, 16);
    $json = openssl_decrypt($ciphertext, 'AES-256-CBC', $secret, OPENSSL_RAW_DATA, $iv);
    return json_decode($json, true);
}

$data = decrypt_token($token, $secret);

// Check token validity
if (!$data || !isset($data['user_id']) || !isset($data['ts']) || (time() - $data['ts']) > 300) {
    echo <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Session Expired</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                text-align: center;
                padding: 40px;
            }
            button {
                background-color: #17794F;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 6px;
                cursor: pointer;
                margin-top: 20px;
            }
            button:hover {
                background-color: #145f3f;
            }
        </style>
    </head>
    <body>
        <script>
            setTimeout(function() {
                alert("Session expired or invalid token. Please try again.");
                if (window.opener) {
                    window.opener.location.reload(); // Refresh parent
                }
                setTimeout(function () {
                    window.close(); // Try closing popup
                }, 100);
            }, 100);
        </script>
        <noscript>
            <p>This window will not close automatically because JavaScript is disabled.</p>
            <p><strong>Please close this window manually and refresh the main page.</strong></p>
        </noscript>
        <p>If this window does not close automatically, click the button below and please refresh before trying again:</p>
        <button onclick="window.close()">Close Window</button>
    </body>
    </html>
    HTML;
    exit;
}

// If valid, forward user_id silently
$user_id = intval($data['user_id']);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Redirecting...</title>
</head>
<body>
    <form id="silentForm" action="/index.php" method="POST">
        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
    </form>
    <script>
        document.getElementById('silentForm').submit();
    </script>
</body>
</html>
