<?php
// views/auth/forgot_password.php
require_once '../../config/db_connect.php';

// Jika sudah login, redirect sesuai role
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: ../admin/dashboard_admin.php");
    } else {
        header("Location: ../warga/dashboard_warga.php");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Arsip Teluknaga</title>
    <style>
        :root {
            --primary-color: #4a148c; /* Ungu Tua */
            --secondary-color: #e91e63; /* Merah Muda */
            --bg-color: #fdf2f8;
            --card-bg: #ffffff;
            --text-main: #1f2937;
            --text-muted: #6b7280;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .auth-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }

        .card {
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(74, 20, 140, 0.15);
            padding: 40px 30px;
            border-top: 5px solid var(--primary-color);
        }

        .card-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .card-header h2 {
            margin: 0;
            color: var(--primary-color);
            font-size: 28px;
        }

        .card-header p {
            color: var(--text-muted);
            margin-top: 10px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--primary-color);
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            box-sizing: border-box;
            font-family: inherit;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(233, 30, 99, 0.2);
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--primary-color), #7b1fa2);
            color: white;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            transition: transform 0.2s, box-shadow 0.2s;
            margin-top: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(74, 20, 140, 0.3);
        }

        .auth-links {
            text-align: center;
            margin-top: 25px;
            font-size: 14px;
        }

        .auth-links a {
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 600;
        }

        .auth-links a:hover {
            text-decoration: underline;
        }
        
        /* Flash Message Alert Styles */
        .alert {
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-success { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-danger { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .alert-warning { background-color: #fef08a; color: #854d0e; border: 1px solid #fde047; }
        .alert-info { background-color: #e0f2fe; color: #075985; border: 1px solid #bae6fd; }
    </style>
</head>
<body>

<div class="auth-container">
    <div class="card">
        <div class="card-header">
            <h2>Lupa Password</h2>
            <p>Konfirmasi NIK dan Email untuk mereset password</p>
        </div>

        <?php display_flash_message(); ?>

        <form action="../../actions/auth/forgot_password_process.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            
            <div class="form-group">
                <label for="nik">NIK</label>
                <input type="text" id="nik" name="nik" class="form-control" required placeholder="Masukkan 16 digit NIK">
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" required placeholder="Masukkan email terdaftar">
            </div>
            
            <button type="submit" class="btn-submit">Verifikasi</button>
        </form>

        <div class="auth-links">
            <a href="login.php">Kembali ke halaman Login</a>
        </div>
    </div>
</div>

</body>
</html>
