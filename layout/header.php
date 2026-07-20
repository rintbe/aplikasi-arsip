<?php
require_once 'config.php';
check_login();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title : 'Dashboard' ?> - Arsip Surat Desa Teluknaga</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #fdf2f8; /* pink-50 */
            font-family: 'Inter', sans-serif;
        }
        .bg-glass {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
        }
        .main-gradient {
            background: linear-gradient(135deg, #e9d5ff 0%, #fbcfe8 100%);
        }
        /* Flash Message Styles */
        .alert {
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            position: relative;
            animation: slideDown 0.3s ease-out;
        }
        .alert-success { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-danger { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .alert-warning { background-color: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
        .alert-info { background-color: #dbeafe; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .close-btn {
            position: absolute;
            top: 10px;
            right: 15px;
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: inherit;
        }
        @keyframes slideDown {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body class="text-slate-800 flex h-screen overflow-hidden">
    
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <!-- Top Navbar -->
        <header class="bg-glass border-b border-purple-100 py-4 px-6 flex justify-between items-center shadow-sm z-10 w-full">
            <h2 class="text-xl font-semibold text-purple-800">
                <?= isset($page_title) ? $page_title : 'Dashboard' ?>
            </h2>
            <div class="flex items-center space-x-4">
                <span class="text-sm font-medium text-slate-600 border px-3 py-1 bg-white rounded-full shadow-sm">
                    <i class="fa-solid fa-user-circle text-purple-500 mr-2"></i><?= htmlspecialchars($_SESSION['nama_lengkap']) ?>
                </span>
                <a href="logout.php" onclick="confirmAction(event, this.href, 'Yakin ingin keluar?');" class="text-pink-600 hover:text-white hover:bg-pink-500 border border-pink-200 px-3 py-1 rounded-full text-sm font-medium transition-colors">
                    <i class="fa-solid fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </header>
        
        <!-- Main Scrollable Content -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#f8fafc] p-6">
