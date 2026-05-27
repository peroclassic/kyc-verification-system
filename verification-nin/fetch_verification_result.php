<?php
// fetch_verification_result.php
session_start();
$user_id = $_POST['user_id'] ?? $_SESSION['user_id'] ?? '';

header('Content-Type: application/json');

require_once 'db_config.php'; // ensure this sets up $conn as a PDO object

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Get raw POST data
$data = json_decode(file_get_contents("php://input"), true);
$user_id = $data['user_id'] ?? '';

if (empty($user_id)) {
    echo json_encode(['status' => 'error', 'message' => 'User ID is required']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT status, result_text FROM verifications WHERE user_id = :user_id ORDER BY id DESC LIMIT 1");
    $stmt->execute(['user_id' => $user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        echo json_encode([
            'status' => $row['status'],       // e.g. "success", "failed"
            'message' => $row['result_text']
        ]);
    } else {
        echo json_encode([
            'status' => 'pending',
            'message' => 'No result yet. Please wait or refresh.'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
