/**
 * ========================================
 * MAIN.JS - JavaScript Pendukung Aplikasi
 * ========================================
 */

document.addEventListener('DOMContentLoaded', function() {

    // Smooth scrolling untuk anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });

    // Form validation real-time
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Konversi Drom ke Liter otomatis
    const jumlahDromInput = document.getElementById('jumlah_drom');
    const jumlahLiterInput = document.getElementById('jumlah_liter');
    
    if (jumlahDromInput && jumlahLiterInput) {
        jumlahDromInput.addEventListener('input', function() {
            const drom = parseFloat(this.value) || 0;
            jumlahLiterInput.value = (drom * 200).toFixed(2);
        });
    }

    // Toggle password visibility
    const togglePassword = document.querySelectorAll('.toggle-password');
    togglePassword.forEach(btn => {
        btn.addEventListener('click', function() {
            const target = document.querySelector(this.getAttribute('data-target'));
            if (target.type === 'password') {
                target.type = 'text';
                this.innerHTML = '<i class="fas fa-eye-slash"></i>';
            } else {
                target.type = 'password';
                this.innerHTML = '<i class="fas fa-eye"></i>';
            }
        });
    });

    // Auto-format number (uang)
    const currencyInputs = document.querySelectorAll('.currency-input');
    currencyInputs.forEach(input => {
        input.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            value = new Intl.NumberFormat('id-ID').format(value);
            this.value = 'Rp ' + value;
        });
    });

    // Toast notification (simple)
    window.showToast = function(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `alert alert-${type} alert-dismissible fade show position-fixed bottom-0 end-0 m-3`;
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(toast);
            bsAlert.close();
        }, 5000);
    };

    // Confirm delete
    window.confirmDelete = function(callback) {
        if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
            callback();
        }
    };

    // Back to top button
    const backToTopButton = document.createElement('button');
    backToTopButton.innerHTML = '<i class="fas fa-arrow-up"></i>';
    backToTopButton.className = 'fab';
    backToTopButton.style.display = 'none';
    document.body.appendChild(backToTopButton);

    window.addEventListener('scroll', function() {
        if (window.scrollY > 300) {
            backToTopButton.style.display = 'flex';
        } else {
            backToTopButton.style.display = 'none';
        }
    });

    backToTopButton.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // Date picker initialization (if needed)
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.showPicker();
        });
    });

    console.log('✅ Aplikasi Minyak Tanah: JavaScript loaded successfully!');
});

/**
 * Fungsi utilitas tambahan
 */
function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(angka);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('Teks berhasil disalin!', 'success');
    }).catch(err => {
        showToast('Gagal menyalin teks', 'danger');
    });
}

// Export functions for global use
window.formatRupiah = formatRupiah;
window.formatDate = formatDate;
window.copyToClipboard = copyToClipboard;
