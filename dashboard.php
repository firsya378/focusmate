<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$user = getUserById($_SESSION['user_id']);
$tasks = getUserTasks($_SESSION['user_id'], 'pending');
$completedTasks = getUserTasks($_SESSION['user_id'], 'completed');

// Mendapatkan statistik belajar
$db = getDB();
$stmt = $db->prepare("SELECT COUNT(*) as total_sessions, SUM(duration) as total_time FROM study_sessions WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$stats = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - FocusMate</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <!-- Navigasi -->
    <nav class="bg-white shadow-md fixed w-full top-0 z-50">
        <div class="container mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-8">
                    <h1 class="text-2xl font-bold text-purple-600">
                        <i class="fas fa-brain"></i> FocusMate
                    </h1>
                    <div class="hidden md:flex space-x-6">
                        <a href="dashboard.php" class="text-purple-600 font-semibold">Beranda</a>
                        <a href="tasks.php" class="text-gray-700 hover:text-purple-600">Tugas</a>
                        <a href="timer.php" class="text-gray-700 hover:text-purple-600">Timer</a>
                        <a href="ai-buddy.php" class="text-gray-700 hover:text-purple-600">AI Buddy</a>
                        <?php if (isAdmin()): ?>
                            <a href="admin/index.php" class="text-red-600 hover:text-red-700">Panel Admin</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700">Halo, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                    <a href="profile.php" class="text-gray-600 hover:text-purple-600">
                        <i class="fas fa-user-circle text-2xl"></i>
                    </a>
                    <a href="logout.php" class="text-red-600 hover:text-red-700">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Konten Utama -->
    <div class="container mx-auto px-6 pt-24 pb-12">
        <!-- Kartu Statistik -->
        <div class="grid md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Sesi Belajar</p>
                        <p class="text-3xl font-bold text-purple-600"><?php echo $stats['total_sessions'] ?? 0; ?></p>
                    </div>
                    <i class="fas fa-clock text-4xl text-purple-200"></i>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Waktu Belajar</p>
                        <p class="text-3xl font-bold text-purple-600"><?php echo round(($stats['total_time'] ?? 0) / 60, 1); ?> jam</p>
                    </div>
                    <i class="fas fa-hourglass-half text-4xl text-purple-200"></i>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Tugas Selesai</p>
                        <p class="text-3xl font-bold text-green-600"><?php echo count($completedTasks); ?></p>
                    </div>
                    <i class="fas fa-check-circle text-4xl text-green-200"></i>
                </div>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            <!-- Tugas Tertunda -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-xl font-bold mb-4">Tugas Tertunda</h2>
                <?php if (empty($tasks)): ?>
                    <p class="text-gray-500 text-center py-8">Tidak ada tugas tertunda. Bagus!</p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach (array_slice($tasks, 0, 5) as $task): ?>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <h3 class="font-semibold"><?php echo htmlspecialchars($task['task_name']); ?></h3>
                                    <p class="text-sm text-gray-500">Tenggat: <?php echo $task['task_date'] ?? 'Tidak ada tanggal'; ?></p>
                                </div>
                                <span class="px-2 py-1 text-xs rounded-full 
                                    <?php echo $task['priority'] == 'Tinggi' ? 'bg-red-100 text-red-700' : 
                                             ($task['priority'] == 'Sedang' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700'); ?>">
                                    <?php echo $task['priority']; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <a href="tasks.php" class="block text-center mt-4 text-purple-600 hover:underline">Lihat semua tugas →</a>
            </div>

            <!-- Timer Fokus -->
            <div class="bg-gradient-to-br from-purple-600 to-indigo-600 rounded-xl shadow-md p-6 text-white">
                <h2 class="text-xl font-bold mb-4">Timer Fokus</h2>
                <p class="mb-4">Mulai sesi belajar fokus dengan teknik Pomodoro</p>
                <a href="timer.php" class="inline-block bg-white text-purple-600 px-6 py-2 rounded-lg font-semibold hover:bg-gray-100 transition">
                    Mulai Timer →
                </a>
            </div>
        </div>
    </div>
</body>
</html>
