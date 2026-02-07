<?php
// crud/employees/index.php

require_once '../../config/database.php';
require_once '../../includes/auth.php';

require_login();
// Yalnız superadmin  girə bilir
if (!isSuperAdmin()) {
    // İşçi və ya digər rollar weekly_works səhifəsinə yönləndirilir
    $_SESSION['error'] = 'Bu səhifəyə giriş icazəniz yoxdur!';
    header('Location: /sis/crud/weekly_works/index.php');
    exit();
}


$title = 'İşçilər';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Search və filter parametrləri
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sector_id = isset($_GET['sector_id']) ? (int)$_GET['sector_id'] : 0;
$position_id = isset($_GET['position_id']) ? (int)$_GET['position_id'] : 0;

// Sector və position məlumatlarını al
$sectors = [];
$positions = [];

$sectorQuery = "SELECT * FROM sectors ORDER BY name";
$sectorResult = $conn->query($sectorQuery);
while ($row = $sectorResult->fetch_assoc()) {
    $sectors[] = $row;
}

$positionQuery = "SELECT * FROM positions ORDER BY name";
$positionResult = $conn->query($positionQuery);
while ($row = $positionResult->fetch_assoc()) {
    $positions[] = $row;
}

// Employees məlumatlarını gətir
$query = "SELECT e.*, s.name as sector_name, p.name as position_name, 
          u.username, u.fullname as user_fullname
          FROM employees e
          LEFT JOIN sectors s ON e.sector_id = s.id
          LEFT JOIN positions p ON e.position_id = p.id
          LEFT JOIN users u ON e.user_id = u.id
          WHERE 1=1";

$countQuery = "SELECT COUNT(*) as total 
               FROM employees e
               WHERE 1=1";

// Parametrləri dinamik şəkildə toplamaq üçün
$queryParams = [];
$countParams = [];
$types = "";
$countTypes = "";

if (!empty($search)) {
    $query .= " AND (e.ad LIKE ? OR e.soyad LIKE ? OR u.username LIKE ?)";
    $countQuery .= " AND (e.ad LIKE ? OR e.soyad LIKE ? OR u.username LIKE ?)";
    $searchTerm = "%$search%";
    $queryParams[] = $searchTerm;
    $queryParams[] = $searchTerm;
    $queryParams[] = $searchTerm;
    $countParams[] = $searchTerm;
    $countParams[] = $searchTerm;
    $countParams[] = $searchTerm;
    $types .= "sss";
    $countTypes .= "sss";
}

if ($sector_id > 0) {
    $query .= " AND e.sector_id = ?";
    $countQuery .= " AND e.sector_id = ?";
    $queryParams[] = $sector_id;
    $countParams[] = $sector_id;
    $types .= "i";
    $countTypes .= "i";
}

if ($position_id > 0) {
    $query .= " AND e.position_id = ?";
    $countQuery .= " AND e.position_id = ?";
    $queryParams[] = $position_id;
    $countParams[] = $position_id;
    $types .= "i";
    $countTypes .= "i";
}

$query .= " ORDER BY e.created_at DESC LIMIT ? OFFSET ?";
$queryParams[] = $limit;
$queryParams[] = $offset;
$types .= "ii";

// Ümumi sayı hesabla
$stmtCount = $conn->prepare($countQuery);
if (!empty($countParams)) {
    $stmtCount->bind_param($countTypes, ...$countParams);
}
$stmtCount->execute();
$countResult = $stmtCount->get_result();
$totalRows = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

// Məlumatları gətir
$stmt = $conn->prepare($query);
if (!empty($queryParams)) {
    $stmt->bind_param($types, ...$queryParams);
}
$stmt->execute();
$result = $stmt->get_result();
$employees = [];

while ($row = $result->fetch_assoc()) {
    $employees[] = $row;
}
?>

<?php include '../../includes/header.php'; ?>


<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-users"></i> <?php echo $title; ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="create.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Yeni İşçi
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-3">
                <input type="text" class="form-control" name="search" placeholder="Ad, Soyad, İstifadəçi adı..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-3">
                <select class="form-select" name="sector_id">
                    <option value="0">Bütün sektorlar</option>
                    <?php foreach ($sectors as $sector): ?>
                        <option value="<?php echo $sector['id']; ?>" 
                            <?php echo $sector_id == $sector['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($sector['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" name="position_id">
                    <option value="0">Bütün vəzifələr</option>
                    <?php foreach ($positions as $position): ?>
                        <option value="<?php echo $position['id']; ?>" 
                            <?php echo $position_id == $position['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($position['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i> Axtar
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['success']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['error']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ad</th>
                        <th>Soyad</th>
                        <th>Sektor</th>
                        <th>Vəzifə</th>
                        <th>İstifadəçi</th>
                        <th>Qeydiyyat Tarixi</th>
                        <th>Əməliyyatlar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($employees)): ?>
                        <tr>
                            <td colspan="8" class="text-center">Məlumat tapılmadı</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($employees as $employee): ?>
                            <tr>
                                <td><?php echo $employee['id']; ?></td>
                                <td><?php echo htmlspecialchars($employee['ad']); ?></td>
                                <td><?php echo htmlspecialchars($employee['soyad']); ?></td>
                                <td><?php echo $employee['sector_name'] ? htmlspecialchars($employee['sector_name']) : '-'; ?></td>
                                <td><?php echo $employee['position_name'] ? htmlspecialchars($employee['position_name']) : '-'; ?></td>
                                <td>
                                    <?php if ($employee['user_id']): ?>
                                        <span class="badge bg-info"><?php echo htmlspecialchars($employee['username']); ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">İstifadəçi yoxdur</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d.m.Y H:i', strtotime($employee['created_at'])); ?></td>
                                <td>
                                    <a href="create.php?edit=<?php echo $employee['id']; ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete.php?id=<?php echo $employee['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Bu işçini silmək istədiyinizə əminsiniz?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Səhifələmə">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&sector_id=<?php echo $sector_id; ?>&position_id=<?php echo $position_id; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>