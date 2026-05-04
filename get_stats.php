<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$db = getDB();

// Hitung tugas aktif
$stmt = $db->prepare("SELECT COUNT(*) as count FROM tasks WHERE user_id = ? AND status = 'pending'");
$stmt->execute([$_SESSION['user_id']]);
$tasks = $stmt->fetch();

// Hitung sesi hari ini
$stmt = $db->prepare("SELECT COUNT(*) as count FROM study_sessions WHERE user_id = ? AND DATE(completed_at) = CURDATE()");
$stmt->execute([$_SESSION['user_id']]);
$sessions = $stmt->fetch();

echo json_encode([
    'activeTasks' => $tasks['count'],
    'todaySessions' => $sessions['count']
]);
?>
