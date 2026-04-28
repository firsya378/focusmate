<?php
require_once 'config/database.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($username) || empty($full_name) || empty($email) || empty($password)) {
        $error = 'Harap isi semua field';
    } elseif ($password !== $confirm_password) {
        $error = 'Password tidak cocok';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter';
    } else {
        $db = getDB();
        
        // Cek apakah email sudah terdaftar
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        if ($stmt->fetch()) {
            $error = 'Email atau username sudah terdaftar';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user baru
            $stmt = $db->prepare("INSERT INTO users (username, full_name, email, password, role) VALUES (?, ?, ?, ?, 'user')");
            if ($stmt->execute([$username, $full_name, $email, $hashed_password])) {
                $success = 'Pendaftaran berhasil! Silakan login.';
            } else {
                $error = 'Pendaftaran gagal, silakan coba lagi';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - FocusMate</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-purple-50 to-indigo-100 min-h-screen">
    <div class="container mx-auto px-4 py-12">
        <div class="max-w-md mx-auto">
            <div class="text-center mb-8">
                <i class="fas fa-brain text-5xl text-purple-600"></i>
                <h1 class="text-3xl font-bold text-purple-700 mt-4">Daftar Akun Baru</h1>
                <p class="text-gray-600">Mulai perjalanan fokus Anda</p>
            </div>
            
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <?php if ($error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        <?php echo $success; ?>
                        <a href="login.php" class="font-bold underline">Login di sini</a>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                        <input type="text" name="username" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500">
                    </div>
                    
                    <div class="mb-3">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Nama Lengkap</label>
                        <input type="text" name="full_name" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500">
                    </div>
                    
                    <div class="mb-3">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                        <input type="email" name="email" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500">
                    </div>
                    
                    <div class="mb-3">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                        <input type="password" name="password" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500">
                        <p class="text-xs text-gray-500 mt-1">Minimal 6 karakter</p>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Konfirmasi Password</label>
                        <input type="password" name="confirm_password" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500">
                    </div>
                    
                    <button type="submit" 
                            class="w-full bg-purple-600 text-white py-3 rounded-lg font-bold hover:bg-purple-700 transition">
                        Daftar
                    </button>
                </form>
                
                <p class="text-center text-gray-600 mt-4">
                    Sudah punya akun? 
                    <a href="login.php" class="text-purple-600 hover:underline">Masuk di sini</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>