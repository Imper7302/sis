<?php
// crud/users/update.php

require_once '../../config/database.php';
require_once '../../includes/auth.php';

require_login();

// yalnız superadmin icazəli
if (!isSuperAdmin()) {
    header('Location: ../../dashboard.php');
    exit();
}

$title = 'İstifadəçini Redaktə Et';
$userId = 0;
$user = null;

/* =========================
   USER LOAD
========================= */
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $userId = (int) $_GET['id'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user) {
        $_SESSION['error'] = 'İstifadəçi tapılmadı!';
        header('Location: index.php');
        exit();
    }
} else {
    $_SESSION['error'] = 'İstifadəçi ID-si təyin edilməyib!';
    header('Location: index.php');
    exit();
}

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
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

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
    $q = "SELECT id FROM users WHERE username = ? AND id != ?";
    $st = $conn->prepare($q);
    $st->bind_param("si", $username, $userId);
    $st->execute();
    if ($st->get_result()->num_rows > 0) {
        $errors[] = 'Bu istifadəçi adı artıq mövcuddur!';
    }

    /* ---------- EMAIL UNIQUE ---------- */
    if ($email !== null) {
        $q = "SELECT id FROM users WHERE email = ? AND id != ?";
        $st = $conn->prepare($q);
        $st->bind_param("si", $email, $userId);
        $st->execute();
        if ($st->get_result()->num_rows > 0) {
            $errors[] = 'Bu email artıq istifadə olunur!';
        }
    }

    /* ---------- PASSWORD ---------- */
    // Şifre değiştirmek isteniyorsa kontrol et
    if (!empty($password)) {
        if (strlen($password) < 6) {
            $errors[] = 'Şifrə minimum 6 simvol olmalıdır!';
        } elseif ($password !== $confirm_password) {
            $errors[] = 'Şifrələr eyni deyil!';
        }
    }

    /* =========================
       UPDATE
    ========================= */
    if (empty($errors)) {
        // Şifre değiştirilmek isteniyorsa
        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("
                UPDATE users SET
                    fullname = ?,
                    username = ?,
                    email = ?,
                    password = ?,
                    role = ?,
                    is_active = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->bind_param("sssssii",
                $fullname, $username, $email, $hashed, $role, $is_active, $userId
            );
        } else {
            // Şifre değiştirilmiyorsa
            $stmt = $conn->prepare("
                UPDATE users SET
                    fullname = ?,
                    username = ?,
                    email = ?,
                    role = ?,
                    is_active = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->bind_param("ssssii",
                $fullname, $username, $email, $role, $is_active, $userId
            );
        }

        if ($stmt->execute()) {
            $_SESSION['success'] = 'İstifadəçi uğurla yeniləndi!';
            header('Location: index.php');
            exit();
        } else {
            $errors[] = 'Əməliyyat zamanı xəta baş verdi: ' . $conn->error;
        }
    }

    $_SESSION['errors'] = $errors;
}
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/navigation.php'; ?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-user-edit me-2"></i> <?= $title ?>
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

            <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?= $_SESSION['error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Bağla"></button>
            </div>
            <?php unset($_SESSION['error']); endif; ?>

            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-circle me-2"></i> 
                        <?= htmlspecialchars($user['fullname'] ?: $user['username']) ?>
                    </h5>
                </div>
                
                <form method="POST" class="needs-validation" novalidate>
                    <div class="card-body">
                        
                        <!-- İstifadəçi məlumatları -->
                        <div class="mb-4">
                            <h6 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-info-circle me-2"></i> Əsas məlumatlar
                            </h6>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="fullname" class="form-label">Tam Ad *</label>
                                    <input type="text" id="fullname" name="fullname" 
                                           class="form-control" required
                                           value="<?= htmlspecialchars($user['fullname'] ?? '') ?>">
                                    <div class="invalid-feedback">Tam adı daxil edin</div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="username" class="form-label">İstifadəçi adı *</label>
                                    <input type="text" id="username" name="username" 
                                           class="form-control" required
                                           value="<?= htmlspecialchars($user['username'] ?? '') ?>">
                                    <div class="invalid-feedback">İstifadəçi adını daxil edin</div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" id="email" name="email" 
                                           class="form-control"
                                           value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                                    <div class="form-text">İsteğe bağlı</div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="role" class="form-label">Rol *</label>
                                    <select id="role" name="role" class="form-select" required>
                                        <option value="isci" <?= ($user['role'] === 'isci') ? 'selected' : '' ?>>İşçi</option>
                                        <option value="superadmin" <?= ($user['role'] === 'superadmin') ? 'selected' : '' ?>>Superadmin</option>
                                    </select>
                                    <div class="invalid-feedback">Rol seçin</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Şifrə dəyişikliyi -->
                        <div class="card mb-4 border-warning">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0">
                                    <i class="fas fa-key me-2"></i> Şifrəni Dəyiş (İsteğe bağlı)
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="password" class="form-label">Yeni Şifrə</label>
                                        <input type="password" id="password" name="password" 
                                               class="form-control" minlength="6">
                                        <div class="form-text">
                                            Minimum 6 simvol. Boş buraxılsa, köhnə şifrə qalır.
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="confirm_password" class="form-label">Şifrəni Təkrar</label>
                                        <input type="password" id="confirm_password" name="confirm_password" 
                                               class="form-control" minlength="6">
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
                                       <?= ($user['is_active'] == 1) ? 'checked' : '' ?>
                                       role="switch">
                                <label class="form-check-label" for="is_active">
                                    İstifadəçi aktivdir
                                </label>
                                <div class="form-text">
                                    Aktiv olmayan istifadəçilər sistemə giriş edə bilməz.
                                </div>
                            </div>
                        </div>
                        
                        <!-- Meta məlumatları -->
                        <div class="card border-info mb-4">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-history me-2"></i> Sistem Məlumatları
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row small">
                                    <div class="col-md-4 mb-2">
                                        <strong>Yaranma tarixi:</strong><br>
                                        <?= date('d.m.Y H:i', strtotime($user['created_at'])) ?>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <strong>Son giriş:</strong><br>
                                        <?= $user['last_login'] ? date('d.m.Y H:i', strtotime($user['last_login'])) : 'Heç vaxt' ?>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <strong>ID:</strong><br>
                                        #<?= $user['id'] ?>
                                    </div>
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
                                <a href="change_password.php?id=<?= $user['id'] ?>" 
                                   class="btn btn-outline-warning ms-2">
                                    <i class="fas fa-key me-1"></i> Şifrə Dəyiş
                                </a>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Dəyişiklikləri Saxla
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

<?php include '../../includes/footer.php'; ?>