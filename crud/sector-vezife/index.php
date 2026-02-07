<?php
// crud/sector-vezife/index.php

require_once '../../config/database.php';
require_once '../../includes/auth.php';

require_login();
// Yalnız superadmin girə bilir
if (!isSuperAdmin()) {
    // İşçi və ya digər rollar weekly_works səhifəsinə yönləndirilir
    $_SESSION['error'] = 'Bu səhifəyə giriş icazəniz yoxdur!';
    header('Location: /sis/crud/weekly_works/index.php');
    exit();
}
$title = 'Sektorlar və Vəzifələr';

// Sektörlər üçün POST emalı
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sector_action'])) {
    $action = $_POST['sector_action'];
    
    if ($action === 'create') {
        // Yeni sektor əlavə et
        $name = trim($_POST['sector_name']);
        
        if (empty($name)) {
            $_SESSION['sector_error'] = 'Sektor adı boş ola bilməz!';
        } else {
            // Unikallığı yoxla
            $checkQuery = "SELECT id FROM sectors WHERE name = ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param("s", $name);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            if ($checkResult->num_rows > 0) {
                $_SESSION['sector_error'] = 'Bu sektor artıq mövcuddur!';
            } else {
                $query = "INSERT INTO sectors (name) VALUES (?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("s", $name);
                
                if ($stmt->execute()) {
                    $_SESSION['sector_success'] = 'Sektor uğurla əlavə edildi!';
                } else {
                    $_SESSION['sector_error'] = 'Xəta baş verdi!';
                }
            }
        }
    } elseif ($action === 'update') {
        // Sektoru yenilə
        $sectorId = (int)$_POST['sector_id'];
        $name = trim($_POST['sector_name']);
        
        if (empty($name)) {
            $_SESSION['sector_error'] = 'Sektor adı boş ola bilməz!';
        } else {
            // Unikallığı yoxla (özü istisna olmaqla)
            $checkQuery = "SELECT id FROM sectors WHERE name = ? AND id != ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param("si", $name, $sectorId);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            if ($checkResult->num_rows > 0) {
                $_SESSION['sector_error'] = 'Bu sektor adı artıq mövcuddur!';
            } else {
                $query = "UPDATE sectors SET name = ? WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("si", $name, $sectorId);
                
                if ($stmt->execute()) {
                    $_SESSION['sector_success'] = 'Sektor uğurla yeniləndi!';
                } else {
                    $_SESSION['sector_error'] = 'Xəta baş verdi!';
                }
            }
        }
    } elseif ($action === 'delete') {
        // Sektoru sil
        $sectorId = (int)$_POST['sector_id'];
        
        // Əvvəlcə bu sektorla əlaqəli işçilər varmı yoxla
        $checkQuery = "SELECT COUNT(*) as count FROM employees WHERE sector_id = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("i", $sectorId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $checkData = $checkResult->fetch_assoc();
        
        if ($checkData['count'] > 0) {
            $_SESSION['sector_error'] = 'Bu sektorla əlaqəli işçilər var! Əvvəlcə onları silin və ya başqa sektora köçürün.';
        } else {
            $query = "DELETE FROM sectors WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $sectorId);
            
            if ($stmt->execute()) {
                $_SESSION['sector_success'] = 'Sektor uğurla silindi!';
            } else {
                $_SESSION['sector_error'] = 'Xəta baş verdi!';
            }
        }
    }
    
    header('Location: index.php');
    exit();
}

