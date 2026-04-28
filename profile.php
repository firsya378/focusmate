<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
requireLogin();

$user = getUserById($_SESSION['user_id']);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'] ?? '';
    $status = $_POST['status'] ?? '';
    $birth_date = $_POST['birth_date'] ?? '';
    $email = $_POST['email'] ?? '';
    
    if (empty($full_name) || empty($email)) {
        $error = 'Nama dan email wajib diisi';
    } else {
        // Update password jika diisi
        if (!empty($_POST['password'])) {
            $new_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $db = getDB();
            $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$new_password, $_SESSION['user_id']]);
        }
        
        if (updateProfile($_SESSION['user_id'], [
            'full_name' => $full_name,
            'status' => $status,
            'birth_date' => $birth_date,
            'email' => $email
        ])) {
            $_SESSION['full_name'] = $full_name;
            $success = 'Profil berhasil diperbarui!';
            $user = getUserById($_SESSION['user_id']);
        } else {
            $error = 'Gagal memperbarui profil';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - FocusMate</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <nav class="bg-white shadow-md fixed w-full top-0 z-50">
        <div class="container mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-8">
                    <h1 class="text-2xl font-bold text-purple-600">
                        <i class="fas fa-brain"></i> FocusMate
                    </h1>
                    <div class="hidden md:flex space-x-6">
                        <a href="dashboard.php" class="text-gray-700 hover:text-purple-600">Beranda</a>
                        <a href="tasks.php" class="text-gray-700 hover:text-purple-600">Tugas</a>
                        <a href="timer.php" class="text-gray-700 hover:text-purple-600">Timer</a>
                        <a href="ai-buddy.php" class="text-gray-700 hover:text-purple-600">AI Buddy</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700">Halo, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                    <a href="logout.php" class="text-red-600 hover:text-red-700">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 pt-24 pb-12">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <div class="text-center mb-8">
                    <div class="w-24 h-24 rounded-full bg-purple-100 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-user-circle text-5xl text-purple-600"></i>
                    </div>
                    <h2 class="text-2xl font-bold">Edit Profil</h2>
                </div>
                
                <?php if ($error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Nama Lengkap</label>
                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Status</label>
                        <select name="status" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500">
                            <option value="Siswa" <?php echo $user['status'] == 'Siswa' ? 'selected' : ''; ?>>Siswa</option>
                            <option value="Mahasiswa" <?php echo $user['status'] == 'Mahasiswa' ? 'selected' : ''; ?>>Mahasiswa</option>
                            <option value="Guru" <?php echo $user['status'] == 'Guru' ? 'selected' : ''; ?>>Guru</option>
                            <option value="Dosen" <?php echo $user['status'] == 'Dosen' ? 'selected' : ''; ?>>Dosen</option>
                            <option value="Umum" <?php echo $user['status'] == 'Umum' ? 'selected' : ''; ?>>Umum</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal Lahir</label>
                        <input type="date" name="birth_date" value="<?php echo $user['birth_date']; ?>" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500">
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Password Baru (kosongkan jika tidak diubah)</label>
                        <input type="password" name="password" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500">
                    </div>
                    
                    <button type="submit" class="w-full bg-purple-600 text-white py-3 rounded-lg font-bold hover:bg-purple-700 transition">
                        Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>