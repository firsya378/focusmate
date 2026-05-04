<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $duration = $_POST['duration'] ?? 25;
    $type = $_POST['type'] ?? 'focus';
    saveStudySession($_SESSION['user_id'], $duration, $type);
    echo json_encode(['success' => true]);
}
?>