// Vəzifələr üçün POST emalı
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['position_action'])) {
    $action = $_POST['position_action'];
    
    if ($action === 'create') {
        // Yeni vəzifə əlavə et
        $name = trim($_POST['position_name']);
        
        if (empty($name)) {
            $_SESSION['position_error'] = 'Vəzifə adı boş ola bilməz!';
        } else {
            // Unikallığı yoxla
            $checkQuery = "SELECT id FROM positions WHERE name = ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param("s", $name);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            if ($checkResult->num_rows > 0) {
                $_SESSION['position_error'] = 'Bu vəzifə artıq mövcuddur!';
            } else {
                $query = "INSERT INTO positions (name) VALUES (?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("s", $name);
                
                if ($stmt->execute()) {
                    $_SESSION['position_success'] = 'Vəzifə uğurla əlavə edildi!';
                } else {
                    $_SESSION['position_error'] = 'Xəta baş verdi!';
                }
            }
        }
    } elseif ($action === 'update') {
        // Vəzifəni yenilə
        $positionId = (int)$_POST['position_id'];
        $name = trim($_POST['position_name']);
        
        if (empty($name)) {
            $_SESSION['position_error'] = 'Vəzifə adı boş ola bilməz!';
        } else {
            // Unikallığı yoxla (özü istisna olmaqla)
            $checkQuery = "SELECT id FROM positions WHERE name = ? AND id != ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param("si", $name, $positionId);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            if ($checkResult->num_rows > 0) {
                $_SESSION['position_error'] = 'Bu vəzifə adı artıq mövcuddur!';
            } else {
                $query = "UPDATE positions SET name = ? WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("si", $name, $positionId);
                
                if ($stmt->execute()) {
                    $_SESSION['position_success'] = 'Vəzifə uğurla yeniləndi!';
                } else {
                    $_SESSION['position_error'] = 'Xəta baş verdi!';
                }
            }
        }
    } elseif ($action === 'delete') {
        // Vəzifəni sil
        $positionId = (int)$_POST['position_id'];
        
        // Əvvəlcə bu vəzifə ilə əlaqəli işçilər varmı yoxla
        $checkQuery = "SELECT COUNT(*) as count FROM employees WHERE position_id = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("i", $positionId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $checkData = $checkResult->fetch_assoc();
        
        if ($checkData['count'] > 0) {
            $_SESSION['position_error'] = 'Bu vəzifə ilə əlaqəli işçilər var! Əvvəlcə onları silin və ya başqa vəzifəyə köçürün.';
        } else {
            $query = "DELETE FROM positions WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $positionId);
            
            if ($stmt->execute()) {
                $_SESSION['position_success'] = 'Vəzifə uğurla silindi!';
            } else {
                $_SESSION['position_error'] = 'Xəta baş verdi!';
            }
        }
    }
    
    header('Location: index.php');
    exit();
}

// Sektörləri gətir
$sectorsQuery = "SELECT * FROM sectors ORDER BY name";
$sectorsResult = $conn->query($sectorsQuery);
$sectors = [];
while ($row = $sectorsResult->fetch_assoc()) {
    $sectors[] = $row;
}

// Vəzifələri gətir
$positionsQuery = "SELECT * FROM positions ORDER BY name";
$positionsResult = $conn->query($positionsQuery);
$positions = [];
while ($row = $positionsResult->fetch_assoc()) {
    $positions[] = $row;
}

// Redaktə üçün GET parametrləri
$editSectorId = isset($_GET['edit_sector']) ? (int)$_GET['edit_sector'] : 0;
$editPositionId = isset($_GET['edit_position']) ? (int)$_GET['edit_position'] : 0;
$editSector = null;
$editPosition = null;

if ($editSectorId > 0) {
    $query = "SELECT * FROM sectors WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $editSectorId);
    $stmt->execute();
    $result = $stmt->get_result();
    $editSector = $result->fetch_assoc();
}

if ($editPositionId > 0) {
    $query = "SELECT * FROM positions WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $editPositionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $editPosition = $result->fetch_assoc();
}
?>

<?php include '../../includes/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-sitemap"></i> <?php echo $title; ?></h1>
</div>

