<?php
// absent_employees/index.php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

// Login yoxlaması
require_login();

// Yalnız superadmin və ya admin girişi
if (!isSuperAdmin()) {
    $_SESSION['error'] = 'Bu səhifəyə giriş icazəniz yoxdur!';
    header('Location: /sis/crud/weekly_works/index.php');
    exit();
}

$title = 'İşdə Olmayanlar';

// Session məlumatlarını təhlükəsiz şəkildə yoxla
$is_superadmin = false;
$current_user_id = 0;
$current_user_role = '';

if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
    $is_superadmin = ($_SESSION['user']['role'] === 'superadmin');
    $current_user_id = $_SESSION['user']['id'];
    $current_user_role = $_SESSION['user']['role'];
} elseif (isset($_SESSION['role'])) {
    $is_superadmin = ($_SESSION['role'] === 'superadmin');
    $current_user_id = $_SESSION['user_id'] ?? 0;
    $current_user_role = $_SESSION['role'] ?? '';
}

// Filter parametrləri
$employee_id = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : 0;
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01'); // Ayın 1-i
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Sorgu şərtləri
$conditions = ["ww.status != 'is_rejim'"];
$params = [];
$types = '';

if ($employee_id > 0) {
    $conditions[] = "ww.employee_id = ?";
    $params[] = $employee_id;
    $types .= 'i';
}

if (!empty($status_filter)) {
    $conditions[] = "ww.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if (!empty($start_date)) {
    $conditions[] = "ww.start_date >= ?";
    $params[] = $start_date;
    $types .= 's';
}

if (!empty($end_date)) {
    $conditions[] = "ww.end_date <= ?";
    $params[] = $end_date;
    $types .= 's';
}

$whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Statistika
$statsQuery = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'mezuniyyet' THEN 1 ELSE 0 END) as mezuniyyet,
    SUM(CASE WHEN status = 'xestelik' THEN 1 ELSE 0 END) as xestelik,
    SUM(CASE WHEN status = 'ezamiyyet' THEN 1 ELSE 0 END) as ezamiyyet,
    SUM(worked_days) as total_days
    FROM weekly_works ww 
    INNER JOIN employees e ON ww.employee_id = e.id 
    $whereClause";

if (!empty($params)) {
    $stmtStats = $conn->prepare($statsQuery);
    $stmtStats->bind_param($types, ...$params);
    $stmtStats->execute();
    $statsData = $stmtStats->get_result()->fetch_assoc();
} else {
    $statsData = $conn->query($statsQuery)->fetch_assoc();
}

// Məlumatları gətir
$query = "SELECT ww.*, 
          e.ad as employee_ad, 
          e.soyad as employee_soyad, 
          CONCAT(e.ad, ' ', e.soyad) as fullname,
          e.user_id as employee_user_id
          FROM weekly_works ww 
          INNER JOIN employees e ON ww.employee_id = e.id
          $whereClause 
          ORDER BY ww.start_date DESC, ww.end_date DESC";

if (!empty($params)) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $absent_records = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $absent_records = $conn->query($query)->fetch_all(MYSQLI_ASSOC);
}

// İşçi listi
$employees = $conn->query("SELECT id, ad, soyad FROM employees ORDER BY ad, soyad")->fetch_all(MYSQLI_ASSOC);

// Status listi
$status_types = [
    'mezuniyyet' => ['label' => 'Məzuniyyət', 'color' => 'info', 'icon' => 'fa-umbrella-beach'],
    'xestelik' => ['label' => 'Xəstəlik', 'color' => 'danger', 'icon' => 'fa-heartbeat'],
    'ezamiyyet' => ['label' => 'Ezamiyyət', 'color' => 'warning', 'icon' => 'fa-plane']
];

include '../../includes/header.php';
?>

<style>
.absent-card {
    border-left: 4px solid;
    transition: all 0.3s ease;
    border-radius: 8px;
}

