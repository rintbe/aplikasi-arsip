        </main>
        
        <footer class="bg-white border-t border-purple-100 py-3 px-6 text-center text-sm text-slate-400 z-10 w-full flex-shrink-0">
            Arsip Surat Desa Teluknaga &copy; <?= date('Y') ?>
        </footer>
    </div> <!-- End of flex-1 Main wrapper -->

    <!-- Optional script for nice transitions or logic can be placed here -->

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmAction(event, url, text) {
            event.preventDefault();
            Swal.fire({
                title: 'Konfirmasi',
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ec4899',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Ya',
                cancelButtonText: 'Batal',
                position: 'center'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        }
    </script>
</body>
</html>