<div class="row">
    <!-- Sektorlar Bölməsi -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-building"></i> Sektorlar</h5>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['sector_success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['sector_success']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['sector_success']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['sector_error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['sector_error']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['sector_error']); ?>
                <?php endif; ?>
                
                <!-- Sektor Formu -->
                <form method="POST" action="" class="mb-4">
                    <div class="mb-3">
                        <label for="sector_name" class="form-label">
                            <?php echo $editSector ? 'Sektoru Redaktə Et' : 'Yeni Sektor Əlavə Et'; ?>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="sector_name" 
                               name="sector_name" 
                               required
                               value="<?php echo $editSector ? htmlspecialchars($editSector['name']) : ''; ?>"
                               placeholder="Sektor adı daxil edin">
                    </div>
                    
                    <?php if ($editSector): ?>
                        <input type="hidden" name="sector_id" value="<?php echo $editSector['id']; ?>">
                        <input type="hidden" name="sector_action" value="update">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save"></i> Yenilə
                            </button>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Ləğv et
                            </a>
                        </div>
                    <?php else: ?>
                        <input type="hidden" name="sector_action" value="create">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Əlavə Et
                        </button>
                    <?php endif; ?>
                </form>
                
                <!-- Sektorlar Siyahısı -->
                <h6>Sektorların Siyahısı</h6>
                <?php if (empty($sectors)): ?>
                    <div class="alert alert-info">
                        Heç bir sektor yoxdur.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Ad</th>
                                    <th>Qeydiyyat Tarixi</th>
                                    <th>Əməliyyatlar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sectors as $sector): ?>
                                    <tr>
                                        <td><?php echo $sector['id']; ?></td>
                                        <td><?php echo htmlspecialchars($sector['name']); ?></td>
                                        <td><?php echo date('d.m.Y', strtotime($sector['created_at'])); ?></td>
                                        <td>
                                            <a href="?edit_sector=<?php echo $sector['id']; ?>" 
                                               class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="" class="d-inline" 
                                                  onsubmit="return confirm('Bu sektoru silmək istədiyinizə əminsiniz?');">
                                                <input type="hidden" name="sector_id" value="<?php echo $sector['id']; ?>">
                                                <input type="hidden" name="sector_action" value="delete">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Vəzifələr Bölməsi -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-user-tie"></i> Vəzifələr</h5>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['position_success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['position_success']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['position_success']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['position_error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['position_error']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['position_error']); ?>
                <?php endif; ?>
                
                <!-- Vəzifə Formu -->
                <form method="POST" action="" class="mb-4">
                    <div class="mb-3">
                        <label for="position_name" class="form-label">
                            <?php echo $editPosition ? 'Vəzifəni Redaktə Et' : 'Yeni Vəzifə Əlavə Et'; ?>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="position_name" 
                               name="position_name" 
                               required
                               value="<?php echo $editPosition ? htmlspecialchars($editPosition['name']) : ''; ?>"
                               placeholder="Vəzifə adı daxil edin">
                    </div>
                    
                    <?php if ($editPosition): ?>
                        <input type="hidden" name="position_id" value="<?php echo $editPosition['id']; ?>">
                        <input type="hidden" name="position_action" value="update">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save"></i> Yenilə
                            </button>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Ləğv et
                            </a>
                        </div>
                    <?php else: ?>
                        <input type="hidden" name="position_action" value="create">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Əlavə Et
                        </button>
                    <?php endif; ?>
                </form>
                
                <!-- Vəzifələr Siyahısı -->
                <h6>Vəzifələrin Siyahısı</h6>
                <?php if (empty($positions)): ?>
                    <div class="alert alert-info">
                        Heç bir vəzifə yoxdur.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Ad</th>
                                    <th>Qeydiyyat Tarixi</th>
                                    <th>Əməliyyatlar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($positions as $position): ?>
                                    <tr>
                                        <td><?php echo $position['id']; ?></td>
                                        <td><?php echo htmlspecialchars($position['name']); ?></td>
                                        <td><?php echo date('d.m.Y', strtotime($position['created_at'])); ?></td>
                                        <td>
                                            <a href="?edit_position=<?php echo $position['id']; ?>" 
                                               class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="" class="d-inline" 
                                                  onsubmit="return confirm('Bu vəzifəni silmək istədiyinizə əminsiniz?');">
                                                <input type="hidden" name="position_id" value="<?php echo $position['id']; ?>">
                                                <input type="hidden" name="position_action" value="delete">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>