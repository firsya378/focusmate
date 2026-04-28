<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
requireLogin();

// Handle chat AI
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = $_POST['message'];
    $response = getAIResponse($message);
    
    // Simpan ke database
    saveChatHistory($_SESSION['user_id'], $message, $response);
    
    header('Content-Type: application/json');
    echo json_encode(['response' => $response]);
    exit();
}

// Ambil riwayat chat
$db = getDB();
$stmt = $db->prepare("SELECT * FROM chat_history WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
$stmt->execute([$_SESSION['user_id']]);
$chatHistory = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Buddy - FocusMate</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .chat-message {
            animation: slideIn 0.3s ease-out;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .typing-indicator span {
            animation: blink 1.4s infinite both;
        }
        .typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
        .typing-indicator span:nth-child(3) { animation-delay: 0.4s; }
        @keyframes blink {
            0% { opacity: 0.1; }
            20% { opacity: 1; }
            100% { opacity: 0.1; }
        }
    </style>
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
                        <a href="ai-buddy.php" class="text-purple-600 font-semibold">AI Buddy</a>
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
        <div class="grid md:grid-cols-3 gap-8">
            <!-- Sidebar Informasi -->
            <div class="md:col-span-1 space-y-6">
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-purple-100">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-purple-600 to-indigo-600 flex items-center justify-center text-white text-2xl">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg">AI Buddy</h3>
                            <p class="text-xs text-slate-500">Siap membantu 24/7</p>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="p-4 bg-purple-50 rounded-2xl border border-purple-100">
                            <div class="flex items-center gap-2 text-purple-700 mb-2">
                                <i class="fas fa-lightbulb text-sm"></i>
                                <span class="font-bold text-sm">Tips Belajar</span>
                            </div>
                            <p class="text-xs text-slate-600 leading-relaxed">Gunakan teknik Pomodoro: 25 menit fokus, 5 menit istirahat untuk hasil maksimal!</p>
                        </div>
                        
                        <div class="p-4 bg-blue-50 rounded-2xl border border-blue-100">
                            <div class="flex items-center gap-2 text-blue-700 mb-2">
                                <i class="fas fa-chart-line text-sm"></i>
                                <span class="font-bold text-sm">Progress Hari Ini</span>
                            </div>
                            <div class="space-y-2">
                                <div class="flex justify-between text-xs">
                                    <span>Sesi Belajar</span>
                                    <span class="font-bold" id="sessionCount">3/5</span>
                                </div>
                                <div class="w-full bg-blue-100 h-1.5 rounded-full">
                                    <div class="bg-blue-500 h-1.5 rounded-full" style="width: 60%"></div>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 bg-green-50 rounded-2xl border border-green-100">
                            <div class="flex items-center gap-2 text-green-700 mb-2">
                                <i class="fas fa-tasks text-sm"></i>
                                <span class="font-bold text-sm">Tugas Aktif</span>
                            </div>
                            <p class="text-2xl font-bold text-green-600" id="activeTasks">0 <span class="text-xs font-normal text-slate-500">tersisa</span></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Area Chat AI -->
            <div class="md:col-span-2">
                <div class="bg-white rounded-3xl shadow-lg border border-purple-100 overflow-hidden">
                    <!-- Header Chat -->
                    <div class="p-4 border-b border-purple-100 bg-gradient-to-r from-purple-50 to-white">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-600 to-indigo-600 flex items-center justify-center text-white">
                                <i class="fas fa-comment-dots"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-purple-700">Asisten Belajar AI</h4>
                                <p class="text-xs text-slate-500">Online • Siap membantu tugas dan belajarmu</p>
                            </div>
                        </div>
                    </div>

                    <!-- Pesan Chat -->
                    <div id="chatMessages" class="h-96 overflow-y-auto p-6 space-y-4 bg-slate-50/50">
                        <!-- Pesan Sambutan -->
                        <div class="flex gap-3 chat-message">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-600 to-indigo-600 flex items-center justify-center text-white text-sm flex-shrink-0">
                                <i class="fas fa-robot"></i>
                            </div>
                            <div class="bg-white p-3 rounded-2xl rounded-tl-none shadow-sm max-w-[80%]">
                                <p class="text-sm text-slate-700">Halo! 👋 Aku AI Buddy, asisten belajarmu. Ada yang bisa aku bantu? Kamu bisa bertanya tentang:</p>
                                <div class="flex flex-wrap gap-2 mt-2">
                                    <span class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded-full">✏️ Tips belajar</span>
                                    <span class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded-full">📚 Materi pelajaran</span>
                                    <span class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded-full">⏰ Manajemen waktu</span>
                                    <span class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded-full">🎯 Motivasi</span>
                                </div>
                            </div>
                        </div>
                        
                        <?php foreach (array_reverse($chatHistory) as $chat): ?>
                        <div class="flex gap-3 chat-message justify-end">
                            <div class="bg-purple-600 text-white p-3 rounded-2xl rounded-tr-none max-w-[80%]">
                                <p class="text-sm"><?php echo htmlspecialchars($chat['message']); ?></p>
                            </div>
                            <div class="w-8 h-8 rounded-lg bg-purple-200 flex items-center justify-center text-purple-700 text-sm flex-shrink-0">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                        <div class="flex gap-3 chat-message">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-600 to-indigo-600 flex items-center justify-center text-white text-sm flex-shrink-0">
                                <i class="fas fa-robot"></i>
                            </div>
                            <div class="bg-white p-3 rounded-2xl rounded-tl-none shadow-sm max-w-[80%]">
                                <p class="text-sm text-slate-700"><?php echo nl2br(htmlspecialchars($chat['response'])); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Input Chat -->
                    <div class="p-4 border-t border-purple-100 bg-white">
                        <form id="chatForm" class="flex gap-3">
                            <input type="text" id="aiMessage" placeholder="Tanya AI Buddy..." class="flex-1 p-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-purple-500 outline-none transition" autocomplete="off">
                            <button type="submit" class="bg-purple-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-purple-700 shadow-md transition-all">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>
                        <div class="flex gap-2 mt-3">
                            <button onclick="quickQuestion('tips belajar efektif')" class="text-xs bg-slate-100 hover:bg-purple-100 text-slate-600 px-3 py-1.5 rounded-full transition-colors">Tips belajar</button>
                            <button onclick="quickQuestion('cara mengatasi prokrastinasi')" class="text-xs bg-slate-100 hover:bg-purple-100 text-slate-600 px-3 py-1.5 rounded-full transition-colors">Atasi prokrastinasi</button>
                            <button onclick="quickQuestion('motivasi belajar')" class="text-xs bg-slate-100 hover:bg-purple-100 text-slate-600 px-3 py-1.5 rounded-full transition-colors">Motivasi</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const chatMessages = document.getElementById('chatMessages');
        const chatForm = document.getElementById('chatForm');
        const aiMessage = document.getElementById('aiMessage');

        chatForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const message = aiMessage.value.trim();
            if (!message) return;

            // Tampilkan pesan user
            addMessage(message, 'user');
            aiMessage.value = '';

            // Tampilkan indikator typing
            showTypingIndicator();

            try {
                const response = await fetch('ai-buddy.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'message=' + encodeURIComponent(message)
                });
                const data = await response.json();
                removeTypingIndicator();
                addMessage(data.response, 'ai');
            } catch (error) {
                removeTypingIndicator();
                addMessage('Maaf, terjadi kesalahan. Silakan coba lagi.', 'ai');
            }
        });

        function addMessage(text, sender) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `flex gap-3 chat-message ${sender === 'user' ? 'justify-end' : ''}`;
            
            if (sender === 'user') {
                messageDiv.innerHTML = `
                    <div class="bg-purple-600 text-white p-3 rounded-2xl rounded-tr-none max-w-[80%]">
                        <p class="text-sm">${escapeHtml(text)}</p>
                    </div>
                    <div class="w-8 h-8 rounded-lg bg-purple-200 flex items-center justify-center text-purple-700 text-sm flex-shrink-0">
                        <i class="fas fa-user"></i>
                    </div>
                `;
            } else {
                messageDiv.innerHTML = `
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-600 to-indigo-600 flex items-center justify-center text-white text-sm flex-shrink-0">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="bg-white p-3 rounded-2xl rounded-tl-none shadow-sm max-w-[80%]">
                        <p class="text-sm text-slate-700">${nl2br(escapeHtml(text))}</p>
                    </div>
                `;
            }
            
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function showTypingIndicator() {
            const indicator = document.createElement('div');
            indicator.id = 'typingIndicator';
            indicator.className = 'flex gap-3 chat-message';
            indicator.innerHTML = `
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-600 to-indigo-600 flex items-center justify-center text-white text-sm flex-shrink-0">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="bg-white p-4 rounded-2xl rounded-tl-none shadow-sm">
                    <div class="typing-indicator flex gap-1">
                        <span class="w-2 h-2 bg-purple-600 rounded-full"></span>
                        <span class="w-2 h-2 bg-purple-600 rounded-full"></span>
                        <span class="w-2 h-2 bg-purple-600 rounded-full"></span>
                    </div>
                </div>
            `;
            chatMessages.appendChild(indicator);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function removeTypingIndicator() {
            const indicator = document.getElementById('typingIndicator');
            if (indicator) indicator.remove();
        }

        function quickQuestion(question) {
            aiMessage.value = question;
            chatForm.dispatchEvent(new Event('submit'));
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function nl2br(str) {
            return str.replace(/\n/g, '<br>');
        }

        // Load statistik
        async function loadStats() {
            try {
                const response = await fetch('get_stats.php');
                const stats = await response.json();
                document.getElementById('activeTasks').innerHTML = `${stats.activeTasks} <span class="text-xs font-normal text-slate-500">tersisa</span>`;
            } catch(e) {}
        }
        loadStats();
    </script>
</body>
</html>