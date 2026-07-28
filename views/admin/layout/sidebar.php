<!-- views/admin/layout/sidebar.php -->
<?php 
$current_page = basename($_SERVER['PHP_SELF']); 
?>
<aside id="sidebar" class="w-64 bg-glass border-r border-purple-100 shadow-[4px_0_24px_rgba(0,0,0,0.02)] z-30 fixed inset-y-0 left-0 transform -translate-x-full md:relative md:translate-x-0 transition duration-200 ease-in-out flex flex-col">
    <div class="p-6 text-center border-b border-purple-100 flex-shrink-0 main-gradient relative">
        <button id="closeSidebar" class="md:hidden absolute top-4 right-4 text-purple-600 hover:text-purple-800 transition-colors">
            <i class="fa-solid fa-times text-xl"></i>
        </button>
        <div class="w-20 h-auto mx-auto mb-3 flex items-center justify-center drop-shadow-md">
            <img src="../../assets/images/logo.png" alt="Logo Desa Teluknaga" class="w-full h-auto object-contain">
        </div>
        <h1 class="text-lg font-bold text-slate-800">Arsip Desa</h1>
        <p class="text-xs text-purple-700 font-medium tracking-wide border border-purple-300 rounded-full px-2 py-0.5 inline-block mt-1 bg-white/50 backdrop-blur-sm">TELUKNAGA</p>
    </div>

    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
        
        <a href="dashboard_admin.php" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg transition-colors group <?= $current_page == 'dashboard_admin.php' ? 'bg-purple-600 text-white shadow-md' : 'text-slate-600 hover:bg-purple-100 hover:text-purple-700' ?>">
            <i class="fa-solid fa-home w-6 text-center transition-colors <?= $current_page == 'dashboard_admin.php' ? 'text-white' : 'text-purple-400 group-hover:text-purple-600' ?>"></i>
            Dashboard
        </a>

        <a href="daftar_warga.php" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg transition-colors group <?= $current_page == 'daftar_warga.php' ? 'bg-purple-600 text-white shadow-md' : 'text-slate-600 hover:bg-purple-100 hover:text-purple-700' ?>">
            <i class="fa-solid fa-users w-6 text-center transition-colors <?= $current_page == 'daftar_warga.php' ? 'text-white' : 'text-purple-400 group-hover:text-purple-600' ?>"></i>
            Data Warga
        </a>

        <a href="daftar_pengajuan.php" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors group mb-1 mt-1 <?= $current_page == 'daftar_pengajuan.php' ? 'bg-purple-600 text-white shadow-md' : 'text-slate-600 hover:bg-purple-100 hover:text-purple-700' ?>">
            <i class="fa-solid fa-file-signature w-6 text-center transition-colors <?= $current_page == 'daftar_pengajuan.php' ? 'text-white' : 'text-indigo-400 group-hover:text-indigo-600' ?>"></i>
            Pengajuan Warga
        </a>
        
        <div class="pt-4 pb-2 px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">
            Surat Keterangan
        </div>

        <a href="kematian.php" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors group mb-1 <?= $current_page == 'kematian.php' ? 'bg-purple-600 text-white shadow-md' : 'text-slate-600 hover:bg-purple-100 hover:text-purple-700' ?>">
            <i class="fa-solid fa-book-dead w-6 text-center transition-colors <?= $current_page == 'kematian.php' ? 'text-white' : 'text-purple-400 group-hover:text-purple-600' ?>"></i>
            Surat Kematian
        </a>
        
        <a href="pernikahan.php" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors group mb-1 <?= $current_page == 'pernikahan.php' ? 'bg-purple-600 text-white shadow-md' : 'text-slate-600 hover:bg-purple-100 hover:text-purple-700' ?>">
            <i class="fa-solid fa-ring w-6 text-center transition-colors <?= $current_page == 'pernikahan.php' ? 'text-white' : 'text-purple-400 group-hover:text-purple-600' ?>"></i>
            Surat Pernikahan
        </a>

        <a href="usaha.php" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors group mb-1 <?= $current_page == 'usaha.php' ? 'bg-purple-600 text-white shadow-md' : 'text-slate-600 hover:bg-purple-100 hover:text-purple-700' ?>">
            <i class="fa-solid fa-store w-6 text-center transition-colors <?= $current_page == 'usaha.php' ? 'text-white' : 'text-purple-400 group-hover:text-purple-600' ?>"></i>
            Surat Usaha
        </a>

        <a href="pindah.php" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors group mb-1 <?= $current_page == 'pindah.php' ? 'bg-purple-600 text-white shadow-md' : 'text-slate-600 hover:bg-purple-100 hover:text-purple-700' ?>">
            <i class="fa-solid fa-truck-fast w-6 text-center transition-colors <?= $current_page == 'pindah.php' ? 'text-white' : 'text-purple-400 group-hover:text-purple-600' ?>"></i>
            Surat Pindah Penduduk
        </a>

        <a href="domisili.php" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors group mb-1 <?= $current_page == 'domisili.php' ? 'bg-purple-600 text-white shadow-md' : 'text-slate-600 hover:bg-purple-100 hover:text-purple-700' ?>">
            <i class="fa-solid fa-map-location-dot w-6 text-center transition-colors <?= $current_page == 'domisili.php' ? 'text-white' : 'text-purple-400 group-hover:text-purple-600' ?>"></i>
            Surat Domisili
        </a>

        <div class="pt-4 pb-2 px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">
            Eksternal
        </div>

        <a href="masuk.php" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors group mb-1 <?= $current_page == 'masuk.php' ? 'bg-purple-600 text-white shadow-md' : 'text-slate-600 hover:bg-purple-100 hover:text-purple-700' ?>">
            <i class="fa-solid fa-inbox w-6 text-center transition-colors <?= $current_page == 'masuk.php' ? 'text-white' : 'text-purple-400 group-hover:text-purple-600' ?>"></i>
            Surat Masuk (Instansi)
        </a>

        <a href="keluar.php" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors group mb-1 <?= $current_page == 'keluar.php' ? 'bg-purple-600 text-white shadow-md' : 'text-slate-600 hover:bg-purple-100 hover:text-purple-700' ?>">
            <i class="fa-solid fa-paper-plane w-6 text-center transition-colors <?= $current_page == 'keluar.php' ? 'text-white' : 'text-purple-400 group-hover:text-purple-600' ?>"></i>
            Surat Keluar (Instansi)
        </a>
    </nav>
</aside>
