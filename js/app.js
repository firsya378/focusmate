// js/app.js - Core Functions (Updated)

// ============ DATABASE (Local Storage) ============
const DB = {
    get: function(key) {
        try {
            return JSON.parse(localStorage.getItem(key)) || [];
        } catch {
            return [];
        }
    },
    set: function(key, data) {
        localStorage.setItem(key, JSON.stringify(data));
    },
    getUsers: function() {
        let users = this.get('focusmate_users');
        if (users.length === 0) {
            users = [{
                id: 1,
                username: 'admin',
                email: 'admin@focusmate.com',
                password: this.hashPassword('admin123'),
                full_name: 'Administrator',
                role: 'admin',
                status: 'Admin',
                birth_date: '',
                avatar_url: '',
                created_at: new Date().toISOString()
            }];
            this.set('focusmate_users', users);
        }
        return users;
    },
    getTasks: function() {
        return this.get('focusmate_tasks');
    },
    getSessions: function() {
        return this.get('focusmate_sessions');
    },
    getChats: function() {
        return this.get('focusmate_chats');
    },
    hashPassword: function(password) {
        let hash = 0;
        for (let i = 0; i < password.length; i++) {
            const char = password.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash;
        }
        return 'hashed_' + hash;
    },
    getNextId: function(key) {
        const data = this.get(key);
        if (data.length === 0) return 1;
        return Math.max(...data.map(item => item.id || 0)) + 1;
    }
};

// ============ AUTH FUNCTIONS ============
function loginUser(email, password) {
    const users = DB.getUsers();
    const hashed = DB.hashPassword(password);
    const user = users.find(u => u.email === email && u.password === hashed);
    
    if (user) {
        const session = {
            user_id: user.id,
            username: user.username,
            full_name: user.full_name,
            role: user.role,
            login_time: new Date().toISOString()
        };
        localStorage.setItem('focusmate_session', JSON.stringify(session));
        return { success: true, user: user };
    }
    return { success: false, message: 'Email atau password salah' };
}

function registerUser(username, fullName, email, password, confirmPassword) {
    if (password !== confirmPassword) {
        return { success: false, message: 'Password tidak cocok' };
    }
    if (password.length < 6) {
        return { success: false, message: 'Password minimal 6 karakter' };
    }
    
    const users = DB.getUsers();
    if (users.find(u => u.email === email)) {
        return { success: false, message: 'Email sudah terdaftar' };
    }
    if (users.find(u => u.username === username)) {
        return { success: false, message: 'Username sudah terdaftar' };
    }
    
    const newUser = {
        id: DB.getNextId('focusmate_users'),
        username: username,
        email: email,
        password: DB.hashPassword(password),
        full_name: fullName,
        role: 'user',
        status: 'Mahasiswa',
        birth_date: '',
        avatar_url: '',
        created_at: new Date().toISOString()
    };
    
    users.push(newUser);
    DB.set('focusmate_users', users);
    return { success: true, message: 'Pendaftaran berhasil!' };
}

function getCurrentUser() {
    const session = localStorage.getItem('focusmate_session');
    if (!session) return null;
    return JSON.parse(session);
}

function isLoggedIn() {
    return getCurrentUser() !== null;
}

function isAdmin() {
    const user = getCurrentUser();
    return user && user.role === 'admin';
}

function logout() {
    localStorage.removeItem('focusmate_session');
    window.location.href = 'login.html';
}

function requireLogin() {
    if (!isLoggedIn()) {
        window.location.href = 'login.html';
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        window.location.href = 'dashboard.html';
    }
}

// ============ USER FUNCTIONS ============
function getUserById(id) {
    const users = DB.getUsers();
    return users.find(u => u.id === id) || null;
}

function updateUserProfile(userId, data) {
    const users = DB.getUsers();
    const index = users.findIndex(u => u.id === userId);
    if (index === -1) return false;
    
    users[index] = { ...users[index], ...data };
    DB.set('focusmate_users', users);
    
    const session = getCurrentUser();
    if (session && session.user_id === userId) {
        session.full_name = data.full_name || session.full_name;
        localStorage.setItem('focusmate_session', JSON.stringify(session));
    }
    return true;
}

function getAllUsers() {
    return DB.getUsers();
}

function updateUserRole(userId, role) {
    const users = DB.getUsers();
    const index = users.findIndex(u => u.id === userId);
    if (index === -1) return false;
    users[index].role = role;
    DB.set('focusmate_users', users);
    return true;
}

function deleteUser(userId) {
    const users = DB.getUsers();
    const user = users.find(u => u.id === userId);
    if (user && user.role === 'admin') return false;
    const filtered = users.filter(u => u.id !== userId);
    DB.set('focusmate_users', filtered);
    return true;
}

// ============ TASK FUNCTIONS ============
function getTasks(userId) {
    const tasks = DB.getTasks();
    return tasks.filter(t => t.user_id === userId);
}

function getTasksByStatus(userId, status) {
    const tasks = getTasks(userId);
    return tasks.filter(t => t.status === status);
}

function addTask(userId, data) {
    const tasks = DB.getTasks();
    const newTask = {
        id: DB.getNextId('focusmate_tasks'),
        user_id: userId,
        task_name: data.task_name,
        description: data.description || '',
        task_date: data.task_date || '',
        start_time: data.start_time || '',
        end_time: data.end_time || '',
        priority: data.priority || 'Sedang',
        status: data.status || 'pending',
        created_at: new Date().toISOString(),
        updated_at: new Date().toISOString()
    };
    tasks.push(newTask);
    DB.set('focusmate_tasks', tasks);
    return newTask;
}

