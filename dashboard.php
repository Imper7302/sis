<?php
// Dashboard - Professional İdarəetmə Paneli
require_once 'config/database.php';
require_once 'includes/auth.php';
require_login();

// Yalnız superadmin dashboard-a girə bilir
if (!isSuperAdmin()) {
    // İşçi və ya digər rollar weekly_works səhifəsinə yönləndirilir
    $_SESSION['error'] = 'Bu səhifəyə giriş icazəniz yoxdur!';
    header('Location: /sis/crud/weekly_works/index.php');
    exit();
}

$title = 'Dashboard';
// Sessiya xətalarının qarşısını almaq üçün təhlükəsiz dəyişənlər
$u_name = $_SESSION['username'] ?? ($_SESSION['user']['username'] ?? 'İstifadəçi');
$u_role = $_SESSION['role'] ?? ($_SESSION['user']['role'] ?? 'isci');
$u_id   = $_SESSION['user_id'] ?? ($_SESSION['user']['id'] ?? 0);

$page_title = "Dashboard - İdarəetmə Paneli";
include 'includes/header.php';

// Tarix məlumatları
$current_month = date('Y-m');
$this_week_start = date('Y-m-d', strtotime('monday this week'));
$today = date('Y-m-d');

try {
    // 1. AYLIQ STATİSTİKA
    $sql_sums = "SELECT 
        SUM(worked_days) as total_days, 
        SUM(veten_muraciyet) as total_veten, 
        SUM(teskilat_muraciyet) as total_tesk, 
        SUM(sorqu) as total_sorqu, 
        SUM(imtina) as total_imtina, 
        SUM(arayish) as total_arayish, 
        SUM(geri_qaytarilan) as total_geri,
        SUM(tesekkur) as total_tesekkur
    FROM weekly_works 
    WHERE DATE_FORMAT(start_date, '%Y-%m') = '$current_month'";
    
    $stats = $conn->query($sql_sums)->fetch_assoc();

    // 2. İŞÇİ REYTİNQİ (Top Performer - Bu Ay)
    $sql_rank = "SELECT 
        e.ad, 
        e.soyad, 
        SUM(w.veten_muraciyet + w.teskilat_muraciyet) as total_apps,
        AVG(w.qiymetlendirme_id) as avg_qiymet
    FROM weekly_works w 
    JOIN employees e ON w.employee_id = e.id 
    WHERE DATE_FORMAT(w.start_date, '%Y-%m') = '$current_month' 
    GROUP BY e.id 
    ORDER BY total_apps DESC 
    LIMIT 3";
    
    $rankings = $conn->query($sql_rank);

    // 3. BİLDİRİŞLƏR (Notifications)
    $notifications = [];
    $new_reports = $conn->query("SELECT COUNT(*) as count FROM weekly_works WHERE DATE(created_at) = '$today'")->fetch_assoc()['count'];
    
    if($new_reports > 0) {
        $notifications[] = ["text" => "$new_reports yeni hesabat daxil edilib", "icon" => "fa-file-import", "color" => "primary"];
    }
    
    // Geri qaytarılan sənəd xəbərdarlığı
    if(($stats['total_geri'] ?? 0) > 20) {
        $notifications[] = ["text" => "Geri qaytarılan sənəd sayı kritikdir!", "icon" => "fa-exclamation-circle", "color" => "danger"];
    }
    
    // Təşəkkür sayı xəbərdarlığı
    if(($stats['total_tesekkur'] ?? 0) > 10) {
        $notifications[] = ["text" => "Təşəkkür sayı yüksəkdir! Əla iş!", "icon" => "fa-thumbs-up", "color" => "success"];
    }

    // 4. GÖZLƏYƏN İŞLƏR (Reminders - Bu həftə hesabat verməyənlər)
    $sql_reminders = "SELECT id, ad, soyad FROM employees WHERE id NOT IN (
        SELECT employee_id FROM weekly_works WHERE start_date >= '$this_week_start'
    )";
    $reminders = $conn->query($sql_reminders);

    // 5. SON 5 İŞ - Qiymətləndirmə ilə birlikdə
    $sql_recent = "SELECT 
        w.*, 
        CONCAT(e.ad, ' ', e.soyad) as fullname,
        q.title as qiymetlendirme_title,
        q.score as qiymetlendirme_score
    FROM weekly_works w 
    JOIN employees e ON w.employee_id = e.id
    LEFT JOIN qiymetlendirmeler q ON w.qiymetlendirme_id = q.id
    ORDER BY w.created_at DESC 
    LIMIT 5";
    
    $recent_works = $conn->query($sql_recent);

} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<style>
.dashboard-card {
    border: none;
    border-radius: 12px;
    transition: 0.3s;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    background: #fff;
}

