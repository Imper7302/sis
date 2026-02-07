<?php // crud/users/index.php
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_login();

$title = 'İstifadəçilər';

// Eğer kullanıcı superadmin değilse, sadece kendi bilgilerini görebilir
$is_superadmin = ($_SESSION['role'] === 'superadmin');

/* ====================== FILTER PARAMS ====================== */
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$role   = isset($_GET['role']) ? trim($_GET['role']) : '';

/* ====================== WHERE CLAUSE ====================== */
$where  = [];
$params = [];
$types  = '';

// Superadmin değilse sadece kendi kaydını görebilir
if (!$is_superadmin) {
    $where[] = 'id = ?';
    $params[] = $_SESSION['user_id'];
    $types .= 'i';
} else {
    // Superadmin için filtreler
    if ($search !== '') {
        $where[] = '(username LIKE ? OR fullname LIKE ?)';
        $params[] = "%$search%";
        $params[] = "%$search%";
        $types .= 'ss';
    }
    
    if ($role !== '') {
        $where[] = 'role = ?';
        $params[] = $role;
        $types .= 's';
    }
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

/* ====================== USERS QUERY ====================== */
$sql = " 
    SELECT id, username, is_active, fullname, role, created_at, last_login 
    FROM users 
    $whereSql 
    ORDER BY id DESC 
";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$users  = $result->fetch_all(MYSQLI_ASSOC);

/* ====================== STATISTICS ====================== */
// Superadmin ise tüm istatistikleri göster, değilse sadece kendi bilgilerini
if ($is_superadmin) {
    $total_users       = $conn->query("SELECT COUNT(*) c FROM users")->fetch_assoc()['c'];
    $total_superadmin  = $conn->query("SELECT COUNT(*) c FROM users WHERE role='superadmin'")->fetch_assoc()['c'];
    $total_isci        = $conn->query("SELECT COUNT(*) c FROM users WHERE role='isci'")->fetch_assoc()['c'];
} else {
    $total_users = 1;
    $total_superadmin = ($_SESSION['role'] === 'superadmin') ? 1 : 0;
    $total_isci = ($_SESSION['role'] === 'isci') ? 1 : 0;
}
?>

<?php include '../../includes/header.php'; ?>


<div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
    <h1 class="h3"><i class="fas fa-users"></i> İstifadəçilər</h1>
    
    <?php if ($is_superadmin): ?>
        <a href="create.php" class="btn btn-success">
            <i class="fas fa-plus"></i> Yeni User
        </a>
    <?php else: ?>
        <a href="change_password.php?id=<?= $_SESSION['user_id'] ?>" class="btn btn-warning">
            <i class="fas fa-key"></i> Şifrəni Dəyiş
        </a>
    <?php endif; ?>
</div>

<!-- STAT CARDS - Sadece superadmin için -->
<?php if ($is_superadmin): ?>
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6>Ümumi</h6>
                <h2><?= $total_users ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h6>Superadmin</h6>
                <h2><?= $total_superadmin ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6>İşçi</h6>
                <h2><?= $total_isci ?></h2>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- FILTER - Sadece superadmin için -->
<?php if ($is_superadmin): ?>
<form class="card mb-3" method="get">
    <div class="card-body row g-3">
        <div class="col-md-6">
            <input type="text" name="search" class="form-control" 
                   placeholder="Ad və ya username axtar" 
                   value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-4">
            <select name="role" class="form-select">
                <option value="">Bütün rollar</option>
                <option value="superadmin" <?= $role==='superadmin'?'selected':'' ?>>Superadmin</option>
                <option value="isci" <?= $role==='isci'?'selected':'' ?>>İşçi</option>
            </select>
        </div>
        <div class="col-md-2 d-grid">
            <button class="btn btn-primary">
                <i class="fas fa-filter"></i> Filtrlə
            </button>
        </div>
    </div>
</form>
<?php endif; ?>

<!-- TABLE -->
<div class="card">
    <div class="card-body table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ad Soyad</th>
                    <th>Username</th>
                    <th>Status</th>
                    <th>Rol</th>
                    <th>Son giriş</th>
                    <th width="120">Əməliyyat</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($users): 
                    foreach ($users as $u): 
                        // Kullanıcının kendi kaydı mı kontrol et
                        $is_own_record = ($u['id'] == $_SESSION['user_id']);
                ?>
                    <tr>
                        <td>#<?= $u['id'] ?></td>
                        <td><?= htmlspecialchars($u['fullname'] ?: '—') ?></td>
                        <td><?= htmlspecialchars($u['username']) ?></td>
                        
                        <!-- AKTİV / DEAKTİV -->
                        <td>
                            <?php if ($u['is_active'] == 1): ?>
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle"></i> Aktiv
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary">
                                    <i class="fas fa-ban"></i> Deaktiv
                                </span>
                            <?php endif; ?>
                        </td>
                        
                        <td>
                            <span class="badge bg-<?= $u['role']==='superadmin'?'danger':'info' ?>">
                                <?= $u['role'] ?>
                            </span>
                        </td>
                        
                        <td>
                            <?= $u['last_login'] ? date('d.m.Y H:i', strtotime($u['last_login'])) : '—' ?>
                        </td>
                        
                        <td>
                            <?php if ($is_superadmin): ?>
                                <!-- Superadmin tüm kullanıcıları düzenleyebilir -->
                                <a href="update.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                    <a href="delete.php?id=<?= $u['id'] ?>" 
                                       onclick="return confirm('Silinsin?')" 
                                       class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                <?php endif; ?>
                                
                            <?php elseif ($is_own_record): ?>
                                <!-- İşçi sadece kendi şifresini değiştirebilir -->
                                <a href="change_password.php?id=<?= $u['id'] ?>" 
                                   class="btn btn-sm btn-warning" 
                                   title="Şifrəni dəyiş">
                                    <i class="fas fa-key"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; 
                else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">
                            Məlumat tapılmadı
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>