<?php
require_once 'admin_auth.php';
requireAdminLogin();

$admin = getCurrentAdmin();
$conn = getAdminDbConnection();

$message = '';
$message_type = '';

// Handle different actions
$action = $_GET['action'] ?? '';

// Handle user deletion
if ($_POST['action'] ?? '' === 'delete_user' && isset($_POST['user_id'])) {
    $user_id = (int)$_POST['user_id'];
    
    // Get user info for logging
    $userInfo = $conn->query("SELECT username FROM users WHERE id = $user_id")->fetch_assoc();
    
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        $message = "User deleted successfully!";
        $message_type = "success";
        logAdminActivity('user_delete', "Deleted user: " . $userInfo['username']);
    } else {
        $message = "Error deleting user. They may have active orders/appointments.";
        $message_type = "danger";
    }
}

// Get users with pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';

$whereClause = "WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $whereClause .= " AND (username LIKE ? OR email LIKE ? OR full_name LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
    $types .= "sss";
}

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM users $whereClause";
$countStmt = $conn->prepare($countQuery);
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$totalUsers = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalUsers / $limit);

// Get users
$userQuery = "
    SELECT u.*, 
           (SELECT COUNT(*) FROM booking WHERE user_id = u.id) as total_appointments,
           (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as total_orders,
           (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE user_id = u.id AND status != 'cancelled') as total_spent
    FROM users u 
    $whereClause 
    ORDER BY u.created_at DESC 
    LIMIT $limit OFFSET $offset
";

$userStmt = $conn->prepare($userQuery);
if (!empty($params)) {
    $userStmt->bind_param($types, ...$params);
}
$userStmt->execute();
$users = $userStmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - VEDAMRUT Admin</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Cormorant+Garamond:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Admin Shared Styles -->
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header" data-aos="fade-down">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-1">User Management</h3>
                    <p class="mb-0 text-muted">Manage all registered users and their activities</p>
                </div>
                <div class="text-muted">
                    <i class="fas fa-users me-2"></i><?php echo number_format($totalUsers); ?> Total Users
                </div>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Search and Filters -->
        <div class="search-filters" data-aos="fade-up" data-aos-delay="100">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" name="search" 
                               placeholder="Search users..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-filter"></i> Search
                    </button>
                </div>
                <div class="col-md-3">
                    <a href="users.php" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-refresh"></i> Reset
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Users Table -->
        <div class="card" data-aos="fade-up" data-aos-delay="200">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-users me-2"></i> Users List
                    <span class="badge status-active ms-2"><?php echo number_format($totalUsers); ?> total</span>
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Contact</th>
                                <th>Activity</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($users->num_rows > 0): ?>
                                <?php while($user = $users->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar">
                                                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                            </div>
                                            <div class="ms-3">
                                                <div class="fw-bold"><?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?></div>
                                                <small class="text-muted">@<?php echo htmlspecialchars($user['username']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div><?php echo htmlspecialchars($user['email']); ?></div>
                                        <?php if ($user['phone']): ?>
                                        <small class="text-muted"><?php echo htmlspecialchars($user['phone']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar-check"></i> <?php echo $user['total_appointments']; ?> appointments<br>
                                            <i class="fas fa-shopping-cart"></i> <?php echo $user['total_orders']; ?> orders<br>
                                            <i class="fas fa-rupee-sign"></i> <?php echo formatCurrency($user['total_spent']); ?> spent
                                        </small>
                                    </td>
                                    <td>
                                        <small><?php echo formatDate($user['created_at']); ?></small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#viewUserModal"
                                                    onclick="viewUser(<?php echo htmlspecialchars(json_encode($user)); ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger"
                                                    onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No users found</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="card-footer">
                <nav>
                    <ul class="pagination justify-content-center mb-0">
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>">
                                Previous
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page-2); $i <= min($totalPages, $page+2); $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>">
                                Next
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- View User Modal -->
    <div class="modal fade" id="viewUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">User Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="userDetailsContent">
                    <!-- User details will be populated here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete user <strong id="deleteUserName"></strong>?</p>
                    <p class="text-danger"><i class="fas fa-warning"></i> This action cannot be undone!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete_user">
                        <input type="hidden" name="user_id" id="deleteUserId">
                        <button type="submit" class="btn btn-danger">Delete User</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-danger" id="logoutModalLabel">
                        <i class="fas fa-exclamation-triangle"></i> Confirm Logout
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="mb-3">
                        <i class="fas fa-sign-out-alt fa-3x text-warning"></i>
                    </div>
                    <h6 class="mb-3">Are you sure you want to logout?</h6>
                    <p class="text-muted mb-0">You will be redirected to the login page.</p>
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

    <!-- AOS Animation Library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 600,
            easing: 'ease-in-out',
            once: true
        });
        
        function viewUser(user) {
            const content = `
                <div class="row">
                    <div class="col-md-4 text-center">
                        <div class="user-avatar mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2rem;">
                            ${user.username.charAt(0).toUpperCase()}
                        </div>
                        <h6>${user.full_name || user.username}</h6>
                        <small class="text-muted">@${user.username}</small>
                    </div>
                    <div class="col-md-8">
                        <table class="table table-borderless">
                            <tr><td><strong>Email:</strong></td><td>${user.email}</td></tr>
                            <tr><td><strong>Phone:</strong></td><td>${user.phone || 'Not provided'}</td></tr>
                            <tr><td><strong>Address:</strong></td><td>${user.address || 'Not provided'}</td></tr>
                            <tr><td><strong>Total Appointments:</strong></td><td>${user.total_appointments}</td></tr>
                            <tr><td><strong>Total Orders:</strong></td><td>${user.total_orders}</td></tr>
                            <tr><td><strong>Total Spent:</strong></td><td>₹${parseFloat(user.total_spent).toLocaleString()}</td></tr>
                            <tr><td><strong>Joined:</strong></td><td>${new Date(user.created_at).toLocaleDateString()}</td></tr>
                        </table>
                    </div>
                </div>
            `;
            document.getElementById('userDetailsContent').innerHTML = content;
        }
        
        function deleteUser(userId, username) {
            document.getElementById('deleteUserId').value = userId;
            document.getElementById('deleteUserName').textContent = username;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
        
        // Logout functionality
        function confirmLogout() {
            const logoutModal = new bootstrap.Modal(document.getElementById('logoutModal'));
            logoutModal.show();
        }
        
        function performLogout() {
            const logoutBtn = document.querySelector('#logoutModal .btn-danger');
            logoutBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging out...';
            logoutBtn.disabled = true;
            
            setTimeout(() => {
                window.location.href = '../logout.php';
            }, 1000);
        }
    </script>
</body>
</html>

<?php $conn->close(); ?>