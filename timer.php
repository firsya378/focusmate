<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timer Fokus - FocusMate</title>
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
                        <a href="timer.php" class="text-purple-600 font-semibold">Timer</a>
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
        <div class="max-w-2xl mx-auto text-center">
            <h2 class="text-3xl font-bold mb-8">Timer Fokus Pomodoro</h2>
            <div class="purple-gradient bg-gradient-to-br from-purple-600 to-indigo-600 p-12 rounded-3xl shadow-2xl text-white">
                <div id="display" class="text-9xl font-black mb-10 tracking-tighter">25:00</div>
                <div class="flex justify-center gap-6">
                    <button onclick="toggleTimer()" id="btnStart" class="bg-white text-purple-700 px-10 py-4 rounded-2xl font-bold text-xl btn-interact shadow-lg hover:bg-gray-100 transition">
                        MULAI
                    </button>
                    <button onclick="resetTimer()" class="bg-purple-800 text-white px-10 py-4 rounded-2xl font-bold text-xl btn-interact shadow-lg hover:bg-purple-900 transition">
                        RESET
                    </button>
                </div>
            </div>
            <div class="mt-8 bg-white rounded-xl shadow-md p-6">
                <h3 class="font-bold text-lg mb-2">📖 Tips Penggunaan</h3>
                <p class="text-gray-600">Gunakan teknik Pomodoro: 25 menit fokus, 5 menit istirahat. Setelah 4 sesi, istirahatlah selama 15-30 menit!</p>
            </div>
        </div>
    </div>

    <script>
        let timer;
        let timeLeft = 1500;
        let isRunning = false;

        function toggleTimer() {
            const btn = document.getElementById('btnStart');
            if (!isRunning) {
                isRunning = true;
                btn.innerText = "JEDA";
                btn.classList.add('bg-yellow-400', 'text-white');
                timer = setInterval(() => {
                    timeLeft--;
                    updateDisplay();
                    if (timeLeft <= 0) { 
                        clearInterval(timer);
                        alert("🎉 Sesi Fokus Selesai! Waktunya istirahat sebentar.");
                        // Simpan sesi belajar
                        fetch('save_session.php', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                            body: 'duration=25&type=focus'
                        });
                        resetTimer();
                    }
                }, 1000);
            } else {
                isRunning = false;
                btn.innerText = "LANJUT";
                btn.classList.remove('bg-yellow-400');
                clearInterval(timer);
            }
        }

        function updateDisplay() {
            const min = Math.floor(timeLeft / 60);
            const sec = timeLeft % 60;
            document.getElementById('display').innerText = `${min.toString().padStart(2,'0')}:${sec.toString().padStart(2,'0')}`;
        }

        function resetTimer() {
            clearInterval(timer);
            timeLeft = 1500;
            isRunning = false;
            updateDisplay();
            const btn = document.getElementById('btnStart');
            btn.innerText = "MULAI";
            btn.classList.remove('bg-yellow-400');
        }
    </script>
</body>
</html>
