<?php
// Auth faylı - Giriş yoxlamaları
require_once __DIR__ . '/../config/database.php';

// Əgər session başlamayıbsa başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database faylında artıq clean_input() funksiyası var, buna görə burada təkrar təyin etməyin
// Giriş yoxlaması
function checkAuth() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        header('Location: ../login.php');
        exit();
    }
    return true;
}

// Superadmin yoxlaması
function isSuperAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin';
}

// İşçi yoxlaması
function isEmployee() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'isci';
}

// Yenidən yönləndirmə funksiyası
function redirect($url) {
    header("Location: $url");
    exit();
}

// Çıxış funksiyası
function logout() {
    $_SESSION = array();
    session_destroy();
    redirect('../login.php');
}

// Admin ID almaq
function getAdminId() {
    return $_SESSION['user_id'] ?? null;
}

// Admin adını almaq
function getAdminName() {
    return $_SESSION['fullname'] ?? $_SESSION['username'] ?? 'İstifadəçi';
}
?>