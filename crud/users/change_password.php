<?php // crud/users/change_password.php
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_login();

$title = 'Şifrə Dəyiş';

// ID parametresini al
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Güvenlik kontrolü
if (!$user_id) {
    header('Location: index.php');
    exit();
}

// Kullanıcıyı getir
$stmt = $conn->prepare("SELECT id, username, fullname FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header('Location: index.php');
    exit();
}

// Yetki kontrolü: Superadmin değilse sadece kendi şifresini değiştirebilir
if ($_SESSION['role'] !== 'superadmin' && $user['id'] != $_SESSION['user_id']) {
    header('Location: index.php');
    exit();
}

// Şifre değiştirme işlemi
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validasyon
    if (empty($password) || empty($confirm_password)) {
        $error = 'Bütün sahələri doldurun!';
    } elseif (strlen($password) < 6) {
        $error = 'Şifrə ən az 6 simvol olmalıdır!';
    } elseif ($password !== $confirm_password) {
        $error = 'Şifrələr uyğun gəlmir!';
    } else {
        // Şifreyi hashle ve güncelle
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update_stmt->bind_param('si', $hashed_password, $user_id);
        
        if ($update_stmt->execute()) {
            $success = 'Şifrə uğurla dəyişdirildi!';
            
            // Eğer kendi şifresini değiştirdiyse session'ı yenile
            if ($user_id == $_SESSION['user_id']) {
                $_SESSION['password_changed'] = true;
            }
        } else {
            $error = 'Xəta baş verdi: ' . $conn->error;
        }
    }
}
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/navigation.php'; ?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-key"></i> Şifrə Dəyiş - <?= htmlspecialchars($user['fullname'] ?: $user['username']) ?>
                    </h4>
                </div>
                <div class="card-body">
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php endif; ?>
                    
                    <form method="post">
                        <div class="mb-3">
                            <label for="password" class="form-label">Yeni Şifrə</label>
                            <input type="password" class="form-control" id="password" 
                                   name="password" required minlength="6">
                            <div class="form-text">Ən az 6 simvol olmalıdır</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Şifrəni Təkrar Yaz</label>
                            <input type="password" class="form-control" id="confirm_password" 
                                   name="confirm_password" required minlength="6">
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Şifrəni Dəyiş
                            </button>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Geri
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>