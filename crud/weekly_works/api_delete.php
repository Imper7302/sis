<?php
// weekly_works/api_delete.php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

// Login yoxlaması
require_login();

// JSON cavabı üçün header
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Yalnız POST sorğuları qəbul edilir']);
    exit();
}

// Session məlumatlarını təhlükəsiz şəkildə yoxla
$is_superadmin = false;
$current_user_id = 0;

if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
    $is_superadmin = ($_SESSION['user']['role'] === 'superadmin');
    $current_user_id = $_SESSION['user']['id'];
} elseif (isset($_SESSION['role'])) {
    $is_superadmin = ($_SESSION['role'] === 'superadmin');
    $current_user_id = $_SESSION['user_id'] ?? 0;
}

// ID-ni al
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Yanlış ID']);
    exit();
}

// Silinəcək qeydin mövcud olub-olmadığını yoxla
$checkQuery = "SELECT ww.id, ww.employee_id, e.ad, e.soyad, e.user_id 
               FROM weekly_works ww 
               INNER JOIN employees e ON ww.employee_id = e.id 
               WHERE ww.id = ?";
$checkStmt = $conn->prepare($checkQuery);
$checkStmt->bind_param("i", $id);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Qeyd tapılmadı']);
    exit();
}

$record = $checkResult->fetch_assoc();

// İcazə yoxlaması
if (!$is_superadmin && $record['user_id'] != $current_user_id) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Bu əməliyyat üçün yetkiniz yoxdur']);
    exit();
}

// Qeydi sil
$deleteQuery = "DELETE FROM weekly_works WHERE id = ?";
$deleteStmt = $conn->prepare($deleteQuery);
$deleteStmt->bind_param("i", $id);

if ($deleteStmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Qeyd uğurla silindi']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Silinmə zamanı xəta baş verdi']);
}

$deleteStmt->close();
$conn->close();
?>