.absent-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.absent-card.mezuniyyet { border-left-color: #0dcaf0; }
.absent-card.xestelik { border-left-color: #dc3545; }
.absent-card.ezamiyyet { border-left-color: #ffc107; }

.status-badge {
    padding: 0.25rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.avatar-sm {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(45deg, #6c757d, #adb5bd);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 0.9rem;
}

.day-badge {
    min-width: 70px;
    padding: 0.4rem 0.8rem;
    font-weight: 600;
}

@media (max-width: 768px) {
    .day-badge {
        min-width: 60px;
        padding: 0.3rem 0.6rem;
        font-size: 0.9rem;
    }
}
</style>

<div class="container-fluid py-4">
    <!-- Başlıq -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1 text-dark">
                <i class="fas fa-user-clock text-warning me-2"></i>İşdə Olmayanlar
            </h2>
            <p class="text-muted mb-0">Məzuniyyət, xəstəlik və ezamiyyət siyahısı</p>
        </div>
        <a href="../weekly_works/index.php" class="btn btn-outline-dark">
            <i class="fas fa-arrow-left me-2"></i>Həftəlik İşlərə Qayıt
        </a>
    </div>

    <!-- Statistika Kartları -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-warning bg-opacity-10 text-warning me-3">
                            <i class="fas fa-user-clock"></i>
                        </div>
                        <div>
                            <h6 class="text-muted small text-uppercase fw-bold mb-0">Ümumi Qeyd</h6>
                            <h3 class="fw-bold mb-0"><?php echo $statsData['total'] ?? 0; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-info bg-opacity-10 text-info me-3">
                            <i class="fas fa-umbrella-beach"></i>
                        </div>
                        <div>
                            <h6 class="text-muted small text-uppercase fw-bold mb-0">Məzuniyyət</h6>
                            <h3 class="fw-bold mb-0"><?php echo $statsData['mezuniyyet'] ?? 0; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-danger bg-opacity-10 text-danger me-3">
                            <i class="fas fa-heartbeat"></i>
                        </div>
                        <div>
                            <h6 class="text-muted small text-uppercase fw-bold mb-0">Xəstəlik</h6>
                            <h3 class="fw-bold mb-0"><?php echo $statsData['xestelik'] ?? 0; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-warning bg-opacity-10 text-warning me-3">
                            <i class="fas fa-plane"></i>
                        </div>
                        <div>
                            <h6 class="text-muted small text-uppercase fw-bold mb-0">Ezamiyyət</h6>
                            <h3 class="fw-bold mb-0"><?php echo $statsData['ezamiyyet'] ?? 0; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold small">İşçi</label>
                    <select class="form-select" name="employee_id">
                        <option value="0">Hamısı</option>
                        <?php foreach ($employees as $emp): ?>
                            <option value="<?php echo $emp['id']; ?>" 
                                <?php echo $employee_id == $emp['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($emp['ad'] . ' ' . $emp['soyad']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label fw-bold small">Səbəb</label>
                    <select class="form-select" name="status">
                        <option value="">Hamısı</option>
                        <?php foreach ($status_types as $key => $status): ?>
                            <option value="<?php echo $key; ?>" 
                                <?php echo $status_filter == $key ? 'selected' : ''; ?>>
                                <?php echo $status['label']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label fw-bold small">Başlanğıc</label>
                    <input type="date" class="form-control" name="start_date" value="<?php echo $start_date; ?>">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label fw-bold small">Bitmə</label>
                    <input type="date" class="form-control" name="end_date" value="<?php echo $end_date; ?>">
                </div>
                
                <div class="col-md-2">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i>Filter
                        </button>
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="fas fa-sync"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Qeyd Siyahısı -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold text-white">
                <i class="fas fa-list me-2"></i>İşdə Olmayanlar Siyahısı
            </h5>
        </div>
        <div class="card-body p-0">
            <?php if (count($absent_records) > 0): ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($absent_records as $record): 
                        $status_info = $status_types[$record['status']] ?? ['label' => 'Naməlum', 'color' => 'secondary', 'icon' => 'fa-question'];
                        $start_formatted = date('d.m.Y', strtotime($record['start_date']));
                        $end_formatted = date('d.m.Y', strtotime($record['end_date']));
                        $total_days = (strtotime($record['end_date']) - strtotime($record['start_date'])) / (60 * 60 * 24) + 1;
                    ?>
                    <div class="list-group-item border-0 p-4 absent-card <?php echo $record['status']; ?>">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-3">
                                        <?php 
                                        $initials = mb_substr($record['employee_ad'], 0, 1, 'UTF-8') . 
                                                   mb_substr($record['employee_soyad'], 0, 1, 'UTF-8');
                                        echo strtoupper($initials);
                                        ?>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($record['fullname']); ?></h6>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar-alt me-1"></i>
                                            <?php echo $start_formatted; ?> - <?php echo $end_formatted; ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <span class="badge bg-<?php echo $status_info['color']; ?> status-badge">
                                    <i class="fas <?php echo $status_info['icon']; ?> me-1"></i>
                                    <?php echo $status_info['label']; ?>
                                </span>
                            </div>
                            
                            
                            <div class="col-md-2 text-center">
                                <span class="badge bg-light text-dark day-badge">
                                    <i class="fas fa-calendar-day me-1"></i>
                                    <?php echo $total_days; ?> gün
                                </span>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="text-muted small">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <?php if ($record['status'] == 'mezuniyyet'): ?>
                                        Məzuniyyət
                                    <?php elseif ($record['status'] == 'xestelik'): ?>
                                        Xəstəlik
                                    <?php elseif ($record['status'] == 'ezamiyyet'): ?>
                                        İş ezamiyyəti
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-1 text-end">
                                <?php if ($is_superadmin): ?>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary" type="button" 
                                            data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="#" 
                                               onclick="editAbsentRecord(<?php echo $record['id']; ?>)">
                                                <i class="fas fa-edit me-2"></i>Redaktə et
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="#" 
                                               onclick="deleteAbsentRecord(<?php echo $record['id']; ?>)">
                                                <i class="fas fa-trash me-2"></i>Sil
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-calendar-check text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-2">Qeyd Tapılmadı</h4>
                    <p class="text-muted mb-4">Seçilmiş filterə uyğun işdə olmayan qeydi yoxdur.</p>
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-sync me-2"></i>Filteri Sıfırla
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Redaktə et funksiyası
function editAbsentRecord(id) {
    window.location.href = '../weekly_works/edit.php?id=' + id;
}

// Sil funksiyası
function deleteAbsentRecord(id) {
    if(!confirm('Bu qeydi silmək istədiyinizə əminsiniz?')) return;
    
    const fd = new FormData();
    fd.append('id', id);
    
    fetch('../weekly_works/api_delete.php', {
        method: 'POST',
        body: fd
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            location.reload();
        } else {
            alert('Xəta: ' + data.message);
        }
    })
    .catch(err => alert('Sistem xətası baş verdi.'));
}

// Tarix filterində default dəyərlər
document.addEventListener('DOMContentLoaded', function() {
    // Bu ayın ilk günü və son günü
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    
    // Format: YYYY-MM-DD
    const formatDate = (date) => {
        return date.toISOString().split('T')[0];
    };
    
    // Tarix inputlarını doldur (əgər boşdursa)
    const startInput = document.querySelector('input[name="start_date"]');
    const endInput = document.querySelector('input[name="end_date"]');
    
    if(startInput && !startInput.value) {
        startInput.value = formatDate(firstDay);
    }
    
    if(endInput && !endInput.value) {
        endInput.value = formatDate(lastDay);
    }
});
</script>

<?php include '../../includes/footer.php'; ?>