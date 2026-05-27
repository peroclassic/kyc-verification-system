<?php
require_once 'db_config.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $data['user_id'] ?? '';

if (!$user_id) {
    echo json_encode(['status' => 'error', 'message' => 'Missing user ID']);
    exit;
}

$stmt = $conn->prepare("SELECT status FROM verifications WHERE user_id = ?");
$stmt->execute([$user_id]);
$attempts = $stmt->fetchAll(PDO::FETCH_COLUMN);

$attemptCount = count($attempts);
$max = 2;
$remaining = $max - $attemptCount;

$status = 'allowed';
if ($attemptCount === 0) {
    $status = 'allowed';
} elseif ($attemptCount === 1) {
    $status = (strtolower($attempts[0]) === 'Verified') ? 'Verified' : 'allowed';
} elseif ($attemptCount >= 2) {
    $status = 'blocked';
}

echo json_encode([
    'status' => $status,
    'max_attempts' => $max,
    'remaining_attempts' => max(0, $remaining),
]);
