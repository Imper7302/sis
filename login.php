<?php
// login.php
session_start(); // Session başlatmaq lazımdır

require_once 'config/database.php';


$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = clean_input($_POST['username']);
    $password = $_POST['password'];

    // YALNIZ aktiv istifadəçilər
    $sql = "SELECT * FROM users WHERE username = ? AND is_active = 1";

    try {
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            throw new Exception("SQL hazırlama xətası");
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {

                $_SESSION['user_id']   = $user['id'];
                $_SESSION['username']  = $user['username'];
                $_SESSION['role']      = $user['role'];
                $_SESSION['last_login']= time();
                $_SESSION['fullname']  = $user['fullname'] ?? $user['username'];

                // Əgər işçi roludursa employee_id tap
                if ($user['role'] === 'isci') {
                    $emp_sql = "SELECT id FROM employees WHERE user_id = ?";
                    $emp_stmt = $conn->prepare($emp_sql);
                    $emp_stmt->bind_param("i", $user['id']);
                    $emp_stmt->execute();
                    $emp_result = $emp_stmt->get_result();

                    if ($emp_result->num_rows === 1) {
                        $employee = $emp_result->fetch_assoc();
                        $_SESSION['employee_id'] = $employee['id'];
                    }
                    $emp_stmt->close();
                }

                // Son giriş vaxtı
                $update_sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("i", $user['id']);
                $update_stmt->execute();
                $update_stmt->close();

                // Login logu
                if (!is_dir(__DIR__ . '/logs')) {
                    mkdir(__DIR__ . '/logs', 0755, true);
                }

                $logMessage = date('Y-m-d H:i:s') . " | USER_ID: {$user['id']} | LOGIN | {$user['username']} | ROLE: {$user['role']}" . PHP_EOL;
                file_put_contents(__DIR__ . '/logs/log_' . date('Y_m_d') . '.txt', $logMessage, FILE_APPEND);

                // ===== YÖNLƏNDİRMƏ DƏYİŞİKLİYİ =====
                // Superadmin dashboard.php-yə, isci weekly_works/index.php-yə yönləndirilir
                if ($user['role'] === 'superadmin') {
                    header("Location: /sis/dashboard.php");
                } else {
                    header("Location: /sis/crud/weekly_works/index.php");
                }
                exit();

            } else {
                $error = "İstifadəçi adı və ya şifrə yanlışdır!";
            }
        } else {
            // ya user yoxdur, ya da deaktivdir
            $error = "Hesab deaktiv edilib və ya məlumatlar yanlışdır!";
        }

        $stmt->close();

    } catch (Exception $e) {
        $error = "Sistem xətası: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İnsan Hüquqları şöbəsi | Rəsmi Admin Girişi</title>
    <link rel="icon" type="image/png" href="/sis/assets/images/logo.png">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Rəsmi Dövlət Dizaynı Stili -->
     <link rel="stylesheet" href="/sis/assets/css/login.css">

</head>
<body>
    <div class="container login-wrapper">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="login-card">
                    <!-- Logo və Başlıq -->
                    <div class="logo-section">
                        <div class="logo-container">
                            <img src="/sis/assets/images/logo.png" alt="sis Logo" class="logo-img">
                        </div>
                        <h1 class="system-title">İnsan hüquqları şöbəsi</h1>
                        <h3 class="system-title">Admin Panel</h3>
                        <p class="system-subtitle">Təhlükəsiz Giriş Sistemi</p>
                    </div>
                    
                    <!-- Xəta Mesajı -->
                    <?php if(!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?= htmlspecialchars($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Giriş Formu -->
                    <form method="POST" id="loginForm">
                        <!-- İstifadəçi Adı -->
                        <div class="mb-4">
                            <label for="username" class="form-label">İstifadəçi Adı</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-user-tie"></i>
                                </span>
                                <input type="text" 
                                       name="username" 
                                       id="username" 
                                       class="form-control" 
                                       required 
                                       autofocus
                                       placeholder="İstifadəçi adınızı daxil edin">
                            </div>
                        </div>
                        
                        <!-- Şifrə -->
                        <div class="mb-4">
                            <label for="password" class="form-label">Şifrə</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-key"></i>
                                </span>
                                <input type="password" 
                                       name="password" 
                                       id="password" 
                                       class="form-control" 
                                       required
                                       placeholder="Şifrənizi daxil edin">
                                <button type="button" 
                                        class="password-toggle" 
                                        id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-text mt-2">
                                <i class="fas fa-info-circle me-1"></i>
                                Şifrə ən azı 6 simvoldan ibarət olmalıdır
                            </div>
                        </div>
                        
                        <!-- Giriş Düyməsi -->
                        <div class="d-grid gap-2 mb-4">
                            <button type="submit" class="btn btn-login text-white">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Sistemə Daxil Ol
                            </button>
                        </div>
                    </form>
                    
                    <!-- Təhlükəsizlik Bildirişi -->
                    <div class="security-notice">
                        <h6><i class="fas fa-shield-alt me-2"></i>Təhlükəsizlik Bildirişi</h6>
                        <p>
                            Bu sistem yalnız icazəli işçilər üçün nəzərdə tutulub. Yetkisiz giriş cəhdində məlumat qeyd ediləcək və 
                            hüquqi tədbirlər görüləcəkdir.
                        </p>
                    </div>
                    
                    <!-- Footer -->
                    <div class="footer-text">
                        <div class="mb-2">
                            <span class="state-emblem"></span>
                            <span class="copyright">Milli Proqramlaşdırma Mərkəzi</span>
                        </div>
                        <div>© <?= date('Y') ?> İnsan hüquqları şöbəsi — Bütün hüquqlar qorunur</div>
                        <div class="mt-1 small">v2.1.4 | Son yenilənmə: <?= date('d.m.Y') ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scriptlər -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Show/Hide Password funksiyası
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const passwordIcon = togglePassword.querySelector('i');
            
            togglePassword.addEventListener('click', function() {
                // Şifrə tipini dəyiş
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // İkonu dəyiş
                if (type === 'text') {
                    passwordIcon.classList.remove('fa-eye');
                    passwordIcon.classList.add('fa-eye-slash');
                    togglePassword.setAttribute('title', 'Şifrəni gizlət');
                } else {
                    passwordIcon.classList.remove('fa-eye-slash');
                    passwordIcon.classList.add('fa-eye');
                    togglePassword.setAttribute('title', 'Şifrəni göstər');
                }
            });
            
            // Form göndəriləndə əlavə təhlükəsizlik
            document.getElementById('loginForm').addEventListener('submit', function(e) {
                const username = document.getElementById('username').value.trim();
                const password = document.getElementById('password').value.trim();
                
                // Əsas validasiya
                if (username.length < 3) {
                    alert('İstifadəçi adı ən azı 3 simvol olmalıdır!');
                    e.preventDefault();
                    return;
                }
                
                if (password.length < 6) {
                    alert('Şifrə ən azı 6 simvol olmalıdır!');
                    e.preventDefault();
                    return;
                }
                
                // Düyməni deaktiv et və yükləmə vəziyyətini göstər
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Giriş edilir...';
                submitBtn.disabled = true;
            });
            
            // Form sahələrinə fokus olanda input group-un border rəngini dəyiş
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.closest('.input-group').style.borderColor = 'var(--accent-blue)';
                    this.closest('.input-group').style.boxShadow = '0 0 0 0.25rem rgba(13, 110, 253, 0.25)';
                });
                
                input.addEventListener('blur', function() {
                    this.closest('.input-group').style.borderColor = '#ced4da';
                    this.closest('.input-group').style.boxShadow = 'none';
                });
            });
        });
    </script>
</body>
</html>