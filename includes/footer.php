<?php // Footer faylı - Scriptlər və bağlanma tagləri ?>
        </div> <!-- container-fluid bağlanır -->
        
        <!-- Footer -->
        <div class="footer">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">
                        <i class="fas fa-copyright me-1"></i> <?php echo date('Y'); ?> İnsan Hüquqları şöbəsi
                    </p>
                    <small class="text-muted">Bütün hüquqlar qorunur</small>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">
                        <i class="fas fa-code me-1"></i> v1.0.0
                    </p>
                    <small class="text-muted">Milli Proqramlaşdırma Mərkəzi</small>
                </div>
            </div>
        </div>
    </div> <!-- Main Content end -->
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Custom Scripts -->
    <script>
        // Sidebar Toggle - artıq header.php-də var, lakin əlavə funksiyalar
        document.addEventListener('DOMContentLoaded', function() {
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                const sidebar = document.querySelector('.sidebar');
                const toggleBtn = document.getElementById('sidebarToggle');
                if (window.innerWidth <= 992) {
                    if (sidebar && toggleBtn && 
                        !sidebar.contains(event.target) && 
                        !toggleBtn.contains(event.target)) {
                        sidebar.classList.remove('active');
                        const mainContent = document.querySelector('.main-content');
                        if (mainContent) mainContent.style.marginLeft = '0';
                        toggleBtn.innerHTML = '<i class="fas fa-bars"></i>';
                    }
                }
            });
            
            // Confirmation for delete actions
            window.confirmDelete = function(message = 'Bu əməliyyatı təsdiqləyirsiniz?') {
                return confirm(message);
            };
            
            // Show loading state on form submit
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function() {
                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Yüklənir...';
                        submitBtn.disabled = true;
                    }
                });
            });
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                document.querySelectorAll('.alert').forEach(alert => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });

        document.addEventListener('DOMContentLoaded', function () {
            function updateTime() {
                const now = new Date();

                // Bakı UTC+4
                const bakuTime = new Date(
                    now.toLocaleString("en-US", { timeZone: "Asia/Baku" })
                );

                const hours   = String(bakuTime.getHours()).padStart(2, '0');
                const minutes = String(bakuTime.getMinutes()).padStart(2, '0');
                const seconds = String(bakuTime.getSeconds()).padStart(2, '0');

                const timeEl = document.getElementById('currentTime');
                if (timeEl) {
                    timeEl.textContent = `${hours}:${minutes}:${seconds}`;
                }
            }

            updateTime(); // dərhal göstər
            setInterval(updateTime, 1000); // hər saniyə yenilə
        });

        document.addEventListener('DOMContentLoaded', function () {
            const dateEl = document.getElementById('currentDate');
            if (dateEl) {
                const now = new Date(
                    new Date().toLocaleString("en-US", { timeZone: "Asia/Baku" })
                );
                const day = String(now.getDate()).padStart(2, '0');
                const month = String(now.getMonth() + 1).padStart(2, '0');
                const year = now.getFullYear();
                dateEl.textContent = `${day}.${month}.${year}`;
            }
        });
    </script>
</body>
</html>