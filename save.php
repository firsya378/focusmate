<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $duration = $_POST['duration'] ?? 25;
    $type = $_POST['type'] ?? 'focus';
    saveStudySession($_SESSION['user_id'], $duration, $type);
    echo json_encode(['success' => true]);
}
?>