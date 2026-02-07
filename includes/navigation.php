<?php 
// navigation.php - Sidebar navigasiya 
$current_page = basename($_SERVER['PHP_SELF']); 
$current_dir = basename(dirname($_SERVER['PHP_SELF'])); 

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';

// Cari istifadəçinin ad və soyadını users cədvəlindən götür
$user_fullname = "İstifadəçi";
$user_role = "qeydiyyatdan keçməyib";

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // users cədvəlindən fullname və rol götürürük
    $stmt = $conn->prepare("SELECT fullname, role FROM users WHERE id = ?");
    
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $row = $result->fetch_assoc()) {
            if (!empty($row['fullname'])) {
                $user_fullname = $row['fullname'];
            } else {
                // Əgər fullname boşdursa, username göstər
                $stmt2 = $conn->prepare("SELECT username FROM users WHERE id = ?");
                $stmt2->bind_param("i", $user_id);
                $stmt2->execute();
                $result2 = $stmt2->get_result();
                
                if ($result2 && $row2 = $result2->fetch_assoc()) {
                    $user_fullname = $row2['username'];
                }
            }
            
            $user_role = $row['role'] === 'superadmin' ? 'Super Admin' : 'İşçi';
        }
    }
}
?>

<!-- Sidebar Toggle Button (Mobile only) -->
<button class="sidebar-toggle" id="sidebarToggle">
    <i class="fas fa-bars"></i>
</button>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="sidebar" id="mainSidebar">
    <div class="sidebar-header">
        <img src="/sis/assets/images/logo.png" alt="Logo" class="sidebar-logo">
        <h6 class="text-white fw-bold mb-0">İnsan Hüquqları Şöbəsi</h6>
        <small class="text-white-50">Rəqəmsal İdarəetmə</small>
        
        <div class="user-info mt-4 p-2 bg-white bg-opacity-10 rounded-3">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-user-circle fa-2x text-info"></i>
                </div>
                <div class="ms-3 text-start">
                    <p class="mb-0 small fw-bold text-white"><?php echo htmlspecialchars($user_fullname); ?></p>
                    <span class="badge bg-info" style="font-size: 0.6rem;"><?php echo $user_role; ?></span>
                </div>
            </div>
        </div>
    </div>

    <ul class="sidebar-nav">
        <?php if (isSuperAdmin()): ?>
        <li>
            <a href="/sis/dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i> Dashboard
            </a>
        </li>
        <?php endif; ?>
        
        <li>
            <a href="/sis/crud/weekly_works/index.php" class="<?php echo ($current_page == 'index.php' && $current_dir == 'weekly_works') ? 'active' : ''; ?>">
                <i class="fas fa-tasks"></i> Həftəlik İşlər
            </a>
        </li>

        <?php if (isSuperAdmin()): ?>
        <li>
            <a href="/sis/crud/employees/index.php" class="<?php echo ($current_page == 'index.php' && $current_dir == 'employees') ? 'active' : ''; ?>">
                <i class="fas fa-user-tie"></i> İşçilər
            </a>
        </li>
        <li>
            <a href="/sis/crud/sector-vezife/index.php" class="<?php echo ($current_page == 'index.php' && $current_dir == 'sector-vezife') ? 'active' : ''; ?>">
                <i class="fas fa-sitemap"></i> Struktur
            </a>
        </li>
        <?php endif; ?>

        <li>
            <a href="/sis/crud/users/index.php" class="<?php echo ($current_page == 'index.php' && $current_dir == 'users') ? 'active' : ''; ?>">
                <i class="fas fa-users-cog"></i> İdarəçilər
            </a>
        </li>

        <li class="mt-auto">
            <a href="/sis/logout.php" class="text-danger">
                <i class="fas fa-power-off"></i> Çıxış
            </a>
        </li>
    </ul>
</aside>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('mainSidebar');
    const toggleBtn = document.getElementById('sidebarToggle');
    const overlay = document.getElementById('sidebarOverlay');
    
    // Varsa yadda saxla
    const isMobile = window.innerWidth <= 992;
    
    function toggleSidebar() {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('show');
        document.body.style.overflow = sidebar.classList.contains('active') && isMobile ? 'hidden' : '';
    }
    
    function closeSidebar() {
        sidebar.classList.remove('active');
        overlay.classList.remove('show');
        document.body.style.overflow = '';
    }
    
    // Toggle düyməsi
    if(toggleBtn) {
        toggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleSidebar();
        });
    }
    
    // Overlay klik
    if(overlay) {
        overlay.addEventListener('click', closeSidebar);
    }
    
    // ESC düyməsi
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeSidebar();
        }
    });
    
    // Linklərə klik edəndə mobilde bağla
    if (isMobile) {
        document.querySelectorAll('.sidebar-nav a').forEach(link => {
            link.addEventListener('click', closeSidebar);
        });
    }
    
    // Ekran ölçüsü dəyişdikdə
    window.addEventListener('resize', function() {
        if (window.innerWidth > 992) {
            closeSidebar();
        }
    });
});
</script>