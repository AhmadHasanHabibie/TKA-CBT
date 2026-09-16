import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Global CBT Alert & Confirmation Helpers
window.showConfirm = function(options) {
    window.dispatchEvent(new CustomEvent('confirm', {
        detail: {
            title: options.title || 'Konfirmasi Tindakan',
            message: options.message || 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
            type: options.type || 'danger',
            confirmText: options.confirmText || 'Ya, Lanjutkan',
            cancelText: options.cancelText || 'Batal',
            action: options.action || null
        }
    }));
};

window.showToast = function(message, type = 'success', duration = 5000) {
    window.dispatchEvent(new CustomEvent('toast', {
        detail: { message, type, duration }
    }));
};

Alpine.start();
