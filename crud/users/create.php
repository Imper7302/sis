<?php
// crud/users/create.php

require_once '../../config/database.php';
require_once '../../includes/auth.php';

require_login();

// yalnız superadmin icazəli
if (!isSuperAdmin()) {
    header('Location: ../../dashboard.php');
    exit();
}

$title = 'Yeni İstifadəçi';

/* =========================
   FORM SUBMIT
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fullname  = trim($_POST['fullname']);
    $username  = trim($_POST['username']);
    $email     = trim($_POST['email']);
    $email     = $email === '' ? null : $email;
    $role      = $_POST['role'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Şifre alanlarını al
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    $errors = [];

    /* ---------- VALIDATION ---------- */
    if ($fullname === '') {
        $errors[] = 'Tam ad boş ola bilməz!';
    }

    if ($username === '') {
        $errors[] = 'İstifadəçi adı boş ola bilməz!';
    }

    if (!in_array($role, ['superadmin', 'isci'])) {
        $errors[] = 'Yanlış rol seçimi!';
    }

    if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email formatı yanlışdır!';
    }

    /* ---------- USERNAME UNIQUE ---------- */
    $q = "SELECT id FROM users WHERE username = ?";
    $st = $conn->prepare($q);
    $st->bind_param("s", $username);
    $st->execute();
    if ($st->get_result()->num_rows > 0) {
        $errors[] = 'Bu istifadəçi adı artıq mövcuddur!';
    }

    /* ---------- EMAIL UNIQUE ---------- */
    if ($email !== null) {
        $q = "SELECT id FROM users WHERE email = ?";
        $st = $conn->prepare($q);
        $st->bind_param("s", $email);
        $st->execute();
        if ($st->get_result()->num_rows > 0) {
            $errors[] = 'Bu email artıq istifadə olunur!';
        }
    }

    /* ---------- PASSWORD ---------- */
    if ($password === '') {
        $errors[] = 'Şifrə boş ola bilməz!';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Şifrə minimum 6 simvol olmalıdır!';
    } elseif ($password !== $confirm_password) {
        $errors[] = 'Şifrələr eyni deyil!';
    }

    /* =========================
       CREATE
    ========================= */
    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("
            INSERT INTO users
            (fullname, username, email, password, role, is_active)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssssi",
            $fullname, $username, $email, $hashed, $role, $is_active
        );

        if ($stmt->execute()) {
            $newUserId = $stmt->insert_id;
            
            // Log yazmaq istəyirsinizsə
            // logAction('user_create', "Yeni istifadəçi yaradıldı: {$username} (ID: {$newUserId})");
            
            $_SESSION['success'] = 'İstifadəçi uğurla əlavə edildi!';
            header('Location: index.php');
            exit();
        } else {
            $errors[] = 'Əməliyyat zamanı xəta baş verdi: ' . $conn->error;
        }
    }

    $_SESSION['errors'] = $errors;
    $_SESSION['form_data'] = $_POST;
}
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/navigation.php'; ?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-user-plus me-2"></i> <?= $title ?>
                </h1>
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Geri
                </a>
            </div>

            <?php if (!empty($_SESSION['errors'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i> Xəta!</h5>
                <ul class="mb-0">
                    <?php foreach ($_SESSION['errors'] as $e): ?>
                        <li><?= $e ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Bağla"></button>
            </div>
            <?php unset($_SESSION['errors']); endif; ?>

            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-plus-circle me-2"></i> Yeni İstifadəçi Formu
                    </h5>
                </div>
                
                <form method="POST" class="needs-validation" novalidate>
                    <div class="card-body">
                        
                        <!-- Əsas məlumatlar -->
                        <div class="mb-4">
                            <h6 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-info-circle me-2"></i> Əsas məlumatlar
                            </h6>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="fullname" class="form-label">Tam Ad *</label>
                                    <input type="text" id="fullname" name="fullname" 
                                           class="form-control" required
                                           value="<?= htmlspecialchars($_SESSION['form_data']['fullname'] ?? '') ?>">
                                    <div class="invalid-feedback">Tam adı daxil edin</div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="username" class="form-label">İstifadəçi adı *</label>
                                    <input type="text" id="username" name="username" 
                                           class="form-control" required
                                           value="<?= htmlspecialchars($_SESSION['form_data']['username'] ?? '') ?>">
                                    <div class="invalid-feedback">İstifadəçi adını daxil edin</div>
                                    <div class="form-text">Daxili istifadə üçün unikal ad</div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" id="email" name="email" 
                                           class="form-control"
                                           value="<?= htmlspecialchars($_SESSION['form_data']['email'] ?? '') ?>">
                                    <div class="form-text">İsteğe bağlı - bəyanatlar üçün</div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="role" class="form-label">Rol *</label>
                                    <select id="role" name="role" class="form-select" required>
                                        <option value="">Seçin</option>
                                        <option value="isci" <?= (($_SESSION['form_data']['role'] ?? '') === 'isci') ? 'selected' : '' ?>>İşçi</option>
                                        <option value="superadmin" <?= (($_SESSION['form_data']['role'] ?? '') === 'superadmin') ? 'selected' : '' ?>>Superadmin</option>
                                    </select>
                                    <div class="invalid-feedback">Rol seçin</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Şifrə -->
                        <div class="card mb-4 border-primary">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-shield-alt me-2"></i> Təhlükəsizlik
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="password" class="form-label">Şifrə *</label>
                                        <input type="password" id="password" name="password" 
                                               class="form-control" required minlength="6">
                                        <div class="invalid-feedback">Minimum 6 simvol daxil edin</div>
                                        <div class="form-text">Minimum 6 simvol</div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="confirm_password" class="form-label">Şifrəni Təkrar *</label>
                                        <input type="password" id="confirm_password" name="confirm_password" 
                                               class="form-control" required minlength="6">
                                        <div class="invalid-feedback">Şifrəni təkrar daxil edin</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Status -->
                        <div class="mb-4">
                            <h6 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-toggle-on me-2"></i> Status
                            </h6>
                            
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" 
                                       name="is_active" id="is_active" 
                                       <?= (isset($_SESSION['form_data']['is_active']) && $_SESSION['form_data']['is_active'] == 0) ? '' : 'checked' ?>
                                       role="switch">
                                <label class="form-check-label" for="is_active">
                                    İstifadəçi aktivdir
                                </label>
                                <div class="form-text">
                                    Aktiv olmayan istifadəçilər sistemə giriş edə bilməz.
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <a href="index.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i> Ləğv et
                                </a>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-plus-circle me-1"></i> İstifadəçi Yarat
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Bootstrap validation
(function () {
    'use strict'
    
    var forms = document.querySelectorAll('.needs-validation')
    
    Array.prototype.slice.call(forms)
        .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                
                form.classList.add('was-validated')
            }, false)
        })
})()
</script>

<?php 
unset($_SESSION['form_data']); 
include '../../includes/footer.php'; 
?>