-- Membuat Database
CREATE DATABASE IF NOT EXISTS focusmate_db;
USE focusmate_db;

-- Tabel Users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    status VARCHAR(50) DEFAULT 'Mahasiswa',
    birth_date DATE,
    avatar_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Tasks (Tugas)
CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    task_name VARCHAR(255) NOT NULL,
    description TEXT,
    task_date DATE,
    start_time TIME,
    end_time TIME,
    priority ENUM('Rendah', 'Sedang', 'Tinggi') DEFAULT 'Sedang',
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel Sesi Belajar (untuk timer Pomodoro)
CREATE TABLE IF NOT EXISTS study_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    duration INT NOT NULL,
    session_type ENUM('focus', 'break') DEFAULT 'focus',
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel Riwayat Chat AI
CREATE TABLE IF NOT EXISTS chat_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    response TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Memasukkan data admin (password: admin123)
-- CATATAN: Ganti 'your_hashed_password' dengan hasil dari password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO users (username, email, password, full_name, role, status) 
VALUES ('admin', 'admin@focusmate.com', '$2y$10$LoIkJQmXxCl.0VZSTPISIO1WF.UKYIpeRsN.QW1TDkQeseeScJeVa', 'Administrator', 'admin', 'Admin');

-- Memasukkan data user contoh (password: user123)
INSERT INTO users (username, email, password, full_name, role, status) 
VALUES ('user1', 'user1@example.com', '$2y$10$/k25.7bkj5wjt0ITxoz7f.wBsUf1JNgSsG07ktaiX0zo0qrF5WxYG', 'John Doe', 'user', 'Mahasiswa');
