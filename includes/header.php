<?php
// Bakı vaxtı
date_default_timezone_set('Asia/Baku');
// Header faylı - Üst hissə və CSS/JS scriptləri
require_once 'auth.php';
checkAuth();

$page_title = $page_title ?? 'İnsan Hüquqları şöbəsi | Admin Panel';
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Tarix və saat funksiyaları
function getCurrentDate() {
    return date('d.m.Y');
}

function getCurrentTime() {
    return date('H:i:s');
}
?>
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    
    <link rel="icon" type="image/png" href="/sis/assets/images/logo.png">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/sis/assets/css/style.css">
    <link rel="stylesheet" href="/sis/assets/css/header.css">
    <link rel="stylesheet" href="/sis/assets/css/navigation.css">
    
    <style>
    /* Temporary fix until navigation.css is fixed */
    body {
        padding-left: 260px; /* Sidebar üçün yer */
        overflow-x: hidden;
    }
    
    @media (max-width: 992px) {
        body {
            padding-left: 0;
        }
        .sidebar {
            transform: translateX(-100%);
        }
        .sidebar.active {
            transform: translateX(0);
        }
    }
    </style>
</head>
<body>
    
    <!-- Sidebar Navigation -->
    <?php include __DIR__ . '/navigation.php'; ?>
    
    <!-- Main Content -->
    <main class="main-content">
        <!-- Content Header -->
        <header class="content-header">
            <div>
                <h4 class="mb-1"><?php echo $page_title; ?></h4>
                <p class="text-muted mb-0">
                    <i class="fas fa-calendar-alt me-1"></i> 
                    <span id="currentDate"><?php echo getCurrentDate(); ?></span> | 
                    <i class="fas fa-clock me-1"></i> 
                    <span id="currentTime"><?php echo getCurrentTime(); ?></span>
                </p>
            </div>
            
            <div class="user-info text-end">
                <div class="fw-bold">
                    <?php 
                    if (function_exists('getAdminName')) {
                        echo getAdminName();
                    } else {
                        echo isset($_SESSION['user']['name']) ? $_SESSION['user']['name'] : 'Admin';
                    }
                    ?>
                </div>
                <small class="user-role">
                    <i class="fas fa-user-shield me-1"></i>
                    <?php 
                    $role = $_SESSION['role'] ?? 'İstifadəçi';
                    echo ucfirst($role === 'superadmin' ? 'Super Admin' : $role);
                    ?>
                </small>
            </div>
        </header>

        <!-- Main Content Container -->
        <div class="container-fluid py-3">