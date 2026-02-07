<?php
// weekly_works/api_create.php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

// Login yoxlaması
require_login();

// JSON cavabı üçün header
header('Content-Type: application/json');

// AJAX sorğusu olub-olmadığını yoxla
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Yalnız POST sorğuları qəbul edilir']);
    exit();
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
    // Köhnə versiya ilə uyğunluq
    $is_superadmin = ($_SESSION['role'] === 'superadmin');
    $current_user_id = $_SESSION['user_id'] ?? 0;
    $current_user_role = $_SESSION['role'] ?? '';
}

// POST məlumatlarını al
$employee_id = isset($_POST['employee_id']) ? (int)$_POST['employee_id'] : 0;
$start_date = isset($_POST['start_date']) ? trim($_POST['start_date']) : '';
$end_date = isset($_POST['end_date']) ? trim($_POST['end_date']) : '';

// Ədədi sahələri emal et ("-" və ya boş dəyərləri 0-a çevir)
function parseNumericField($value) {
    if ($value === '-' || $value === '' || $value === null) {
        return 0;
    }
    return (int)$value;
}

$worked_days = isset($_POST['worked_days']) ? parseNumericField($_POST['worked_days']) : 0;
$veten_muraciyet = isset($_POST['veten_muraciyet']) ? parseNumericField($_POST['veten_muraciyet']) : 0;
$teskilat_muraciyet = isset($_POST['teskilat_muraciyet']) ? parseNumericField($_POST['teskilat_muraciyet']) : 0;
$sorqu = isset($_POST['sorqu']) ? parseNumericField($_POST['sorqu']) : 0;
$imtina = isset($_POST['imtina']) ? parseNumericField($_POST['imtina']) : 0;
$arayish = isset($_POST['arayish']) ? parseNumericField($_POST['arayish']) : 0;
$geri_qaytarilan = isset($_POST['geri_qaytarilan']) ? parseNumericField($_POST['geri_qaytarilan']) : 0;
$tesekkur = isset($_POST['tesekkur']) ? parseNumericField($_POST['tesekkur']) : 0; // YENİ SÜTUN
$imtina_gulhuseyn = isset($_POST['imtina_gulhuseyn']) ? parseNumericField($_POST['imtina_gulhuseyn']) : 0;
$imtina_aynur = isset($_POST['imtina_aynur']) ? parseNumericField($_POST['imtina_aynur']) : 0;
$imtina_adil = isset($_POST['imtina_adil']) ? parseNumericField($_POST['imtina_adil']) : 0;
$qiymetlendirme_id = isset($_POST['qiymetlendirme_id']) ? ($_POST['qiymetlendirme_id'] === '' ? null : (int)$_POST['qiymetlendirme_id']) : null; // YENİ SÜTUN

// Validasiya
$errors = [];

if (empty($employee_id)) {
    $errors[] = 'İşçi seçilməyib!';
}

if (empty($start_date)) {
    $errors[] = 'Başlanğıc tarixi boş ola bilməz!';
}

if (empty($end_date)) {
    $errors[] = 'Bitmə tarixi boş ola bilməz!';
}

if (strtotime($start_date) > strtotime($end_date)) {
    $errors[] = 'Başlanğıc tarixi bitmə tarixindən sonra ola bilməz!';
}

// İşçi məhdudiyyəti yoxlaması
if (!$is_superadmin && $current_user_role === 'isci') {
    // İşçinin öz məlumatını yoxla
    $userEmpQuery = "SELECT id FROM employees WHERE user_id = ? AND id = ?";
    $userEmpStmt = $conn->prepare($userEmpQuery);
    $userEmpStmt->bind_param("ii", $current_user_id, $employee_id);
    $userEmpStmt->execute();
    $userEmpResult = $userEmpStmt->get_result();
    
    if ($userEmpResult->num_rows === 0) {
        $errors[] = 'Yalnız öz məlumatlarınız üçün əlavə edə bilərsiniz!';
    }
}

// Qiymətləndirmə yalnız superadmin tərəfindən təyin edilə bilər
if (!$is_superadmin && !empty($qiymetlendirme_id)) {
    $errors[] = 'Qiymətləndirmə yalnız superadmin tərəfindən təyin edilə bilər!';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Validasiya xətası',
        'errors' => $errors
    ]);
    exit();
}

// Həftə üçün unikal yoxlama
$checkQuery = "SELECT id FROM weekly_works WHERE employee_id = ? AND start_date = ? AND end_date = ?";
$checkStmt = $conn->prepare($checkQuery);
$checkStmt->bind_param("iss", $employee_id, $start_date, $end_date);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    http_response_code(409);
    echo json_encode([
        'success' => false,
        'message' => 'Bu həftə üçün işçinin qeydi artıq mövcuddur!'
    ]);
    exit();
}

// Yeni qeyd əlavə et
$query = "INSERT INTO weekly_works (
            employee_id, start_date, end_date, worked_days, 
            veten_muraciyet, teskilat_muraciyet, sorqu, imtina, 
            arayish, geri_qaytarilan, tesekkur, 
            imtina_gulhuseyn, imtina_aynur, imtina_adil, 
            qiymetlendirme_id, created_at
          ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
          
$stmt = $conn->prepare($query);

// NULL dəyərini emal et
if ($qiymetlendirme_id === null) {
    $stmt->bind_param(
        "issiiiiiiiiiiii", 
        $employee_id, $start_date, $end_date, $worked_days,
        $veten_muraciyet, $teskilat_muraciyet, $sorqu, $imtina,
        $arayish, $geri_qaytarilan, $tesekkur,
        $imtina_gulhuseyn, $imtina_aynur, $imtina_adil,
        $qiymetlendirme_id
    );
} else {
    $stmt->bind_param(
        "issiiiiiiiiiiii", 
        $employee_id, $start_date, $end_date, $worked_days,
        $veten_muraciyet, $teskilat_muraciyet, $sorqu, $imtina,
        $arayish, $geri_qaytarilan, $tesekkur,
        $imtina_gulhuseyn, $imtina_aynur, $imtina_adil,
        $qiymetlendirme_id
    );
}

if ($stmt->execute()) {
    $newId = $stmt->insert_id;
    
    // Yeni əlavə olunan qeydi gətir
    $selectQuery = "SELECT ww.*, e.ad as employee_ad, e.soyad as employee_soyad, 
                    CONCAT(e.ad, ' ', e.soyad) as employee_fullname, 
                    e.user_id as employee_user_id,
                    q.title as qiymetlendirme_title
                    FROM weekly_works ww 
                    INNER JOIN employees e ON ww.employee_id = e.id
                    LEFT JOIN qiymetlendirmeler q ON ww.qiymetlendirme_id = q.id
                    WHERE ww.id = ?";
    $selectStmt = $conn->prepare($selectQuery);
    $selectStmt->bind_param("i", $newId);
    $selectStmt->execute();
    $result = $selectStmt->get_result();
    $newRecord = $result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'message' => 'İş qeydi uğurla əlavə edildi!',
        'id' => $newId,
        'record' => $newRecord
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Əlavə etmə zamanı xəta baş verdi!',
        'error' => $conn->error
    ]);
}

$stmt->close();
$conn->close();
?>