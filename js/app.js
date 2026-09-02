// js/app.js - Core Functions

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
        // Seed admin jika belum ada
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
        // Simple hash (untuk demo, di real app pakai bcrypt)
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
        // Save session
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
    
    // Update session jika sedang login
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
    
    if (msg.includes('tips belajar') || msg.includes('cara belajar')) {
        return "📚 Tips Belajar Efektif:\n\n1️⃣ Gunakan teknik Pomodoro (25 menit belajar, 5 menit istirahat)\n2️⃣ Buat catatan ringkas dengan mind mapping\n3️⃣ Ajarkan kembali ke orang lain\n4️⃣ Cari lingkungan yang nyaman dan minim gangguan\n5️⃣ Istirahat cukup dan jaga kesehatan";
    }
    else if (msg.includes('prokrastinasi') || msg.includes('malas')) {
        return "🎯 Cara Mengatasi Prokrastinasi:\n\n• Mulai dengan tugas kecil selama 2 menit\n• Buat deadline pribadi lebih awal\n• Matikan notifikasi HP saat belajar\n• Beri reward setelah menyelesaikan tugas\n• Ingat tujuan jangka panjangmu!";
    }
    else if (msg.includes('motivasi')) {
        return "💪 Motivasi Hari Ini:\n\n\"Kesuksesan bukanlah akhir, kegagalan bukanlah hal yang fatal. Yang terpenting adalah keberanian untuk melanjutkan.\" - Winston Churchill\n\nYuk, mulai belajar! Setiap langkah kecil hari ini akan membawamu lebih dekat ke impianmu! ✨";
    }
    else if (msg.includes('matematika') || msg.includes('mtk')) {
        return "🧮 Tips Belajar Matematika:\n\n• Pahami konsep dasar, jangan hanya menghafal rumus\n• Latihan soal secara rutin\n• Gunakan aplikasi seperti Photomath untuk bantuan\n• Tonton video tutorial di YouTube\n• Bergabung dengan grup diskusi matematika";
    }
    else if (msg.includes('bahasa inggris') || msg.includes('english')) {
        return "🇬🇧 Tips Belajar Bahasa Inggris:\n\n• Tonton film dengan subtitle English\n• Dengarkan podcast bahasa Inggris\n• Praktik speaking dengan teman\n• Baca artikel atau buku bahasa Inggris\n• Gunakan aplikasi Duolingo untuk latihan daily";
    }
    else if (msg.includes('ujian') || msg.includes('try out')) {
        return "📝 Persiapan Menghadapi Ujian:\n\n• Buat jadwal belajar terstruktur\n• Fokus pada materi yang sulit terlebih dahulu\n• Latihan soal tahun sebelumnya\n• Istirahat cukup sebelum ujian\n• Datang lebih awal dan bawa perlengkapan lengkap";
    }
    else if (msg.includes('hai') || msg.includes('halo') || msg.includes('hi') || msg.includes('hello')) {
        return "Halo! 👋 Ada yang bisa aku bantu tentang belajar atau tugasmu hari ini?";
    }
    else {
        return "Maaf, aku masih belajar untuk menjawab pertanyaan itu. Coba tanya tentang:\n\n• Tips belajar\n• Cara mengatasi prokrastinasi\n• Motivasi belajar\n• Tips matematika\n• Tips bahasa Inggris\n• Persiapan ujian";
    }
}