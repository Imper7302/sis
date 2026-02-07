<?php
// weekly_works/index.php
require_once '../../config/database.php';
require_once '../../includes/auth.php';
// Login yoxlaması
require_login();

$title = 'Həftəlik İşlər';

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

// Sayt parametrləri
$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filter parametrləri
$employee_id = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : 0;
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Sorgu şərtləri
$conditions = [];
$params = [];
$types = '';

if ($employee_id > 0) {
    $conditions[] = "ww.employee_id = ?";
    $params[] = $employee_id;
    $types .= 'i';
}

if (!empty($start_date)) {
    $conditions[] = "ww.end_date >= ?";
    $params[] = $start_date;
    $types .= 's';
}

if (!empty($end_date)) {
    $conditions[] = "ww.start_date <= ?";
    $params[] = $end_date;
    $types .= 's';
}

if (!$is_superadmin && $current_user_role === 'isci') {
    $conditions[] = "e.user_id = ?";
    $params[] = $current_user_id;
    $types .= 'i';
}

$whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Qiymətləndirmə siyahısı (ID => ad + avtomatik rəng)
$qiymetlendirmeler = [];
$qRes = $conn->query("SELECT id, title FROM qiymetlendirmeler ORDER BY id");
$defaultColors = [
    1 => '#198754', // yaşıl
    2 => '#0d6efd', // mavi
    3 => '#ffc107', // sarı
];

if ($qRes) {
    while ($q = $qRes->fetch_assoc()) {
        $qiymetlendirmeler[$q['id']] = [
            'title' => $q['title'],
            'color' => $defaultColors[$q['id']] ?? '#6c757d' // default boz
        ];
    }
}

// Ümumi statistika
$statsQuery = "SELECT COUNT(*) as total, SUM(ww.worked_days) as total_days, SUM(ww.veten_muraciyet + ww.teskilat_muraciyet) as total_apps FROM weekly_works ww INNER JOIN employees e ON ww.employee_id = e.id $whereClause";

if (!empty($params)) {
    $stmtStats = $conn->prepare($statsQuery);
    $stmtStats->bind_param($types, ...$params);
    $stmtStats->execute();
    $statsData = $stmtStats->get_result()->fetch_assoc();
} else {
    $statsData = $conn->query($statsQuery)->fetch_assoc();
}

// Məlumatları gətir - qiymətləndirmə adını JOIN ilə əlavə edirik
$query = "SELECT ww.*, e.ad as employee_ad, e.soyad as employee_soyad, 
          CONCAT(e.ad, ' ', e.soyad) as fullname, 
          e.user_id as employee_user_id,
          q.title as qiymetlendirme_title,
          q.id as qiymetlendirme_id
          FROM weekly_works ww 
          INNER JOIN employees e ON ww.employee_id = e.id
          LEFT JOIN qiymetlendirmeler q ON ww.qiymetlendirme_id = q.id
          $whereClause 
          ORDER BY ww.id DESC LIMIT ? OFFSET ?";

$limitOffsetParams = $params;
$limitOffsetTypes = $types . 'ii';
$limitOffsetParams[] = $limit;
$limitOffsetParams[] = $offset;

$stmt = $conn->prepare($query);
if ($limitOffsetTypes) {
    $stmt->bind_param($limitOffsetTypes, ...$limitOffsetParams);
}
$stmt->execute();
$weekly_works = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// NÖMRƏ SIRALAMASI ÜÇÜN DÜZƏLİŞ:
// Ümumi məlumat sayını hesabla (səhifələmə nəzərə alınmadan)
$totalQuery = "SELECT COUNT(*) as total FROM weekly_works ww INNER JOIN employees e ON ww.employee_id = e.id $whereClause";

