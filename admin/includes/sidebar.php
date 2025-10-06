<?php
// Get current page for navigation highlighting
$currentPage = basename($_SERVER['PHP_SELF']);
$admin = getCurrentAdmin();
?>

<!-- Sidebar -->
<nav class="sidebar d-flex flex-column">
    <div class="sidebar-header">
        <h4><i class="fas fa-leaf"></i> VEDAMRUT</h4>
        <small>Admin Dashboard</small>
    </div>
    
    <div class="sidebar-nav">
        <a href="index.php" class="nav-link <?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">
            <i class="fas fa-dashboard"></i> Dashboard
        </a>
        <a href="users.php" class="nav-link <?php echo $currentPage === 'users.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i> Users
        </a>
        <a href="appointments.php" class="nav-link <?php echo $currentPage === 'appointments.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-check"></i> Appointments
        </a>
        <a href="orders.php" class="nav-link <?php echo $currentPage === 'orders.php' ? 'active' : ''; ?>">
            <i class="fas fa-shopping-cart"></i> Orders
        </a>
        <a href="products.php" class="nav-link <?php echo $currentPage === 'products.php' ? 'active' : ''; ?>">
            <i class="fas fa-box"></i> Products
        </a>
        <a href="settings.php" class="nav-link <?php echo $currentPage === 'settings.php' ? 'active' : ''; ?>">
            <i class="fas fa-cog"></i> Settings
        </a>
    </div>
    
    <div class="mt-auto logout-section">
        <!-- Admin Profile Info -->
        <div class="admin-profile">
            <div class="admin-avatar">
                <?php echo strtoupper(substr($admin['full_name'], 0, 1)); ?>
            </div>
            <div>
                <div class="fw-bold" style="font-size: 0.9rem;"><?php echo htmlspecialchars($admin['full_name']); ?></div>
                <div class="session-info">
                    <i class="fas fa-user-shield"></i> <?php echo ucfirst($admin['role']); ?><br>
                    <i class="fas fa-clock"></i> <?php echo date('g:i A'); ?>
                </div>
            </div>
        </div>
        
        <!-- Enhanced Logout Button -->
        <button type="button" class="logout-btn" onclick="confirmLogout()">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout Safely</span>
        </button>
    </div>
</nav>

<!-- Logout Confirmation Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title text-danger" id="logoutModalLabel">
                    <i class="fas fa-exclamation-triangle"></i> Confirm Logout
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <i class="fas fa-sign-out-alt fa-3x text-warning"></i>
                </div>
                <h6 class="mb-3">Are you sure you want to logout?</h6>
                <p class="text-muted mb-0">You will be redirected to the login page and your current session will be terminated.</p>
                <div class="mt-3 p-3 bg-light rounded">
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i> 
                        Session started: <strong><?php echo date('M j, Y g:i A'); ?></strong><br>
                        Logged in as: <strong><?php echo htmlspecialchars($admin['full_name']); ?></strong>
                    </small>
                </div>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" class="btn btn-danger" onclick="performLogout()">
                    <i class="fas fa-sign-out-alt"></i> Yes, Logout
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Enhanced Logout Functionality
function confirmLogout() {
    // Show the logout modal
    const logoutModal = new bootstrap.Modal(document.getElementById('logoutModal'));
    logoutModal.show();
}

function performLogout() {
    // Show loading state
    const logoutBtn = document.querySelector('#logoutModal .btn-danger');
    const originalText = logoutBtn.innerHTML;
    
    logoutBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging out...';
    logoutBtn.disabled = true;
    
    // Add a small delay for better UX
    setTimeout(() => {
        // Redirect to logout
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
        alert('Your session is about to expire. Please save your work.');
        // Auto-logout after another 5 minutes of inactivity
        setTimeout(() => {
            window.location.href = '../logout.php?reason=timeout';
        }, 5 * 60 * 1000);
    }, SESSION_TIMEOUT);
}

// Reset timeout on user activity
['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(event => {
    document.addEventListener(event, resetSessionTimeout, true);
});

// Initialize session timeout
resetSessionTimeout();
</script>