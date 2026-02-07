<?php
// crud/employees/delete.php

require_once '../../config/database.php';
require_once '../../includes/auth.php';

require_login();

// İşçi ID-sini yoxla
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = 'Yanlış sorğu!';
    header('Location: index.php');
    exit();
}

$employeeId = (int)$_GET['id'];

// Əvvəlcə bu işçinin weekly_works qeydləri varmı yoxla
$checkQuery = "SELECT COUNT(*) as count FROM weekly_works WHERE employee_id = ?";
$checkStmt = $conn->prepare($checkQuery);
$checkStmt->bind_param("i", $employeeId);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();
$checkData = $checkResult->fetch_assoc();

if ($checkData['count'] > 0) {
    $_SESSION['error'] = 'Bu işçinin həftəlik iş qeydləri var. Əvvəlcə onları silməlisiniz.';
    header('Location: index.php');
    exit();
}

// İşçini sil
$query = "DELETE FROM employees WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $employeeId);

if ($stmt->execute()) {
    $_SESSION['success'] = 'İşçi uğurla silindi!';
} else {
    $_SESSION['error'] = 'Silinmə zamanı xəta baş verdi!';
}

header('Location: index.php');
exit();
?>