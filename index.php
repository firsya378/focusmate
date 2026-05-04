<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
requireAdmin();

$users = getAllUsers();
$tasks = getAllTasks();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - FocusMate</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <nav class="bg-white shadow-md p-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold text-purple-600">Panel Admin</h1>
            <div class="flex space-x-4">
                <a href="../dashboard.php" class="text-gray-600 hover:text-purple-600">Kembali ke Dashboard</a>
                <a href="../logout.php" class="text-red-600 hover:text-red-700">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 py-8">
        <h2 class="text-2xl font-bold mb-6">Kelola Pengguna</h2>
        <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
            <table class="min-w-full">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left">ID</th>
                        <th class="px-6 py-3 text-left">Nama</th>
                        <th class="px-6 py-3 text-left">Email</th>
                        <th class="px-6 py-3 text-left">Role</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr class="border-t">
                        <td class="px-6 py-3"><?php echo $user['id']; ?></td>
                        <td class="px-6 py-3"><?php echo htmlspecialchars($user['full_name']); ?></td>
                        <td class="px-6 py-3"><?php echo htmlspecialchars($user['email']); ?></td>
                        <td class="px-6 py-3">
                            <form method="POST" action="manage_users.php" class="inline">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <select name="role" onchange="this.form.submit()" 
                                        class="px-2 py-1 border rounded <?php echo $user['role'] == 'admin' ? 'bg-purple-100' : ''; ?>">
                                    <option value="user" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>>User</option>
                                    <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                </select>
                            </form>
                        </td>
                        <td class="px-6 py-3"><?php echo htmlspecialchars($user['status']); ?></td>
                        <td class="px-6 py-3">
                            <?php if ($user['role'] != 'admin'): ?>
                            <form method="POST" action="manage_users.php" class="inline" onsubmit="return confirm('Hapus user ini?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <h2 class="text-2xl font-bold mb-6">Semua Tugas</h2>
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left">Tugas</th>
                        <th class="px-6 py-3 text-left">Pengguna</th>
                        <th class="px-6 py-3 text-left">Prioritas</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-left">Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $task): ?>
                    <tr class="border-t">
                        <td class="px-6 py-3"><?php echo htmlspecialchars($task['task_name']); ?></td>
                        <td class="px-6 py-3"><?php echo htmlspecialchars($task['user_name']); ?></td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-1 rounded-full text-xs 
                                <?php echo $task['priority'] == 'Tinggi' ? 'bg-red-100 text-red-700' : 
                                         ($task['priority'] == 'Sedang' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700'); ?>">
                                <?php echo $task['priority']; ?>
                            </span>
                        </td>
                        <td class="px-6 py-3">
                            <?php echo $task['status'] == 'pending' ? 'Tertunda' : ($task['status'] == 'completed' ? 'Selesai' : 'Dibatalkan'); ?>
                        </td>
                        <td class="px-6 py-3"><?php echo $task['task_date'] ?? '-'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