if (!empty($params)) {
    $stmtTotal = $conn->prepare($totalQuery);
    $stmtTotal->bind_param($types, ...$params);
    $stmtTotal->execute();
    $totalResult = $stmtTotal->get_result()->fetch_assoc();
    $total_records = $totalResult['total'];
} else {
    $totalResult = $conn->query($totalQuery)->fetch_assoc();
    $total_records = $totalResult['total'];
}

// İşçi listi
$employees = $conn->query("SELECT id, ad, soyad, user_id FROM employees")->fetch_all(MYSQLI_ASSOC);
$current_employee = null;
foreach ($employees as $emp) {
    if ($emp['user_id'] == $current_user_id) $current_employee = $emp;
}

include '../../includes/header.php';
?>

<link rel="stylesheet" href="../../assets/css/weekly_w_in.css">


<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-start border-primary border-4 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted small text-uppercase fw-bold">Ümumi Qeyd</h6>
                    <h3 class="fw-bold mb-0"><?php echo $statsData['total'] ?? 0; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-success border-4 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted small text-uppercase fw-bold">Cəmi İş Günü</h6>
                    <h3 class="fw-bold mb-0"><?php echo $statsData['total_days'] ?? 0; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-info border-4 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted small text-uppercase fw-bold">Ümumi Müraciət</h6>
                    <h3 class="fw-bold mb-0"><?php echo $statsData['total_apps'] ?? 0; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-warning border-4 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted small text-uppercase fw-bold">Aktiv İşçilər</h6>
                    <h3 class="fw-bold mb-0"><?php echo count($employees); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold small">İşçi</label>
                    <select class="form-select" name="employee_id">
                        <option value="0">Hamısı</option>
                        <?php foreach ($employees as $emp): ?>
                            <option value="<?php echo $emp['id']; ?>" <?php echo $employee_id == $emp['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($emp['ad'] . ' ' . $emp['soyad']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small">Başlanğıc Tarixi</label>
                    <input type="date" class="form-control" name="start_date" value="<?php echo $start_date; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small">Bitmə Tarixi</label>
                    <input type="date" class="form-control" name="end_date" value="<?php echo $end_date; ?>">
                </div>
                <div class="col-md-3">
                    <div class="btn-group w-100">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                        <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-sync"></i></a>
                        <button type="button" class="btn btn-outline-success" onclick="exportToExcel()"><i class="fas fa-file-excel"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold text-white"><i class="fas fa-list me-2"></i>İş Siyahısı</h5>
        <div>
            <?php if ($is_superadmin || $current_user_role === 'isci'): ?>
                <button onclick="addNewRow()" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>Yeni Sətir</button>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 table-compact" id="weeklyWorksTable">
            <thead class="table-light small">
                <tr class="text-center">
                    <th class="fw-semibold">#</th>
                    <th class="fw-semibold">İşçi</th>
                    <th class="fw-semibold">Həftə</th>
                    <th class="fw-semibold" title="İş Günü">Gün</th>
                    <th class="fw-semibold" title="Vətəndaş Müraciəti">Vət.</th>
                    <th class="fw-semibold" title="Təşkilat Müraciəti">Təş.</th>
                    <th class="fw-semibold" title="Sorğu">Sor.</th>
                    <th class="fw-semibold" title="İmtina">İmt.</th>
                    <th class="fw-semibold" title="Arayış">Ara.</th>
                    <th class="fw-semibold" title="Geri Qaytarılan">G.Q.</th>
                    <th class="fw-semibold" title="Təşəkkür">Təş.</th>
                    <th class="fw-semibold" title="Gülhüseyn">Gül.</th>
                    <th class="fw-semibold" title="Aynur">Ayn.</th>
                    <th class="fw-semibold" title="Adil">Adil</th>
                    <th class="fw-semibold">Qiymət</th>
                    <th class="fw-semibold">Əməliyyat</th>
                </tr>
            </thead>
            <tbody id="tableBody" class="small">
                <?php if (count($weekly_works) > 0): ?>
                    <?php 
                    // BÖYÜKDƏN KİÇİYƏ NÖMRƏLƏMƏ
                    $start_number = $total_records - $offset;
                    foreach ($weekly_works as $index => $work): 
                        $row_number = $start_number - $index;
                    ?>
                    <tr id="row-<?php echo $work['id']; ?>" 
                        data-id="<?php echo $work['id']; ?>" 
                        data-qiymetlendirme-id="<?php echo $work['qiymetlendirme_id'] ?? ''; ?>"
                        class="align-middle">
                        <td class="text-center">
                            <span class="badge bg-secondary" style="font-size: 0.7rem;"><?php echo $row_number; ?></span>
                        </td>
                        <td>
                            <div class="employee-name" style="font-size: 0.8rem; line-height: 1.1;">
                                <?php echo htmlspecialchars($work['fullname']); ?>
                            </div>
                        </td>
                        <td>
                            <div class="week-info">
                                <?php echo date('d.m', strtotime($work['start_date'])); ?>-
                                <?php echo date('d.m.Y', strtotime($work['end_date'])); ?>
                            </div>
                        </td>
                        <td class="text-center numeric-value bg-light fw-bold"><?php echo $work['worked_days']; ?></td>
                        <td class="text-center numeric-value text-primary fw-bold"><?php echo $work['veten_muraciyet']; ?></td>
                        <td class="text-center numeric-value text-success fw-bold"><?php echo $work['teskilat_muraciyet']; ?></td>
                        <td class="text-center numeric-value"><?php echo $work['sorqu']; ?></td>
                        <td class="text-center numeric-value"><?php echo $work['imtina']; ?></td>
                        <td class="text-center numeric-value"><?php echo $work['arayish']; ?></td>
                        <td class="text-center numeric-value text-danger fw-bold"><?php echo $work['geri_qaytarilan']; ?></td>
                        <td class="text-center numeric-value"><?php echo $work['tesekkur']; ?></td>
                        <td class="text-center numeric-value"><?php echo $work['imtina_gulhuseyn']; ?></td>
                        <td class="text-center numeric-value"><?php echo $work['imtina_aynur']; ?></td>
                        <td class="text-center numeric-value"><?php echo $work['imtina_adil']; ?></td>
                        <td class="text-center" id="qiymet-cell-<?php echo $work['id']; ?>">
                            <?php if ($work['qiymetlendirme_title']): ?>
                                <span class="badge qiymet-badge" 
                                    style="background-color:<?php echo $qiymetlendirmeler[$work['qiymetlendirme_id']]['color'] ?? '#6c757d'; ?>">
                                    <?php echo htmlspecialchars($work['qiymetlendirme_title']); ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <?php if ($is_superadmin): ?>
                                    <button onclick="editRow(<?php echo $work['id']; ?>)" class="btn btn-outline-warning" title="Redaktə et">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteRecord(<?php echo $work['id']; ?>)" class="btn btn-outline-danger" title="Sil">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php elseif ($work['employee_user_id'] == $current_user_id): ?>
                                    <span class="text-muted fst-italic">Təsdiqləndi</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr id="emptyRow"><td colspan="16" class="text-center py-4">Məlumat yoxdur</td></tr>
                <?php endif; ?>
            </tbody>
            <tfoot id="summaryRow" class="table-light fw-bold text-center"></tfoot>
        </table>
    </div>
</div>
</div>
</div>

<script>
// PHP-dən gələn dəyişənləri təhlükəsiz şəkildə JS-ə ötürürük
const isSuperadmin = <?php echo $is_superadmin ? 'true' : 'false'; ?>;
const employees = <?php echo json_encode($employees ?: []); ?>;
const currentEmployee = <?php echo $current_employee ? json_encode($current_employee) : 'null'; ?>;
const currentUserId = <?php echo (int)($current_user_id ?? 0); ?>;

// Qiymətləndirmə məlumatlarını JS-ə ötürürük
const qiymetlendirmeler = <?php echo json_encode($qiymetlendirmeler); ?>;

// Cari səhifə cəmini hesabla
function calculateSummary() {
    const rows = document.querySelectorAll('#tableBody tr:not(.new-row):not(#emptyRow)');
    let s = {
        days: 0, veten: 0, tesk: 0, sorqu: 0, imtina: 0, 
        arayish: 0, geri: 0, tesekkur: 0, g: 0, ay: 0, ad: 0
    };
    
    rows.forEach(row => {
        const c = row.cells;
        if(c.length < 15) return;
        
        s.days += parseInt(c[3].textContent.trim()) || 0;
        s.veten += parseInt(c[4].textContent.trim()) || 0;
        s.tesk += parseInt(c[5].textContent.trim()) || 0;
        s.sorqu += parseInt(c[6].textContent.trim()) || 0;
        s.imtina += parseInt(c[7].textContent.trim()) || 0;
        s.arayish += parseInt(c[8].textContent.trim()) || 0;
        s.geri += parseInt(c[9].textContent.trim()) || 0;
        s.tesekkur += parseInt(c[10].textContent.trim()) || 0;
        s.g += parseInt(c[11].textContent.trim()) || 0;
        s.ay += parseInt(c[12].textContent.trim()) || 0;
        s.ad += parseInt(c[13].textContent.trim()) || 0;
    });
    
    const summaryRow = document.getElementById('summaryRow');
    if (summaryRow) {
        summaryRow.innerHTML = `
            <td colspan="3" class="text-end fw-bold">SƏHİFƏ CƏMİ:</td>
            <td class="fw-bold">${s.days}</td>
            <td class="text-primary fw-bold">${s.veten}</td>
            <td class="text-success fw-bold">${s.tesk}</td>
            <td class="fw-bold">${s.sorqu}</td>
            <td class="fw-bold">${s.imtina}</td>
            <td class="fw-bold">${s.arayish}</td>
            <td class="text-danger fw-bold">${s.geri}</td>
            <td class="fw-bold">${s.tesekkur}</td>
            <td class="fw-bold">${s.g}</td>
            <td class="fw-bold">${s.ay}</td>
            <td class="fw-bold">${s.ad}</td>
            <td colspan="2"></td>`;
    }
}

// Yeni sətir əlavə et
function addNewRow() {
    const tableBody = document.getElementById('tableBody');
    const emptyRow = document.getElementById('emptyRow');
    if (emptyRow) emptyRow.remove();
    
    // Cari tarixi al
    const today = new Date();
    
    // End date = bu gün (format: YYYY-MM-DD)
    const endDate = today.toISOString().split('T')[0];
    
    // Start date = 4 gün əvvəl (5 günlük həftə üçün)
    const startDateObj = new Date(today);
    startDateObj.setDate(today.getDate() - 4);
    const startDate = startDateObj.toISOString().split('T')[0];
    
    const newRow = document.createElement('tr');
    newRow.className = 'new-row table-warning';
    
    let empSelect = isSuperadmin ? 
        `<select class="form-select form-select-sm" name="employee_id">
            ${employees.map(e => `<option value="${e.id}" ${e.user_id == currentUserId ? 'selected' : ''}>${e.ad} ${e.soyad}</option>`).join('')}
        </select>` : 
        `<strong>${currentEmployee ? currentEmployee.ad + ' ' + currentEmployee.soyad : 'Naməlum'}</strong>
         <input type="hidden" name="employee_id" value="${currentEmployee ? currentEmployee.id : ''}">`;
    
    // Qiymətləndirmə optionlarını yaradırıq
    let qiymetOptions = '<option value="">Seçin</option>';
    Object.entries(qiymetlendirmeler).forEach(([id, q]) => {
        qiymetOptions += `<option value="${id}">${q.title}</option>`;
    });
    
    newRow.innerHTML = `
        <td class="text-center">*</td>
        <td>${empSelect}</td>
        <td><div class="d-flex gap-1">
            <input type="date" class="form-control form-control-sm" 
                   name="start_date" 
                   value="${startDate}" 
                   max="${endDate}" 
                   required
                   onchange="validateStartDate(this, '${endDate}')">
            <input type="date" class="form-control form-control-sm" 
                   name="end_date" 
                   value="${endDate}" 
                   max="${endDate}" 
                   required
                   onchange="validateEndDate(this, '${startDate}')">
        </div></td>
        <td><input type="number" class="form-control form-control-sm text-center" name="worked_days" value="5" min="0" max="7" required></td>
        <td><input type="number" class="form-control form-control-sm text-center" name="veten_muraciyet" value="0" min="0"></td>
        <td><input type="number" class="form-control form-control-sm text-center" name="teskilat_muraciyet" value="0" min="0"></td>
        <td><input type="number" class="form-control form-control-sm text-center" name="sorqu" value="0" min="0"></td>
        <td><input type="number" class="form-control form-control-sm text-center" name="imtina" value="0" min="0"></td>
        <td><input type="number" class="form-control form-control-sm text-center" name="arayish" value="0" min="0"></td>
        <td><input type="number" class="form-control form-control-sm text-center" name="geri_qaytarilan" value="0" min="0"></td>
        <td><input type="number" class="form-control form-control-sm text-center" name="tesekkur" value="0" min="0"></td>
        <td><input type="number" class="form-control form-control-sm text-center" name="imtina_gulhuseyn" value="0" min="0"></td>
        <td><input type="number" class="form-control form-control-sm text-center" name="imtina_aynur" value="0" min="0"></td>
        <td><input type="number" class="form-control form-control-sm text-center" name="imtina_adil" value="0" min="0"></td>
        <td>
            ${isSuperadmin ? 
                `<select class="form-select form-select-sm" name="qiymetlendirme_id">
                    ${qiymetOptions}
                </select>` : 
                `<input type="hidden" name="qiymetlendirme_id" value="">`}
        </td>
        <td class="text-center">
            <div class="btn-group btn-group-sm">
                <button onclick="saveNewRow(this)" class="btn btn-success"><i class="fas fa-check"></i></button>
                <button onclick="cancelRow(this)" class="btn btn-danger"><i class="fas fa-times"></i></button>
            </div>
        </td>`;
    
    tableBody.insertBefore(newRow, tableBody.firstChild);
    
    // İş günləri avtomatik hesabla
    autoCalculateWorkedDays(newRow);
}

// Start date validasiya funksiyası
function validateStartDate(input, maxEndDate) {
    const startDate = input.value;
    const endDateInput = input.closest('tr').querySelector('input[name="end_date"]');
    
    // Start date end date-dən böyük ola bilməz
    if (startDate > maxEndDate) {
        alert('Başlanğıc tarixi bitmə tarixindən böyük ola bilməz!');
        input.value = maxEndDate;
        return;
    }
    
    // End date-in max dəyərini yenilə
    if (startDate > endDateInput.value) {
        endDateInput.value = startDate;
    }
    
    // İş günlərini yenidən hesabla
    autoCalculateWorkedDays(input.closest('tr'));
}

// End date validasiya funksiyası
function validateEndDate(input, defaultStartDate) {
    const endDate = input.value;
    const startDateInput = input.closest('tr').querySelector('input[name="start_date"]');
    
    // End date start date-dən kiçik ola bilməz
    if (endDate < startDateInput.value) {
        alert('Bitmə tarixi başlanğıc tarixindən kiçik ola bilməz!');
        input.value = startDateInput.value;
        return;
    }
    
    // İş günlərini yenidən hesabla
    autoCalculateWorkedDays(input.closest('tr'));
}

// İş günlərini avtomatik hesabla
function autoCalculateWorkedDays(row) {
    const startDateInput = row.querySelector('input[name="start_date"]');
    const endDateInput = row.querySelector('input[name="end_date"]');
    const workedDaysInput = row.querySelector('input[name="worked_days"]');
    
    if (startDateInput.value && endDateInput.value) {
        const start = new Date(startDateInput.value);
        const end = new Date(endDateInput.value);
        
        // Tarix fərqini günlə hesabla (millisaniyələri günə çevir)
        const timeDiff = Math.abs(end.getTime() - start.getTime());
        const dayDiff = Math.ceil(timeDiff / (1000 * 60 * 60 * 24)) + 1; // +1 çünki hər iki tarix daxildir
        
        // Maksimum 7 gün məhdudiyyəti
        const calculatedDays = Math.min(dayDiff, 7);
        
        // İş günləri inputuna təyin et
        workedDaysInput.value = calculatedDays;
        
        // Əgər hesablanmış gün sayı 0-dan böyükdürsə, həftəni göstər
        if (calculatedDays > 0) {
            const startFormatted = formatDate(start);
            const endFormatted = formatDate(end);
            console.log(`Avtomatik dolduruldu: ${startFormatted} - ${endFormatted} (${calculatedDays} gün)`);
        }
    }
}

// Tarixi gözəl formatla göstər
function formatDate(date) {
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return `${day}.${month}.${year}`;
}

// Sayt açılanda cari tarixi ayarla
document.addEventListener('DOMContentLoaded', function() {
    calculateSummary();
    
    // Cari günün tarixini console-da göstər (debug üçün)
    const today = new Date();
    console.log(`Cari tarix: ${formatDate(today)}`);
});

// Redaktə rejimi
function editRow(id) {
    if (!isSuperadmin) return;
    
    const row = document.getElementById('row-' + id);
    if (!row || row.classList.contains('editing')) return;
    
    const cells = row.cells;
    const originalData = {
        worked_days: cells[3].textContent.trim(),
        veten_muraciyet: cells[4].textContent.trim(),
        teskilat_muraciyet: cells[5].textContent.trim(),
        sorqu: cells[6].textContent.trim(),
        imtina: cells[7].textContent.trim(),
        arayish: cells[8].textContent.trim(),
        geri_qaytarilan: cells[9].textContent.trim(),
        tesekkur: cells[10].textContent.trim(),
        imtina_gulhuseyn: cells[11].textContent.trim(),
        imtina_aynur: cells[12].textContent.trim(),
        imtina_adil: cells[13].textContent.trim()
    };
    
    // Cari qiymətləndirmə ID-sini al
    const currentQiymetId = row.getAttribute('data-qiymetlendirme-id') || '';
    
    row.classList.add('editing', 'table-info');
    
    // İş günləri
    cells[3].innerHTML = `<input type="number" class="form-control form-control-sm text-center" value="${originalData.worked_days}" min="0" max="7" id="edit-worked-days-${id}">`;
    
    // Digər sahələr
    const fields = ['veten_muraciyet', 'teskilat_muraciyet', 'sorqu', 'imtina', 'arayish', 'geri_qaytarilan', 'tesekkur', 'imtina_gulhuseyn', 'imtina_aynur', 'imtina_adil'];
    fields.forEach((field, index) => {
        cells[index + 4].innerHTML = `<input type="number" class="form-control form-control-sm text-center" value="${originalData[field]}" min="0" id="edit-${field}-${id}">`;
    });
    
    // Qiymətləndirmə sütununu dropdown ilə əvəz et
    let qiymetOptions = '<option value="">Seçin</option>';
    Object.entries(qiymetlendirmeler).forEach(([qId, q]) => {
        const selected = qId == currentQiymetId ? 'selected' : '';
        qiymetOptions += `<option value="${qId}" ${selected} style="background-color:${q.color}">${q.title}</option>`;
    });
    
    cells[14].innerHTML = `
        <select class="form-select form-select-sm" id="edit-qiymetlendirme-${id}" 
                style="background-color:${currentQiymetId && qiymetlendirmeler[currentQiymetId] ? qiymetlendirmeler[currentQiymetId].color : '#f8f9fa'}">
            ${qiymetOptions}
        </select>`;
    
    // İdarə sütununu yenilə
    cells[15].innerHTML = `
        <div class="btn-group btn-group-sm">
            <button onclick="saveEdit(${id})" class="btn btn-success"><i class="fas fa-save"></i></button>
            <button onclick="cancelEdit(${id})" class="btn btn-secondary"><i class="fas fa-undo"></i></button>
        </div>`;
}

// Redaktəni yadda saxla
function saveEdit(id) {
    const row = document.getElementById('row-' + id);
    const fd = new FormData();
    fd.append('id', id);
    
    // Bütün input dəyərlərini əlavə et
    const inputs = row.querySelectorAll('input');
    inputs.forEach(input => {
        const fieldName = input.id.replace(`edit-`, '').replace(`-${id}`, '').replace(/-/g, '_');
        fd.append(fieldName, input.value);
    });
    
    // Qiymətləndirmə dəyərini əlavə et
    const qiymetSelect = document.getElementById(`edit-qiymetlendirme-${id}`);
    if (qiymetSelect) {
        fd.append('qiymetlendirme_id', qiymetSelect.value);
    }
    
    fetch('api_update.php', {
        method: 'POST',
        body: fd
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Xəta: ' + data.message);
        }
    })
    .catch(err => alert('Sistem xətası baş verdi.'));
}

