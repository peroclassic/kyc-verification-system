<?php
// Enable error reporting
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

// Include dependencies
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_config.php';

use SmileIdentity\SmileIdentityCore;

// Log that script started
//file_put_contents('debug_log.txt', "Script started\n", FILE_APPEND);

// Get the raw input and decode JSON
$raw_input = file_get_contents("php://input");
file_put_contents('debug_log.txt', "RAW: $raw_input\n", FILE_APPEND);
$data = json_decode($raw_input, true);

// Validate payload
if (!$data || !isset($data['id_number'], $data['first_name'], $data['last_name'], $data['selfieLiveness'][0]['image'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$id_number = $data['id_number'];
$first_name = $data['first_name'];
$last_name = $data['last_name'];
$selfie_base64 = $data['selfieLiveness'][0]['image'];
$user_id = $data['user_id'];

// Generate job ID and timestamp
$job_id = uniqid('job_', true);

//$user_id = uniqid('user_', true);

// Initialize SmileIdentityCore
$connection = new SmileIdentityCore(
    PARTNER_ID,
    CALLBACK_URL,
    API_KEY,
    SID_SERVER
);

// Prepare partner parameters
$partner_params = [
    'job_id' => $job_id,
    'user_id' => $user_id,
    'job_type' => JOB_TYPE
];

// Prepare ID information
$id_info = [
    'country' => 'NG',
    'id_type' => 'NIN_V2',
    'id_number' => $id_number,
    'first_name' => $first_name,
    'last_name' => $last_name
];

// Prepare image details, including selfie and any additional images
$image_details = [
    [
        'image_type_id' => 2, // Selfie image in base64
        'image' => $selfie_base64
    ]
];

// Add any additional images if needed
if (isset($data['selfieLiveness'][1]['image'])) {
    $image_details[] = [
        'image_type_id' => 3, // Liveness image 2 (for example, if Smile Identity supports additional images)
        'image' => $data['selfieLiveness'][1]['image']
    ];
}

// Set options
$options = [
    'return_job_status' => true
];

// Submit job
try {
    // Step 1: Insert early — without waiting for Smile Identity response
    $stmt = $conn->prepare("
        INSERT INTO verifications (user_id, job_id, id_number, first_name, last_name, request_payload, status, created_at)
        VALUES (:user_id, :job_id, :id_number, :first_name, :last_name, :request_payload, 'pending', NOW())
    ");
    $stmt->execute([
        ':user_id' => $user_id,
        ':job_id' => $job_id,
        ':id_number' => $id_number,
        ':first_name' => $first_name,
        ':last_name' => $last_name,
        ':request_payload' => json_encode([
            'partner_params' => $partner_params,
            'id_info' => $id_info,
            'image_details' => $image_details,
            'options' => $options
        ])
    ]);

    // Step 2: Call Smile Identity
    $response = $connection->submit_job(
        $partner_params,
        $image_details,
        $id_info,
        $options
    );

    
        // Log response
    $response_json = json_encode($response);
    file_put_contents('debug_log.txt', "Smile ID Response: $response_json\n", FILE_APPEND);

   

    // Final response to frontend
    echo json_encode([
        'success' => true,
        'job_id' => $job_id,
        'user_id' => $user_id,
        'message' => 'Verification initiated successfully'
    ]);
} catch (Exception $e) {
    file_put_contents('debug_log.txt', "Error: " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode(['error' => 'Verification failed']);
}