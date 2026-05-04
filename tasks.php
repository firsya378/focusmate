<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

// Handle operasi tugas
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                addTask($_SESSION['user_id'], [
                    'task_name' => $_POST['task_name'],
                    'description' => $_POST['description'],
                    'task_date' => $_POST['task_date'],
                    'start_time' => $_POST['start_time'],
                    'end_time' => $_POST['end_time'],
                    'priority' => $_POST['priority']
                ]);
                break;
                
            case 'edit':
                updateTask($_POST['task_id'], [
                    'task_name' => $_POST['task_name'],
                    'description' => $_POST['description'],
                    'task_date' => $_POST['task_date'],
                    'start_time' => $_POST['start_time'],
                    'end_time' => $_POST['end_time'],
                    'priority' => $_POST['priority'],
                    'status' => $_POST['status']
                ]);
                break;
                
            case 'delete':
                deleteTask($_POST['task_id'], $_SESSION['user_id']);
                break;
        }
        header('Location: tasks.php');
        exit();
    }
}

$tasks = getUserTasks($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Tugas - FocusMate</title>
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
                        <a href="dashboard.php" class="text-gray-700 hover:text-purple-600">Beranda</a>
                        <a href="tasks.php" class="text-purple-600 font-semibold">Tugas</a>
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
        <!-- Form Tambah Tugas -->
        <div class="bg-white rounded-xl shadow-md p-8 mb-8">
            <h2 class="text-2xl font-bold mb-6">Tambah Tugas Baru</h2>
            <form method="POST" action="" class="space-y-4">
                <input type="hidden" name="action" value="add">
                <input type="text" name="task_name" placeholder="Nama Tugas" required 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500">
                <textarea name="description" placeholder="Deskripsi" rows="3" 
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500"></textarea>
                <div class="grid md:grid-cols-2 gap-4">
                    <input type="date" name="task_date" 
                           class="px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500">
                    <input type="time" name="start_time" 
                           class="px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500">
                    <input type="time" name="end_time" 
                           class="px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500">
                    <select name="priority" class="px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500">
                        <option value="Rendah">Prioritas: Rendah</option>
                        <option value="Sedang">Prioritas: Sedang</option>
                        <option value="Tinggi">Prioritas: Tinggi</option>
                    </select>
                </div>
                <button type="submit" class="bg-purple-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-purple-700 transition">
                    Simpan Tugas
                </button>
            </form>
        </div>

        <!-- Daftar Tugas -->
        <div class="bg-white rounded-xl shadow-md p-8">
            <h2 class="text-2xl font-bold mb-6">Daftar Tugas Saya</h2>
            <div class="space-y-4">
                <?php if (empty($tasks)): ?>
                    <p class="text-gray-500 text-center py-8">Belum ada tugas. Buat tugas pertama Anda di atas!</p>
                <?php else: ?>
                    <?php foreach ($tasks as $task): ?>
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($task['task_name']); ?></h3>
                                    <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($task['description']); ?></p>
                                    <div class="flex flex-wrap gap-3 mt-3 text-sm text-gray-500">
                                        <?php if ($task['task_date']): ?>
                                            <span><i class="far fa-calendar"></i> <?php echo $task['task_date']; ?></span>
                                        <?php endif; ?>
                                        <?php if ($task['start_time']): ?>
                                            <span><i class="far fa-clock"></i> <?php echo $task['start_time']; ?> - <?php echo $task['end_time']; ?></span>
                                        <?php endif; ?>
                                        <span class="px-2 py-1 rounded-full text-xs 
                                            <?php echo $task['priority'] == 'Tinggi' ? 'bg-red-100 text-red-700' : 
                                                     ($task['priority'] == 'Sedang' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700'); ?>">
                                            <?php echo $task['priority']; ?>
                                        </span>
                                        <span class="px-2 py-1 rounded-full text-xs
                                            <?php echo $task['status'] == 'completed' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'; ?>">
                                            <?php echo $task['status'] == 'pending' ? 'Tertunda' : ($task['status'] == 'completed' ? 'Selesai' : 'Dibatalkan'); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex space-x-2 ml-4">
                                    <button onclick="editTask(<?php echo htmlspecialchars(json_encode($task)); ?>)" 
                                            class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" action="" class="inline" onsubmit="return confirm('Hapus tugas ini?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                        <button type="submit" class="text-red-600 hover:text-red-800">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Edit Tugas -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-xl p-8 max-w-md w-full mx-4">
            <h3 class="text-xl font-bold mb-4">Edit Tugas</h3>
            <form method="POST" action="" id="editForm">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="task_id" id="edit_task_id">
                <input type="text" name="task_name" id="edit_task_name" required 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg mb-3">
                <textarea name="description" id="edit_description" rows="3" 
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg mb-3"></textarea>
                <input type="date" name="task_date" id="edit_task_date" 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg mb-3">
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <input type="time" name="start_time" id="edit_start_time" 
                           class="px-4 py-3 border border-gray-300 rounded-lg">
                    <input type="time" name="end_time" id="edit_end_time" 
                           class="px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <select name="priority" id="edit_priority" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg mb-3">
                    <option value="Rendah">Prioritas: Rendah</option>
                    <option value="Sedang">Prioritas: Sedang</option>
                    <option value="Tinggi">Prioritas: Tinggi</option>
                </select>
                <select name="status" id="edit_status" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg mb-4">
                    <option value="pending">Tertunda</option>
                    <option value="completed">Selesai</option>
                    <option value="cancelled">Dibatalkan</option>
                </select>
                <div class="flex space-x-3">
                    <button type="submit" class="flex-1 bg-purple-600 text-white py-2 rounded-lg font-semibold hover:bg-purple-700">
                        Simpan Perubahan
                    </button>
                    <button type="button" onclick="closeModal()" class="flex-1 bg-gray-300 text-gray-700 py-2 rounded-lg font-semibold hover:bg-gray-400">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editTask(task) {
            document.getElementById('edit_task_id').value = task.id;
            document.getElementById('edit_task_name').value = task.task_name;
            document.getElementById('edit_description').value = task.description || '';
            document.getElementById('edit_task_date').value = task.task_date || '';
            document.getElementById('edit_start_time').value = task.start_time || '';
            document.getElementById('edit_end_time').value = task.end_time || '';
            document.getElementById('edit_priority').value = task.priority;
            document.getElementById('edit_status').value = task.status;
            document.getElementById('editModal').style.display = 'flex';
        }
        
        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }
    </script>
</body>
</html>
