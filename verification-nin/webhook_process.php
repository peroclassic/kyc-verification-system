<?php
// webhook_process.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';     // Smile ID SDK
require_once __DIR__ . '/config.php';              // Contains PARTNER_ID and API_KEY
require_once __DIR__ . '/db_config.php';           // Contains PDO $conn config

// 1. Get raw POST payload
$raw_payload = file_get_contents('php://input');

// 2. Log raw payload
file_put_contents('webhook_log.txt', $raw_payload . PHP_EOL, FILE_APPEND);

// 3. Decode JSON
$data = json_decode($raw_payload, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    file_put_contents('webhook_debug_log.txt', "JSON Decode Error: " . json_last_error_msg() . "\nPayload: $raw_payload\n", FILE_APPEND);
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

// 4. Validate required fields
if (!isset($data['signature'], $data['timestamp'], $data['PartnerParams'], $data['ResultCode'], $data['ResultText'])) {
    file_put_contents('webhook_debug_log.txt', "Missing required fields in payload: $raw_payload\n", FILE_APPEND);
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

// 5. Extract values
$timestamp         = trim($data['timestamp']);
$received_sig      = trim($data['signature']);
$partner_id        = PARTNER_ID;
$job_id            = $data['PartnerParams']['job_id'] ?? '';
$result_code       = $data['ResultCode'];
$result_text       = $data['ResultText'];
$response_json     = json_encode($data);
$confidence_value  = $data['ConfidenceValue'] ?? null;
$kyc_receipt       = $data['KYCReceipt'];
$selfie_image_url  = $data['ImageLinks']['selfie_image']  ?? null;
$id_card_image_url = $data['ImageLinks']['id_card_image'] ?? null;

// 6. Convert ResultCode to readable status
switch ($result_code) {
    case '0810': $readable_status = 'Verified'; break;
    case '0811': $readable_status = 'Not Verified'; break;
    case '0814': $readable_status = 'Pending'; break;
    default:     $readable_status = 'Unknown'; break;
}

// 7. Compute Expected Signature using strict method
$signature_base = $timestamp . $partner_id . "sid_request";
$expected_sig = base64_encode(hash_hmac('sha256', $signature_base, API_KEY, true));

// 8. Compare signature
if (!hash_equals($expected_sig, $received_sig)) {
    file_put_contents('webhook_debug_log.txt',
        "[Signature Mismatch]\nJob ID: $job_id\nSignature Base: $signature_base\nReceived: $received_sig\nExpected: $expected_sig\n\n",
        FILE_APPEND
    );
    http_response_code(403);
    echo json_encode(['error' => 'Invalid signature']);
    exit;
}

// 9. Proceed to DB insert/update
try {
    $stmt = $conn->prepare("SELECT id FROM verifications WHERE job_id = ?");
    $stmt->execute([$job_id]);

    if ($stmt->rowCount() === 0) {
        // Insert new record
        $insert = $conn->prepare("
            INSERT INTO verifications (
                job_id, status, result_text, result_code, response_payload,
                confidence_value, kyc_receipt, selfie_image_url, id_card_image_url, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $ok = $insert->execute([
            $job_id, $readable_status, $result_text, $result_code, $response_json,
            $confidence_value, $kyc_receipt, $selfie_image_url, $id_card_image_url
        ]);

        if (!$ok) {
            $err = $insert->errorInfo();
            file_put_contents('webhook_debug_log.txt', "Insert failed: " . implode(' | ', $err) . "\n", FILE_APPEND);
            http_response_code(500);
            echo json_encode(['error' => 'Insert failed']);
            exit;
        }

        file_put_contents('webhook_debug_log.txt', "Inserted Job ID: $job_id\n", FILE_APPEND);

    } else {
        // Update existing record
        $update = $conn->prepare("
            UPDATE verifications SET
                status = ?, result_text = ?, result_code = ?, response_payload = ?,
                confidence_value = ?, kyc_receipt = ?, selfie_image_url = ?,
                id_card_image_url = ?, updated_at = NOW()
            WHERE job_id = ?
        ");
        $ok = $update->execute([
            $readable_status, $result_text, $result_code, $response_json,
            $confidence_value, $kyc_receipt, $selfie_image_url, $id_card_image_url, $job_id
        ]);

        if (!$ok) {
            $err = $update->errorInfo();
            file_put_contents('webhook_debug_log.txt', "Update failed: " . implode(' | ', $err) . "\n", FILE_APPEND);
            http_response_code(500);
            echo json_encode(['error' => 'Update failed']);
            exit;
        }

        file_put_contents('webhook_debug_log.txt', "Updated Job ID: $job_id\n", FILE_APPEND);
    }

} catch (PDOException $e) {
    file_put_contents('webhook_debug_log.txt', "DB Exception: " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode(['error' => 'DB error']);
    exit;
}

// 10. All good
http_response_code(200);
echo json_encode(['success' => true]);