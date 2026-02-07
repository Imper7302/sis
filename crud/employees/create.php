<?php
// crud/employees/create.php
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_login();

$title = 'Yeni İşçi';

// Session məlumatlarını yoxla
$is_superadmin = false;
if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
    $is_superadmin = ($_SESSION['user']['role'] === 'superadmin');
} elseif (isset($_SESSION['role'])) {
    $is_superadmin = ($_SESSION['role'] === 'superadmin');
}

if (!$is_superadmin) {
    $_SESSION['error'] = 'Bu əməliyyat üçün yetkiniz yoxdur';
    header('Location: index.php');
    exit();
}

// Sektor və vəzifə siyahıları
$sectors = $conn->query("SELECT id, name FROM sectors ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$positions = $conn->query("SELECT id, name FROM positions ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// Boş istifadəçi hesabları (employees cədvəlində olmayanlar)
$free_users_query = "SELECT u.id, u.username, u.fullname 
                     FROM users u 
                     LEFT JOIN employees e ON u.id = e.user_id 
                     WHERE e.user_id IS NULL 
                     AND u.role = 'isci' 
                     ORDER BY u.fullname";
$free_users = $conn->query($free_users_query)->fetch_all(MYSQLI_ASSOC);

// Form göndərilibsə
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    $ad = isset($_POST['ad']) ? trim($_POST['ad']) : '';
    $soyad = isset($_POST['soyad']) ? trim($_POST['soyad']) : '';
    $sector_id = isset($_POST['sector_id']) && $_POST['sector_id'] !== '' ? (int)$_POST['sector_id'] : null;
    $position_id = isset($_POST['position_id']) && $_POST['position_id'] !== '' ? (int)$_POST['position_id'] : null;
    
    $errors = [];
    
    // Validasiya
    if (empty($ad)) {
        $errors[] = 'Ad boş ola bilməz';
    }
    
    if (empty($soyad)) {
        $errors[] = 'Soyad boş ola bilməz';
    }
    
    if ($user_id > 0) {
        // İstifadəçinin artıq işçisi olub olmadığını yoxla
        $check_user = $conn->prepare("SELECT id FROM employees WHERE user_id = ?");
        $check_user->bind_param('i', $user_id);
        $check_user->execute();
        $check_user->store_result();
        
        if ($check_user->num_rows > 0) {
            $errors[] = 'Bu istifadəçinin artıq işçi qeydi var';
        }
        $check_user->close();
    }
    
    if (empty($errors)) {
        try {
            // Yeni işçi əlavə et
            $query = "INSERT INTO employees (user_id, ad, soyad, sector_id, position_id) 
                     VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($query);
            
            // NULL dəyərləri emal et
            if ($sector_id === null && $position_id === null) {
                $stmt->bind_param('issii', $user_id, $ad, $soyad, $sector_id, $position_id);
            } elseif ($sector_id === null) {
                $stmt->bind_param('issii', $user_id, $ad, $soyad, $sector_id, $position_id);
            } elseif ($position_id === null) {
                $stmt->bind_param('issii', $user_id, $ad, $soyad, $sector_id, $position_id);
            } else {
                $stmt->bind_param('issii', $user_id, $ad, $soyad, $sector_id, $position_id);
            }
            
            if ($stmt->execute()) {
                $_SESSION['success'] = 'İşçi uğurla əlavə edildi';
                header('Location: index.php');
                exit();
            } else {
                $errors[] = 'Baza xətası: ' . $conn->error;
            }
            
            $stmt->close();
        } catch (Exception $e) {
            $errors[] = 'Xəta: ' . $e->getMessage();
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['error'] = implode('<br>', $errors);
    }
}

include '../../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-user-plus me-2"></i>Yeni İşçi Əlavə Et</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" id="employeeForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Ad *</label>
                                <input type="text" class="form-control" name="ad" 
                                       value="<?php echo isset($_POST['ad']) ? htmlspecialchars($_POST['ad']) : ''; ?>" 
                                       required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Soyad *</label>
                                <input type="text" class="form-control" name="soyad" 
                                       value="<?php echo isset($_POST['soyad']) ? htmlspecialchars($_POST['soyad']) : ''; ?>" 
                                       required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold">İstifadəçi Hesabı</label>
                                <select class="form-select" name="user_id">
                                    <option value="0">Yeni hesab yaradılsın</option>
                                    <?php foreach ($free_users as $user): ?>
                                        <option value="<?php echo $user['id']; ?>" 
                                            <?php echo isset($_POST['user_id']) && $_POST['user_id'] == $user['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($user['fullname'] ?: $user['username']); ?>
                                            (<?php echo htmlspecialchars($user['username']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Boş istifadəçi hesabları siyahıdan seçilə bilər</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Sektor</label>
                                <select class="form-select" name="sector_id">
                                    <option value="">Seçilməyib</option>
                                    <?php foreach ($sectors as $sector): ?>
                                        <option value="<?php echo $sector['id']; ?>" 
                                            <?php echo isset($_POST['sector_id']) && $_POST['sector_id'] == $sector['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($sector['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Vəzifə</label>
                                <select class="form-select" name="position_id">
                                    <option value="">Seçilməyib</option>
                                    <?php foreach ($positions as $position): ?>
                                        <option value="<?php echo $position['id']; ?>" 
                                            <?php echo isset($_POST['position_id']) && $_POST['position_id'] == $position['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($position['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-12">
                                <div class="alert-info">
                                    <strong>Qeyd:</strong> 
                                    <ul class="mb-0">
                                        <li>Ad, Soyad və İstifadəçi sahələri məcburidir</li>
                                        <li>Sektor və Vəzifə sahələri istəyə bağlıdır</li>
                                        <li>Sistmdə olan boş istifadəçi hesablarını yuxarıdakı siyahıdan seçə bilərsiniz</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="index.php" class="btn btn-secondary">Ləğv et</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Yadda Saxla
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form göndərildikdə validasiya
    document.getElementById('employeeForm').addEventListener('submit', function(e) {
        const ad = this.querySelector('input[name="ad"]').value.trim();
        const soyad = this.querySelector('input[name="soyad"]').value.trim();
        
        if (!ad) {
            e.preventDefault();
            alert('Zəhmət olmasa adı daxil edin');
            return;
        }
        
        if (!soyad) {
            e.preventDefault();
            alert('Zəhmət olmasa soyadı daxil edin');
            return;
        }
    });
});
</script>

<?php include '../../includes/footer.php'; ?>