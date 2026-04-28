<?php
// includes/functions.php
require_once __DIR__ . '/../config/database.php';

// Mendapatkan data user berdasarkan ID
function getUserById($user_id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

// Mendapatkan semua tugas user
function getUserTasks($user_id, $status = null) {
    $db = getDB();
    if ($status) {
        $stmt = $db->prepare("SELECT * FROM tasks WHERE user_id = ? AND status = ? ORDER BY task_date DESC, priority DESC");
        $stmt->execute([$user_id, $status]);
    } else {
        $stmt = $db->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY task_date DESC, priority DESC");
        $stmt->execute([$user_id]);
    }
    return $stmt->fetchAll();
}

// Menambah tugas baru
function addTask($user_id, $data) {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO tasks (user_id, task_name, description, task_date, start_time, end_time, priority) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)");
    return $stmt->execute([
        $user_id,
        $data['task_name'],
        $data['description'],
        $data['task_date'],
        $data['start_time'],
        $data['end_time'],
        $data['priority']
    ]);
}

// Mengupdate tugas
function updateTask($task_id, $data) {
    $db = getDB();
    $stmt = $db->prepare("UPDATE tasks SET task_name = ?, description = ?, task_date = ?, 
                          start_time = ?, end_time = ?, priority = ?, status = ? WHERE id = ?");
    return $stmt->execute([
        $data['task_name'],
        $data['description'],
        $data['task_date'],
        $data['start_time'],
        $data['end_time'],
        $data['priority'],
        $data['status'],
        $task_id
    ]);
}

// Menghapus tugas
function deleteTask($task_id, $user_id) {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    return $stmt->execute([$task_id, $user_id]);
}

// Menyimpan sesi belajar
function saveStudySession($user_id, $duration, $session_type) {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO study_sessions (user_id, duration, session_type) VALUES (?, ?, ?)");
    return $stmt->execute([$user_id, $duration, $session_type]);
}

// Menyimpan riwayat chat AI
function saveChatHistory($user_id, $message, $response) {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO chat_history (user_id, message, response) VALUES (?, ?, ?)");
    return $stmt->execute([$user_id, $message, $response]);
}

// Mengupdate profil user
function updateProfile($user_id, $data) {
    $db = getDB();
    $stmt = $db->prepare("UPDATE users SET full_name = ?, status = ?, birth_date = ?, email = ? WHERE id = ?");
    return $stmt->execute([
        $data['full_name'],
        $data['status'],
        $data['birth_date'],
        $data['email'],
        $user_id
    ]);
}

// Mendapatkan semua user (untuk admin)
function getAllUsers() {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users ORDER BY created_at DESC");
    $stmt->execute();
    return $stmt->fetchAll();
}

// Mendapatkan semua tugas (untuk admin)
function getAllTasks() {
    $db = getDB();
    $stmt = $db->prepare("SELECT t.*, u.full_name as user_name FROM tasks t 
                          JOIN users u ON t.user_id = u.id 
                          ORDER BY t.created_at DESC");
    $stmt->execute();
    return $stmt->fetchAll();
}

// Mengupdate role user
function updateUserRole($user_id, $role) {
    $db = getDB();
    $stmt = $db->prepare("UPDATE users SET role = ? WHERE id = ?");
    return $stmt->execute([$role, $user_id]);
}

// Menghapus user (kecuali admin)
function deleteUser($user_id) {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
    return $stmt->execute([$user_id]);
}

// Mendapatkan respons AI berdasarkan pesan
function getAIResponse($message) {
    $message = strtolower($message);
    
    if (strpos($message, 'tips belajar') !== false || strpos($message, 'cara belajar') !== false) {
        return "📚 Tips Belajar Efektif:\n\n1️⃣ Gunakan teknik Pomodoro (25 menit belajar, 5 menit istirahat)\n2️⃣ Buat catatan ringkas dengan mind mapping\n3️⃣ Ajarkan kembali ke orang lain\n4️⃣ Cari lingkungan yang nyaman dan minim gangguan\n5️⃣ Istirahat cukup dan jaga kesehatan";
    }
    elseif (strpos($message, 'prokrastinasi') !== false || strpos($message, 'malas') !== false) {
        return "🎯 Cara Mengatasi Prokrastinasi:\n\n• Mulai dengan tugas kecil selama 2 menit\n• Buat deadline pribadi lebih awal\n• Matikan notifikasi HP saat belajar\n• Beri reward setelah menyelesaikan tugas\n• Ingat tujuan jangka panjangmu!";
    }
    elseif (strpos($message, 'motivasi') !== false) {
        return "💪 Motivasi Hari Ini:\n\n\"Kesuksesan bukanlah akhir, kegagalan bukanlah hal yang fatal. Yang terpenting adalah keberanian untuk melanjutkan.\" - Winston Churchill\n\nYuk, mulai belajar! Setiap langkah kecil hari ini akan membawamu lebih dekat ke impianmu! ✨";
    }
    elseif (strpos($message, 'matematika') !== false || strpos($message, 'mtk') !== false) {
        return "🧮 Tips Belajar Matematika:\n\n• Pahami konsep dasar, jangan hanya menghafal rumus\n• Latihan soal secara rutin\n• Gunakan aplikasi seperti Photomath untuk bantuan\n• Tonton video tutorial di YouTube\n• Bergabung dengan grup diskusi matematika";
    }
    elseif (strpos($message, 'bahasa inggris') !== false || strpos($message, 'english') !== false) {
        return "🇬🇧 Tips Belajar Bahasa Inggris:\n\n• Tonton film dengan subtitle English\n• Dengarkan podcast bahasa Inggris\n• Praktik speaking dengan teman\n• Baca artikel atau buku bahasa Inggris\n• Gunakan aplikasi Duolingo untuk latihan daily";
    }
    elseif (strpos($message, 'ujian') !== false || strpos($message, 'try out') !== false) {
        return "📝 Persiapan Menghadapi Ujian:\n\n• Buat jadwal belajar terstruktur\n• Fokus pada materi yang sulit terlebih dahulu\n• Latihan soal tahun sebelumnya\n• Istirahat cukup sebelum ujian\n• Datang lebih awal dan bawa perlengkapan lengkap";
    }
    elseif (strpos($message, 'hai') !== false || strpos($message, 'halo') !== false || strpos($message, 'hi') !== false) {
        return "Halo! 👋 Ada yang bisa aku bantu tentang belajar atau tugasmu hari ini?";
    }
    else {
        return "Maaf, aku masih belajar untuk menjawab pertanyaan itu. Coba tanya tentang:\n\n• Tips belajar\n• Cara mengatasi prokrastinasi\n• Motivasi belajar\n• Tips matematika\n• Tips bahasa Inggris\n• Persiapan ujian";
    }
}
?>