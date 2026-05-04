<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['role'])) {
        updateUserRole($_POST['user_id'], $_POST['role']);
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        deleteUser($_POST['user_id']);
    }
}

header('Location: index.php');
exit();
?>
