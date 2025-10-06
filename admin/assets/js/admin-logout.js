/**
 * Enhanced Logout Functionality for VedaLife Admin Panel
 * Provides confirmation dialog and improved UX for admin logout
 */

// Logout functionality
function confirmLogout() {
    const logoutModal = new bootstrap.Modal(document.getElementById('logoutModal'));
    logoutModal.show();
}

function performLogout() {
    const logoutBtn = document.querySelector('#logoutModal .btn-danger');
    const originalText = logoutBtn.innerHTML;
    
    logoutBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging out...';
    logoutBtn.disabled = true;
    
    // Add a small delay for better UX
    setTimeout(() => {
        window.location.href = '../logout.php';
    }, 1000);
}

// Keyboard shortcut for logout (Ctrl/Cmd + Shift + L)
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'L') {
        e.preventDefault();
        confirmLogout();
    }
});

// Session timeout warning (optional)
let sessionTimeout;
const SESSION_TIMEOUT = 30 * 60 * 1000; // 30 minutes

function resetSessionTimeout() {
    clearTimeout(sessionTimeout);
    sessionTimeout = setTimeout(() => {
        if (confirm('Your session is about to expire. Click OK to stay logged in, or Cancel to logout.')) {
            resetSessionTimeout(); // Reset if user wants to stay
        } else {
            window.location.href = '../logout.php?reason=timeout';
        }
    }, SESSION_TIMEOUT);
}

// Reset timeout on user activity
['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'].forEach(event => {
    document.addEventListener(event, resetSessionTimeout, true);
});

// Initialize session timeout when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    resetSessionTimeout();
    
    // Add tooltip to logout button if it exists
    const logoutBtn = document.querySelector('.logout-btn');
    if (logoutBtn) {
        logoutBtn.setAttribute('title', 'Logout safely (Ctrl+Shift+L)');
        
        // Initialize Bootstrap tooltip if available
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            new bootstrap.Tooltip(logoutBtn);
        }
    }
});

// Enhanced logout with save reminder
function logoutWithSaveReminder() {
    const hasUnsavedChanges = document.querySelector('form[data-unsaved="true"]') !== null;
    
    if (hasUnsavedChanges) {
        if (confirm('You have unsaved changes. Are you sure you want to logout?')) {
            confirmLogout();
        }
    } else {
        confirmLogout();
    }
}

// Detect unsaved form changes
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input, select, textarea');
        
        inputs.forEach(input => {
            input.addEventListener('change', function() {
                form.setAttribute('data-unsaved', 'true');
            });
        });
        
        form.addEventListener('submit', function() {
            form.removeAttribute('data-unsaved');
        });
    });
});

// Prevent accidental page refresh when there are unsaved changes
window.addEventListener('beforeunload', function(e) {
    const hasUnsavedChanges = document.querySelector('form[data-unsaved="true"]') !== null;
    
    if (hasUnsavedChanges) {
        e.preventDefault();
        e.returnValue = '';
        return '';
    }
});