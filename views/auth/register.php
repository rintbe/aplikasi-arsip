<?php
// views/auth/register.php
require_once '../../config/db_connect.php';

// Jika sudah login, arahkan ke halaman utama
if (isset($_SESSION['user_id'])) {
    header("Location: ../warga/dashboard_warga.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Arsip Teluknaga</title>
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
            padding: 20px;
            box-sizing: border-box;
        }

        .auth-container {
            width: 100%;
            max-width: 500px;
        }

        .card {
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(74, 20, 140, 0.15);
            padding: 40px 30px;
            border-top: 5px solid var(--secondary-color);
        }

        .card-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .card-header h2 {
            margin: 0;
            color: var(--primary-color);
            font-size: 26px;
        }

        .card-header p {
            color: var(--text-muted);
            margin-top: 8px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-row .form-group {
            flex: 1;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
            color: var(--primary-color);
            font-size: 13px;
        }

        .form-control {
            width: 100%;
            padding: 10px 14px;
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
            background: linear-gradient(135deg, var(--secondary-color), #d81b60);
            color: white;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            transition: transform 0.2s, box-shadow 0.2s;
            margin-top: 15px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(233, 30, 99, 0.3);
        }

        .auth-links {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }

        .auth-links a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }

        .auth-links a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-success { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-danger { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    </style>
</head>
<body>

<div class="auth-container">
    <div class="card">
        <div class="card-header">
            <h2>Pendaftaran Warga</h2>
            <p>Lengkapi data diri Anda untuk membuat akun</p>
        </div>

        <?php display_flash_message(); ?>

        <form action="../../actions/auth/register_process.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            
            <div class="form-group">
                <label for="nik">NIK (16 Digit)</label>
                <input type="text" id="nik" name="nik" class="form-control" required placeholder="3603xxxxxxxxxxxx" minlength="16" maxlength="16" pattern="[0-9]{16}">
            </div>
            
            <div class="form-group">
                <label for="nama_lengkap">Nama Lengkap</label>
                <input type="text" id="nama_lengkap" name="nama_lengkap" class="form-control" required placeholder="Sesuai KTP">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email">Alamat Email</label>
                    <input type="email" id="email" name="email" class="form-control" required placeholder="nama@email.com">
                </div>
                <div class="form-group">
                    <label for="no_hp">No. WhatsApp/HP</label>
                    <input type="text" id="no_hp" name="no_hp" class="form-control" required placeholder="08xxxxxxxxxx">
                </div>
            </div>
            
            <div class="form-group">
                <label for="alamat">Alamat Lengkap</label>
                <textarea id="alamat" name="alamat" class="form-control" required placeholder="Masukkan alamat lengkap Anda sesuai KTP" rows="3" style="resize: vertical;"></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password (Min. 8 karakter)</label>
                    <div style="position: relative;">
                        <input type="password" id="password" name="password" class="form-control" required minlength="8" placeholder="••••••••" style="padding-right: 40px;">
                        <span id="togglePassword" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--text-muted);" title="Tampilkan Password">
                            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password</label>
                    <div style="position: relative;">
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="8" placeholder="••••••••" style="padding-right: 40px;">
                        <span id="toggleConfirmPassword" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--text-muted);" title="Tampilkan Password">
                            <svg id="eyeIconConfirm" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn-submit">Daftar Akun</button>
        </form>

        <div class="auth-links">
            Sudah punya akun? <a href="login.php">Masuk di sini</a>
        </div>
    </div>
</div>

<script>
    function setupPasswordToggle(toggleId, inputId, iconId) {
        const toggleBtn = document.querySelector(toggleId);
        const inputField = document.querySelector(inputId);
        const iconSvg = document.querySelector(iconId);

        if(!toggleBtn || !inputField || !iconSvg) return;

        toggleBtn.addEventListener('click', function () {
            const type = inputField.getAttribute('type') === 'password' ? 'text' : 'password';
            inputField.setAttribute('type', type);
            
            if (type === 'text') {
                iconSvg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />';
                this.style.color = 'var(--secondary-color)';
            } else {
                iconSvg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
                this.style.color = 'var(--text-muted)';
            }
        });
    }

    setupPasswordToggle('#togglePassword', '#password', '#eyeIcon');
    setupPasswordToggle('#toggleConfirmPassword', '#confirm_password', '#eyeIconConfirm');
</script>

</body>
</html>