function cancelEdit(id) {
    location.reload();
}

function saveNewRow(btn) {
    const row = btn.closest('tr');
    const fd = new FormData();
    const inputs = row.querySelectorAll('input, select');
    
    // Validasiya
    let isValid = true;
    inputs.forEach(input => {
        if (input.hasAttribute('required') && !input.value) isValid = false;
        fd.append(input.name, input.value);
    });
    
    if(!isValid) {
        alert('Zəhmət olmasa vacib xanaları doldurun');
        return;
    }
    
    fetch('api_create.php', {
        method: 'POST',
        body: fd
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            location.reload();
        } else {
            alert(data.message);
        }
    });
}

function cancelRow(btn) {
    btn.closest('tr').remove();
    if (document.querySelectorAll('#tableBody tr').length === 0) {
        location.reload();
    }
    calculateSummary();
}

function deleteRecord(id) {
    if(!confirm('Silmək istədiyinizə əminsiniz?')) return;
    const fd = new FormData();
    fd.append('id', id);
    fetch('api_delete.php', {
        method: 'POST',
        body: fd
    }).then(() => location.reload());
}

// Excel/CSV ixracı
function exportToExcel() {
    const table = document.getElementById('weeklyWorksTable');
    let csv = [];
    const BOM = '\uFEFF';
    
    // Başlıqları yenilə (16 sütun)
    const headers = [];
    table.querySelectorAll('thead th').forEach((th, i) => {
        if(i < 15) headers.push(`"${th.innerText.trim()}"`);
    });
    csv.push(headers.join(','));
    
    // Məlumatları yenilə
    table.querySelectorAll('tbody tr:not(.new-row)').forEach(tr => {
        const row = [];
        tr.querySelectorAll('td').forEach((td, i) => {
            if (i < 15) {
                if (i === 14) {
                    // Qiymətləndirmə sütunu
                    const badge = td.querySelector('.badge');
                    row.push(`"${badge ? badge.textContent.trim() : '—'}"`);
                } else {
                    row.push(`"${td.textContent.trim()}"`);
                }
            }
        });
        if(row.length > 0) csv.push(row.join(','));
    });
    
    const csvContent = BOM + csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.setAttribute("href", url);
    link.setAttribute("download", `hesabat_${new Date().toISOString().slice(0,10)}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

document.addEventListener('DOMContentLoaded', calculateSummary);
</script>

<?php include '../../includes/footer.php'; ?>