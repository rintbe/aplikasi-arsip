CREATE DATABASE IF NOT EXISTS db_arsip_teluknaga;
USE db_arsip_teluknaga;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nik VARCHAR(16) NOT NULL UNIQUE,
    nama_lengkap VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    no_hp VARCHAR(20),
    alamat TEXT,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'warga') DEFAULT 'warga',
    is_active TINYINT(1) DEFAULT 0,
    verification_token VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pengajuan_surat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    jenis_surat VARCHAR(100) NOT NULL,
    keperluan TEXT NOT NULL,
    status ENUM('Pending', 'Proses', 'Selesai', 'Ditolak') DEFAULT 'Pending',
    alasan_penolakan TEXT,
    tgl_pengajuan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    update_terakhir TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE lampiran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pengajuan_id INT NOT NULL,
    nama_file VARCHAR(255) NOT NULL,
    tipe_file VARCHAR(50) NOT NULL,
    path_folder VARCHAR(255) NOT NULL,
    FOREIGN KEY (pengajuan_id) REFERENCES pengajuan_surat(id) ON DELETE CASCADE
);
