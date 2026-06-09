<?php
// views/auth/reset_password.php
require_once '../../config/db_connect.php';

// Validasi Token Reset
if (!isset($_GET['token']) || !isset($_SESSION['reset_token']) || $_GET['token'] !== $_SESSION['reset_token'] || !isset($_SESSION['reset_user_id'])) {
    set_flash_message('danger', 'Sesi reset password tidak valid atau telah kedaluwarsa. Silakan ulangi proses lupa password.');
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Arsip Teluknaga</title>
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
            <h2>Reset Password</h2>
            <p>Masukkan password baru Anda</p>
        </div>

        <?php display_flash_message(); ?>

        <form action="../../actions/auth/reset_password_process.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'], ENT_QUOTES, 'UTF-8') ?>">
            
            <div class="form-group">
                <label for="password">Password Baru</label>
                <div style="position: relative;">
                    <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••" style="padding-right: 40px;">
                    <span id="togglePassword" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--text-muted);" title="Tampilkan Password">
                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </span>
                </div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password Baru</label>
                <div style="position: relative;">
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required placeholder="••••••••" style="padding-right: 40px;">
                    <span id="toggleConfirmPassword" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--text-muted);" title="Tampilkan Password">
                        <svg id="eyeConfirmIcon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </span>
                </div>
            </div>
            
            <button type="submit" class="btn-submit">Simpan Password Baru</button>
        </form>
    </div>
</div>

<script>
    function setupTogglePassword(toggleBtnId, inputId, iconId) {
        const toggleBtn = document.querySelector(toggleBtnId);
        const input = document.querySelector(inputId);
        const icon = document.querySelector(iconId);

        if (toggleBtn && input && icon) {
            toggleBtn.addEventListener('click', function () {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                
                if (type === 'text') {
                    icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />';
                    this.style.color = 'var(--primary-color)';
                } else {
                    icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
                    this.style.color = 'var(--text-muted)';
                }
            });
        }
    }

    setupTogglePassword('#togglePassword', '#password', '#eyeIcon');
    setupTogglePassword('#toggleConfirmPassword', '#confirm_password', '#eyeConfirmIcon');
</script>

</body>
</html>
