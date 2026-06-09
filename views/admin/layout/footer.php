        </main>
        
        <footer class="bg-white border-t border-purple-100 py-3 px-6 text-center text-sm text-slate-400 z-10 w-full flex-shrink-0">
            Arsip Surat Desa Teluknaga &copy; <?= date('Y') ?>
        </footer>
    </div> <!-- End of flex-1 Main wrapper -->

    <!-- Offline Notification Banner -->
    <div id="offline-banner" class="fixed top-0 left-0 w-full bg-red-600 text-white text-center py-2 z-50 transform -translate-y-full transition-transform duration-300 font-medium text-sm shadow-md flex items-center justify-center gap-2">
        <i class="fa-solid fa-wifi"></i> Koneksi internet terputus. Anda sedang dalam mode luring (offline).
    </div>

    <script>
        function updateOnlineStatus() {
            const banner = document.getElementById('offline-banner');
            if (navigator.onLine) {
                banner.classList.add('-translate-y-full');
            } else {
                banner.classList.remove('-translate-y-full');
            }
        }
        window.addEventListener('online', updateOnlineStatus);
        window.addEventListener('offline', updateOnlineStatus);
        
        // Cek status saat pertama kali load
        if (!navigator.onLine) {
            updateOnlineStatus();
        }

        // Mobile Sidebar Toggle Logic
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const openBtn = document.getElementById('openSidebar');
        const closeBtn = document.getElementById('closeSidebar');

        function toggleSidebar() {
            if (sidebar && overlay) {
                sidebar.classList.toggle('-translate-x-full');
                if (overlay.classList.contains('hidden')) {
                    overlay.classList.remove('hidden');
                    setTimeout(() => overlay.classList.remove('opacity-0'), 10);
                } else {
                    overlay.classList.add('opacity-0');
                    setTimeout(() => overlay.classList.add('hidden'), 300); // Wait for transition
                }
            }
        }

        if(openBtn) openBtn.addEventListener('click', toggleSidebar);
        if(closeBtn) closeBtn.addEventListener('click', toggleSidebar);
        if(overlay) overlay.addEventListener('click', toggleSidebar);

        // Inactivity Auto-Logout Logic (30 menit)
        let inactivityTime = function() {
            let time;
            const logoutUrl = '../../actions/auth/logout.php';
            
            function resetTimer() {
                clearTimeout(time);
                time = setTimeout(function() {
                    alert('Sesi Anda telah berakhir karena tidak ada aktivitas selama 30 menit. Anda akan diarahkan ke halaman login.');
                    window.location.href = logoutUrl;
                }, 1800000); // 30 Menit
            }

            window.onload = resetTimer;
            document.onmousemove = resetTimer;
            document.onkeypress = resetTimer;
            document.ontouchstart = resetTimer;
            document.onclick = resetTimer;
            document.onscroll = resetTimer;
        };
        inactivityTime();
    </script>
</body>
</html>