function updateTask(taskId, data) {
    const tasks = DB.getTasks();
    const index = tasks.findIndex(t => t.id === taskId);
    if (index === -1) return false;
    tasks[index] = { ...tasks[index], ...data, updated_at: new Date().toISOString() };
    DB.set('focusmate_tasks', tasks);
    return true;
}

function deleteTask(taskId, userId) {
    let tasks = DB.getTasks();
    tasks = tasks.filter(t => t.id !== taskId || t.user_id !== userId);
    DB.set('focusmate_tasks', tasks);
    return true;
}

function getAllTasks() {
    const tasks = DB.getTasks();
    const users = DB.getUsers();
    return tasks.map(task => {
        const user = users.find(u => u.id === task.user_id);
        return { ...task, user_name: user ? user.full_name : 'Unknown' };
    });
}

// ============ STUDY SESSION FUNCTIONS ============
function saveStudySession(userId, duration, type) {
    const sessions = DB.getSessions();
    sessions.push({
        id: DB.getNextId('focusmate_sessions'),
        user_id: userId,
        duration: duration,
        session_type: type || 'focus',
        completed_at: new Date().toISOString()
    });
    DB.set('focusmate_sessions', sessions);
    return true;
}

function getStudyStats(userId) {
    const sessions = DB.getSessions();
    const userSessions = sessions.filter(s => s.user_id === userId);
    const totalSessions = userSessions.length;
    const totalTime = userSessions.reduce((sum, s) => sum + s.duration, 0);
    return { total_sessions: totalSessions, total_time: totalTime };
}

// ============ CHAT FUNCTIONS ============
function saveChat(userId, message, response) {
    const chats = DB.getChats();
    chats.push({
        id: DB.getNextId('focusmate_chats'),
        user_id: userId,
        message: message,
        response: response,
        created_at: new Date().toISOString()
    });
    DB.set('focusmate_chats', chats);
    return true;
}

function getChatHistory(userId) {
    const chats = DB.getChats();
    return chats.filter(c => c.user_id === userId).sort((a, b) => 
        new Date(b.created_at) - new Date(a.created_at)
    );
}

// ============ AI RESPONSE FUNCTION ============
function getAIResponse(message) {
    const msg = message.toLowerCase();
    
    const responses = {
        'tips belajar|cara belajar|belajar efektif': `📚 **Tips Belajar Efektif**

1️⃣ **Teknik Pomodoro** - 25 menit fokus, 5 menit istirahat
2️⃣ **Mind Mapping** - Buat catatan visual yang terhubung
3️⃣ **Teach Others** - Ajarkan materi ke orang lain
4️⃣ **Lingkungan** - Cari tempat yang nyaman & minim gangguan
5️⃣ **Istirahat** - Jaga kesehatan dengan tidur cukup

✨ *Konsistensi lebih penting daripada intensitas!*`,

        'prokrastinasi|malas|menunda': `🎯 **Cara Mengatasi Prokrastinasi**

• Mulai dengan tugas kecil selama 2 menit (aturan 2 menit)
• Buat deadline pribadi 2 hari lebih awal
• Matikan notifikasi HP saat belajar
• Beri reward kecil setelah selesai tugas
• Ingat kembali tujuan jangka panjangmu

💪 *Tindakan kecil hari ini = hasil besar esok hari!*`,

        'motivasi|semangat|inspirasi': `💪 **Motivasi Hari Ini**

*"Kesuksesan bukanlah akhir, kegagalan bukanlah hal yang fatal. Yang terpenting adalah keberanian untuk melanjutkan."*
— Winston Churchill

✨ *Setiap langkah kecil hari ini membawamu lebih dekat ke impianmu!*

🚀 Yuk, mulai belajar sekarang juga!`,

        'matematika|mtk|hitung': `🧮 **Tips Belajar Matematika**

• Pahami konsep dasar, jangan hafalan rumus
• Latihan soal secara rutin & bertahap
• Gunakan visualisasi untuk masalah abstrak
• Tonton video tutorial di YouTube
• Bergabung dengan grup diskusi matematika

📐 *Matematika adalah tentang pola, bukan tentang angka!*`,

        'bahasa inggris|english|inggris': `🇬🇧 **Tips Belajar Bahasa Inggris**

• Tonton film dengan subtitle English
• Dengarkan podcast bahasa Inggris
• Praktik speaking dengan teman/ rekaman
• Baca artikel atau buku bahasa Inggris
• Gunakan aplikasi Duolingo daily

🗣️ *Practice makes perfect! Don't be afraid to make mistakes.*`,

        'ujian|try out|exams': `📝 **Persiapan Menghadapi Ujian**

• Buat jadwal belajar terstruktur
• Fokus pada materi yang sulit terlebih dahulu
• Latihan soal tahun sebelumnya
• Istirahat cukup sebelum ujian
• Datang lebih awal & bawa perlengkapan lengkap

🎯 *Persiapan yang baik = hasil yang maksimal!*`,

        'hai|halo|hi|hello|pagi|siang|malam': `Halo! 👋 Ada yang bisa aku bantu tentang belajar atau tugasmu hari ini?

💡 Coba tanyakan:
• Tips belajar efektif
• Cara mengatasi prokrastinasi
• Motivasi belajar
• Tips matematika atau bahasa Inggris
• Persiapan ujian`
    };

    for (const [pattern, response] of Object.entries(responses)) {
        const keywords = pattern.split('|');
        if (keywords.some(k => msg.includes(k))) {
            return response;
        }
    }

    return `🤔 **Maaf, aku masih belajar untuk menjawab pertanyaan itu.**

💡 Coba tanyakan tentang:
• Tips belajar efektif
• Cara mengatasi prokrastinasi
• Motivasi belajar
• Tips matematika
• Tips bahasa Inggris
• Persiapan ujian

✨ *Semakin spesifik pertanyaanmu, semakin baik jawabanku!*`;
}
