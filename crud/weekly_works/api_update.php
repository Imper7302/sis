<?php
// weekly_works/api_update.php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

// JSON header qoyun
header('Content-Type: application/json');

// Girişi yoxlayın
require_login();

// POST məlumatlarını yoxlayın
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Yanlış sorğu metodu']);
    exit;
}

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

// Məlumatları təmizlə və yoxla
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$worked_days = isset($_POST['worked_days']) ? (int)$_POST['worked_days'] : 0;
$veten_muraciyet = isset($_POST['veten_muraciyet']) ? (int)$_POST['veten_muraciyet'] : 0;
$teskilat_muraciyet = isset($_POST['teskilat_muraciyet']) ? (int)$_POST['teskilat_muraciyet'] : 0;
$sorqu = isset($_POST['sorqu']) ? (int)$_POST['sorqu'] : 0;
$imtina = isset($_POST['imtina']) ? (int)$_POST['imtina'] : 0;
$arayish = isset($_POST['arayish']) ? (int)$_POST['arayish'] : 0;
$geri_qaytarilan = isset($_POST['geri_qaytarilan']) ? (int)$_POST['geri_qaytarilan'] : 0;
$tesekkur = isset($_POST['tesekkur']) ? (int)$_POST['tesekkur'] : 0;
$imtina_gulhuseyn = isset($_POST['imtina_gulhuseyn']) ? (int)$_POST['imtina_gulhuseyn'] : 0;
$imtina_aynur = isset($_POST['imtina_aynur']) ? (int)$_POST['imtina_aynur'] : 0;
$imtina_adil = isset($_POST['imtina_adil']) ? (int)$_POST['imtina_adil'] : 0;

// Qiymətləndirmə ID-sini al
$qiymetlendirme_id = isset($_POST['qiymetlendirme_id']) ? 
    ($_POST['qiymetlendirme_id'] === '' ? null : (int)$_POST['qiymetlendirme_id']) : 
    null;

// Validasiya
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Yanlış ID']);
    exit;
}

if ($worked_days < 0 || $worked_days > 7) {
    echo json_encode(['success' => false, 'message' => 'İş günləri 0-7 aralığında olmalıdır']);
    exit;
}

// Yalnız superadmin və ya öz qeydini yeniləyən işçi üçün icazə
if (!$is_superadmin) {
    // Qeydin bu işçiyə aid olub olmadığını yoxla
    $checkQuery = "SELECT ww.id FROM weekly_works ww 
                   INNER JOIN employees e ON ww.employee_id = e.id 
                   WHERE ww.id = ? AND e.user_id = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param('ii', $id, $current_user_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Yetkiniz yoxdur']);
        exit;
    }
}

// Qiymətləndirmə yalnız superadmin tərəfindən təyin edilə bilər
if (!$is_superadmin && !is_null($qiymetlendirme_id)) {
    echo json_encode(['success' => false, 'message' => 'Qiymətləndirmə yalnız superadmin tərəfindən təyin edilə bilər']);
    exit;
}

// Məlumatları bazada yenilə
try {
    $query = "UPDATE weekly_works SET 
              worked_days = ?, 
              veten_muraciyet = ?, 
              teskilat_muraciyet = ?, 
              sorqu = ?, 
              imtina = ?, 
              arayish = ?, 
              geri_qaytarilan = ?, 
              tesekkur = ?,
              imtina_gulhuseyn = ?, 
              imtina_aynur = ?, 
              imtina_adil = ?";
    
    // Superadmin üçün qiymətləndirmə ID-sini də əlavə et
    if ($is_superadmin) {
        $query .= ", qiymetlendirme_id = ?";
    }
    
    $query .= " WHERE id = ?";
    
    $stmt = $conn->prepare($query);
    
    if ($is_superadmin) {
        // Superadmin üçün 13 parametr (qiymətləndirmə daxil)
        $stmt->bind_param(
            'iiiiiiiiiiiii', 
            $worked_days, 
            $veten_muraciyet, 
            $teskilat_muraciyet, 
            $sorqu, 
            $imtina, 
            $arayish, 
            $geri_qaytarilan, 
            $tesekkur,
            $imtina_gulhuseyn, 
            $imtina_aynur, 
            $imtina_adil,
            $qiymetlendirme_id,
            $id
        );
    } else {
        // İşçi üçün 12 parametr (qiymətləndirmə olmadan)
        $stmt->bind_param(
            'iiiiiiiiiiii', 
            $worked_days, 
            $veten_muraciyet, 
            $teskilat_muraciyet, 
            $sorqu, 
            $imtina, 
            $arayish, 
            $geri_qaytarilan, 
            $tesekkur,
            $imtina_gulhuseyn, 
            $imtina_aynur, 
            $imtina_adil,
            $id
        );
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Məlumat yeniləndi']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Baza xətası: ' . $stmt->error]);
    }
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Xəta: ' . $e->getMessage()]);
}
?>