.dashboard-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0,0,0,0.1);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
}

.rank-item {
    transition: 0.2s;
    border: 1px solid #eee;
    border-radius: 10px;
}

.rank-item:hover {
    background: #f8f9fa;
}

.rank-1 { color: #FFD700; font-size: 1.5rem; } /* Qızıl */
.rank-2 { color: #C0C0C0; font-size: 1.3rem; } /* Gümüş */
.rank-3 { color: #CD7F32; font-size: 1.1rem; } /* Bürünc */

.qiymet-badge {
    font-size: 0.75em;
    padding: 3px 8px;
    border-radius: 20px;
    font-weight: 600;
}

.qiymet-ela {
    background: linear-gradient(45deg, #198754, #20c997);
    color: white;
}

.qiymet-yaxsi {
    background: linear-gradient(45deg, #0d6efd, #0dcaf0);
    color: white;
}

.qiymet-kafi {
    background: linear-gradient(45deg, #ffc107, #fd7e14);
    color: white;
}
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0 text-dark">Salam, <?php echo htmlspecialchars($u_name); ?>!</h3>
            <p class="text-muted mb-0">Sistemin bugünkü vəziyyəti ilə tanış olun.</p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-white text-primary border p-2 px-3 rounded-pill shadow-sm">
                <i class="fas fa-calendar-day me-2"></i><?php echo date('d.m.Y'); ?>
            </span>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <?php 
        $cards = [
            ['İş Günü', $stats['total_days'] ?? 0, 'fa-calendar-alt', 'primary'],
            ['Vətəndaş', $stats['total_veten'] ?? 0, 'fa-users', 'success'],
            ['Təşkilat', $stats['total_tesk'] ?? 0, 'fa-building', 'info'],
            ['Təşəkkür', $stats['total_tesekkur'] ?? 0, 'fa-heart', 'danger'],
            ['Sorğu', $stats['total_sorqu'] ?? 0, 'fa-search', 'warning'],
            ['Geri Qay.', $stats['total_geri'] ?? 0, 'fa-undo', 'secondary']
        ];
        
        foreach($cards as $c): 
        ?>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card dashboard-card h-100">
                <div class="card-body">
                    <div class="stat-icon bg-<?php echo $c[3]; ?> bg-opacity-10 text-<?php echo $c[3]; ?> mb-3">
                        <i class="fas <?php echo $c[2]; ?>"></i>
                    </div>
                    <div class="text-muted small fw-bold text-uppercase"><?php echo $c[0]; ?></div>
                    <div class="h4 fw-bold mb-0"><?php echo $c[1]; ?></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card dashboard-card mb-4">
                <div class="card-header bg-transparent border-0 py-3">
                    <h5 class="fw-bold mb-0 text-white"><i class="fas fa-medal text-warning me-2"></i>Ayın Ən Aktivləri (Reytinq)</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php 
                        $rank_count = 1;
                        if($rankings->num_rows > 0): 
                            while($r = $rankings->fetch_assoc()): 
                                $qiymet_class = '';
                                $qiymet_text = '';
                                
                                if ($r['avg_qiymet'] >= 2.5) {
                                    $qiymet_class = 'qiymet-ela';
                                    $qiymet_text = 'Əla';
                                } elseif ($r['avg_qiymet'] >= 1.5) {
                                    $qiymet_class = 'qiymet-yaxsi';
                                    $qiymet_text = 'Yaxşı';
                                } elseif ($r['avg_qiymet'] > 0) {
                                    $qiymet_class = 'qiymet-kafi';
                                    $qiymet_text = 'Kafi';
                                }
                        ?>
                        <div class="col-md-4">
                            <div class="rank-item p-3 text-center">
                                <i class="fas fa-trophy rank-<?php echo $rank_count; ?> mb-2"></i>
                                <div class="fw-bold text-dark"><?php echo $r['ad'].' '.$r['soyad']; ?></div>
                                <div class="small text-muted mb-1"><?php echo $r['total_apps']; ?> Müraciət</div>
                                <?php if($qiymet_text): ?>
                                <div>
                                    <span class="badge <?php echo $qiymet_class; ?> qiymet-badge">
                                        <?php echo $qiymet_text; ?>
                                    </span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php 
                            $rank_count++;
                            endwhile; 
                        else: 
                        ?>
                        <div class="text-center p-3 text-muted">Məlumat yoxdur</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="card dashboard-card">
                <div class="card-header bg-transparent border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-white">Son Daxil Edilən İşlər</h5>
                    <a href="crud/weekly_works/index.php" class="btn btn-sm btn-light border">Hamısı</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr class="small text-uppercase text-muted">
                                    <th class="ps-4">İşçi</th>
                                    <th>Başlanğıc</th>
                                    <th>İş Günü</th>
                                    <th class="text-center">Müraciət</th>
                                    <th class="text-center">Qiymətləndirmə</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($w = $recent_works->fetch_assoc()): 
                                    $qiymet_class = '';
                                    $qiymet_text = $w['qiymetlendirme_title'] ?: '-';
                                    
                                    if ($w['qiymetlendirme_title']) {
                                        switch($w['qiymetlendirme_title']) {
                                            case 'Əla':
                                                $qiymet_class = 'qiymet-ela';
                                                break;
                                            case 'Yaxşı':
                                                $qiymet_class = 'qiymet-yaxsi';
                                                break;
                                            case 'Kafi':
                                                $qiymet_class = 'qiymet-kafi';
                                                break;
                                        }
                                    }
                                ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-dark"><?php echo $w['fullname']; ?></td>
                                    <td class="small"><?php echo date('d.m.Y', strtotime($w['start_date'])); ?></td>
                                    <td><span class="badge bg-light text-dark"><?php echo $w['worked_days']; ?> gün</span></td>
                                    <td class="text-center fw-bold text-primary"><?php echo $w['veten_muraciyet'] + $w['teskilat_muraciyet']; ?></td>
                                    <td class="text-center">
                                        <?php if ($w['qiymetlendirme_title']): ?>
                                            <span class="badge <?php echo $qiymet_class; ?> qiymet-badge">
                                                <?php echo htmlspecialchars($w['qiymetlendirme_title']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card dashboard-card mb-4 border-top border-primary border-4">
                <div class="card-header bg-transparent border-0 py-3">
                    <h5 class="fw-bold mb-0 text-white"><i class="fas fa-bell me-2 text-primary"></i>Bildirişlər</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php if(empty($notifications)): ?>
                            <div class="p-4 text-center text-muted small">Hazırda yeni bildiriş yoxdur.</div>
                        <?php else: 
                            foreach($notifications as $n): ?>
                            <div class="list-group-item d-flex align-items-center border-0 py-3">
                                <div class="stat-icon bg-<?php echo $n['color']; ?> bg-opacity-10 text-<?php echo $n['color']; ?> me-3" style="width:35px; height:35px; font-size:1rem;">
                                    <i class="fas <?php echo $n['icon']; ?>"></i>
                                </div>
                                <div class="small fw-bold"><?php echo $n['text']; ?></div>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>
            </div>

            <div class="card dashboard-card border-top border-danger border-4">
                <div class="card-header bg-transparent border-0 py-3 pb-0">
                    <h5 class="fw-bold mb-0 text-white"><i class="fas fa-clock me-2 text-danger"></i>Hesabat Verməyənlər</h5>
                    <small class="text-white">Bu həftə üzrə qeydi olmayanlar</small>
                </div>
                <div class="card-body py-3">
                    <div class="d-flex flex-wrap gap-2">
                        <?php if($reminders->num_rows > 0): 
                            while($rem = $reminders->fetch_assoc()): ?>
                            <div class="d-flex justify-content-between align-items-center bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded p-2 mb-2">
                                <span class="text-danger small fw-bold">
                                    <i class="fas fa-user-clock me-1"></i><?php echo $rem['ad'].' '.$rem['soyad']; ?>
                                </span>
                                <?php if($u_role === 'superadmin'): ?>
                                <button class="btn btn-xs btn-danger py-0 px-2" style="font-size: 10px;" onclick="openAbsenceModal(<?php echo $rem['id']; ?>)">
                                    Qeyd apar
                                </button>
                                <?php endif; ?>
                            </div>
                            <?php endwhile; 
                        else: ?>
                            <div class="alert alert-success w-100 py-2 small mb-0">
                                <i class="fas fa-check-circle me-1"></i>Bütün işçilər hesabat verib.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <a href="crud/weekly_works/index.php" class="btn btn-primary w-100 p-3 rounded-3 shadow-sm fw-bold mb-2">
                    <i class="fas fa-plus-circle me-2"></i>Yeni Hesabat Daxil Et
                </a>
                <?php if($u_role === 'superadmin'): ?>
                <a href="crud/employees/index.php" class="btn btn-outline-dark w-100 p-2 rounded-3 border-dashed">
                    <i class="fas fa-users-cog me-2"></i>İşçi İdarəetmə Paneli
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="absenceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Qeyri-iş Günü Qeydiyyatı</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="absenceForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">İşçi Seçin</label>
                        <select class="form-select" name="employee_id" required>
                            <?php 
                            // Aktiv işçilərin siyahısı (Reminder-də olanlar)
                            $reminders->data_seek(0); // Query-ni başa qaytarırıq
                            while($rem = $reminders->fetch_assoc()): 
                            ?>
                            <option value="<?php echo $rem['id']; ?>"><?php echo $rem['ad'].' '.$rem['soyad']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Səbəb</label>
                        <select class="form-select" name="status">
                            <option value="mezuniyyet">Məzuniyyət</option>
                            <option value="xestelik">Xəstəlik</option>
                            <option value="ezamiyyet">Ezamiyyət</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Başlanğıc Tarixi</label>
                            <input type="date" class="form-control" name="start_date" value="<?php echo $this_week_start; ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Bitmə Tarixi</label>
                            <input type="date" class="form-control" name="end_date" value="<?php echo date('Y-m-d', strtotime('sunday this week')); ?>" required>
                        </div>
                    </div>
                    <input type="hidden" name="worked_days" value="0">
                    <input type="hidden" name="qiymetlendirme_id" value="">
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Ləğv et</button>
                    <button type="submit" class="btn btn-primary">Yadda saxla</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Dəyişəni əvvəlcədən elan edirik
let absenceModal;

// Səhifə tam yükləndikdən sonra işə düşsün
document.addEventListener('DOMContentLoaded', function() {
    const modalElement = document.getElementById('absenceModal');
    if (modalElement) {
        absenceModal = new bootstrap.Modal(modalElement);
    }
});

// Düyməyə kliklədikdə işləyən funksiya
function openAbsenceModal(employeeId) {
    // Əgər modal hələ yaranmayıbsa, bir az gözlə və ya yenidən cəhd et
    if (!absenceModal) {
        const modalElement = document.getElementById('absenceModal');
        absenceModal = new bootstrap.Modal(modalElement);
    }
    
    const select = document.querySelector('select[name="employee_id"]');
    if(select) {
        select.value = employeeId;
    }
    absenceModal.show();
}

// Formun göndərilməsi (API-yə qoşulma)
document.getElementById('absenceForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    
    // API-nin gözlədiyi digər sütunları 0 olaraq doldururuq
    const zeroFields = ['veten_muraciyet', 'teskilat_muraciyet', 'sorqu', 'imtina', 'arayish', 'geri_qaytarilan', 'imtina_gulhuseyn', 'imtina_aynur', 'imtina_adil', 'tesekkur'];
    zeroFields.forEach(f => fd.append(f, 0));
    
    fetch('crud/weekly_works/api_create.php', {
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
    .catch(err => {
        console.error('Xəta:', err);
        alert('Məlumat göndərilərkən xəta baş verdi.');
    });
});
</script>

<?php include 'includes/footer.php'; ?>