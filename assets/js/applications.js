// Müraciətlər üçün JavaScript funksiyaları

// Tarixi formatlama
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('az-AZ', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

// Fayl ölçüsünü formatlama
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Export funksiyası (Excel, PDF)
function exportData(format) {
    const table = document.querySelector('table');
    const rows = table.querySelectorAll('tbody tr');
    let data = [];
    
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length > 0) {
            let rowData = [];
            cells.forEach((cell, index) => {
                if (index < cells.length - 1) { // Son sütunu (əməliyyatları) çıxar
                    rowData.push(cell.textContent.trim());
                }
            });
            data.push(rowData);
        }
    });
    
    if (format === 'excel') {
        exportToExcel(data);
    } else if (format === 'pdf') {
        exportToPDF(data);
    }
}

function exportToExcel(data) {
    alert('Excel export funksiyası aktiv ediləcək');
    // Burada Excel export funksiyası əlavə edilə bilər
}

function exportToPDF(data) {
    alert('PDF export funksiyası aktiv ediləcək');
    // Burada PDF export funksiyası əlavə edilə bilər
}

// Search-in real-time işləməsi
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                this.form.submit();
            }
        });
    }
    
    // Fayl seçildikdə ön izləmə
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            const fileList = document.createElement('div');
            fileList.className = 'mt-2';
            
            if (this.files.length > 0) {
                for (let i = 0; i < this.files.length; i++) {
                    const file = this.files[i];
                    const fileInfo = document.createElement('div');
                    fileInfo.className = 'alert alert-info py-1 mb-1';
                    fileInfo.innerHTML = `
                        <i class="fas fa-file me-2"></i>
                        ${file.name} (${formatFileSize(file.size)})
                    `;
                    fileList.appendChild(fileInfo);
                }
            }
            
            // Köhnə siyahını sil və yenisini əlavə et
            const oldList = this.parentNode.querySelector('.file-list-preview');
            if (oldList) oldList.remove();
            
            if (this.files.length > 0) {
                fileList.className += ' file-list-preview';
                this.parentNode.appendChild(fileList);
            }
        });
    });
});