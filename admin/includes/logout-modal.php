<!-- Enhanced Logout Confirmation Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 bg-gradient" style="background: linear-gradient(45deg, #dc3545, #c82333);">
                <h5 class="modal-title text-white" id="logoutModalLabel">
                    <i class="fas fa-exclamation-triangle"></i> Confirm Logout
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <i class="fas fa-sign-out-alt fa-4x text-warning"></i>
                </div>
                <h5 class="mb-3">Are you sure you want to logout?</h5>
                <p class="text-muted mb-4">You will be securely logged out and redirected to the login page.</p>
                
                <?php if (isset($admin) && !empty($admin)): ?>
                <div class="alert alert-light border">
                    <div class="row align-items-center">
                        <div class="col-3">
                            <div class="logout-modal-avatar">
                                <?php echo strtoupper(substr($admin['full_name'], 0, 1)); ?>
                            </div>
                        </div>
                        <div class="col-9 text-start">
                            <small class="text-muted d-block">
                                <i class="fas fa-user"></i> <strong><?php echo htmlspecialchars($admin['full_name']); ?></strong><br>
                                <i class="fas fa-user-shield"></i> <?php echo ucfirst($admin['role'] ?? 'Admin'); ?><br>
                                <i class="fas fa-clock"></i> Session: <?php echo date('M j, Y g:i A'); ?>
                            </small>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="d-flex justify-content-center mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="logoutRememberChoice">
                        <label class="form-check-label small text-muted" for="logoutRememberChoice">
                            Remember my choice for this session
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Stay Logged In
                </button>
                <button type="button" class="btn btn-danger" onclick="performLogout()">
                    <i class="fas fa-sign-out-alt"></i> Yes, Logout Now
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.logout-modal-avatar {
    width: 50px;
    height: 50px;
    background: linear-gradient(45deg, #28a745, #20c997);
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 1.2rem;
}

.modal-header.bg-gradient {
    background: linear-gradient(45deg, #dc3545, #c82333) !important;
}

#logoutModal .modal-content {
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    border: none;
    border-radius: 15px;
}

#logoutModal .btn-danger:hover {
    background: linear-gradient(45deg, #c82333, #a71e2a);
    border-color: #a71e2a;
}
</style>