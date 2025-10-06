<?php
require_once 'admin_auth.php';
requireAdminLogin();

$admin = getCurrentAdmin();
$conn = getAdminDbConnection();

$message = '';
$message_type = '';

// Handle appointment status update
if ($_POST['action'] ?? '' === 'update_status' && isset($_POST['booking_id'], $_POST['status'])) {
    $booking_id = (int)$_POST['booking_id'];
    $status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE booking SET status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("si", $status, $booking_id);
    
    if ($stmt->execute()) {
        $message = "Appointment status updated successfully!";
        $message_type = "success";
        logAdminActivity('appointment_status_update', "Updated appointment ID: $booking_id status to: $status");
    } else {
        $message = "Error updating appointment status.";
        $message_type = "danger";
    }
}

// Handle appointment deletion
if ($_POST['action'] ?? '' === 'delete_appointment' && isset($_POST['booking_id'])) {
    $booking_id = (int)$_POST['booking_id'];
    
    // Get appointment info for logging
    $apptInfo = $conn->query("SELECT service, name FROM booking WHERE id = $booking_id")->fetch_assoc();
    
    $stmt = $conn->prepare("DELETE FROM booking WHERE id = ?");
    $stmt->bind_param("i", $booking_id);
    
    if ($stmt->execute()) {
        $message = "Appointment deleted successfully!";
        $message_type = "success";
        logAdminActivity('appointment_delete', "Deleted appointment: " . $apptInfo['service'] . " for " . $apptInfo['name']);
    } else {
        $message = "Error deleting appointment.";
        $message_type = "danger";
    }
}

// Handle appointment notes update
if ($_POST['action'] ?? '' === 'update_notes' && isset($_POST['booking_id'], $_POST['notes'])) {
    $booking_id = (int)$_POST['booking_id'];
    $notes = trim($_POST['notes']);
    
    $stmt = $conn->prepare("UPDATE booking SET notes = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("si", $notes, $booking_id);
    
    if ($stmt->execute()) {
        $message = "Appointment notes updated successfully!";
        $message_type = "success";
        logAdminActivity('appointment_notes_update', "Updated notes for appointment ID: $booking_id");
    } else {
        $message = "Error updating appointment notes.";
        $message_type = "danger";
    }
}

// Get appointments with pagination and filters
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$date_filter = $_GET['date'] ?? '';
$service_filter = $_GET['service'] ?? '';

$whereClause = "WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $whereClause .= " AND (b.name LIKE ? OR b.email LIKE ? OR b.service LIKE ? OR u.username LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $types .= "ssss";
}

if (!empty($status_filter)) {
    $whereClause .= " AND b.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if (!empty($date_filter)) {
    $whereClause .= " AND DATE(b.preferred_date) = ?";
    $params[] = $date_filter;
    $types .= "s";
}

if (!empty($service_filter)) {
    $whereClause .= " AND b.service = ?";
    $params[] = $service_filter;
    $types .= "s";
}

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM booking b JOIN users u ON b.user_id = u.id $whereClause";
$countStmt = $conn->prepare($countQuery);
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$totalAppointments = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalAppointments / $limit);

// Get appointments
$appointmentQuery = "
    SELECT b.*, u.username, u.email as user_email
    FROM booking b 
    JOIN users u ON b.user_id = u.id 
    $whereClause 
    ORDER BY b.preferred_date DESC, b.created_at DESC
    LIMIT $limit OFFSET $offset
";

$appointmentStmt = $conn->prepare($appointmentQuery);
if (!empty($params)) {
    $appointmentStmt->bind_param($types, ...$params);
}
$appointmentStmt->execute();
$appointments = $appointmentStmt->get_result();

// Get unique services for filter
$services = $conn->query("SELECT DISTINCT service FROM booking ORDER BY service");
$servicesList = [];
while ($service = $services->fetch_assoc()) {
    $servicesList[] = $service['service'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Management - VEDAMRUT Admin</title>
    
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
                    <h3 class="mb-1">Appointment Management</h3>
                    <p class="mb-0 text-muted">View and manage all customer appointments</p>
                </div>
                <div class="text-muted">
                    <i class="fas fa-calendar-alt me-2"></i><?php echo number_format($totalAppointments); ?> Total Appointments
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
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" name="search" 
                               placeholder="Search appointments..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="service" class="form-select">
                        <option value="">All Services</option>
                        <?php foreach ($servicesList as $service): ?>
                        <option value="<?php echo htmlspecialchars($service); ?>" <?php echo $service_filter === $service ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($service); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="date" value="<?php echo htmlspecialchars($date_filter); ?>">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="appointments.php" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Appointments Table -->
        <div class="card" data-aos="fade-up" data-aos-delay="200">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-calendar-alt me-2"></i> Appointments List
                    <span class="badge status-active ms-2"><?php echo number_format($totalAppointments); ?> total</span>
                </h6>
            </div>
            <div class="card-body p-0">
                <?php if ($appointments->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Customer</th>
                                    <th>Service</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($appointment = $appointments->fetch_assoc()): ?>
                                <tr class="appointment-row">
                                    <td><strong>#<?php echo $appointment['id']; ?></strong></td>
                                    <td>
                                        <div>
                                            <strong><?php echo htmlspecialchars($appointment['name']); ?></strong>
                                            <small class="text-muted d-block">@<?php echo htmlspecialchars($appointment['username']); ?></small>
                                            <small class="text-muted d-block"><?php echo htmlspecialchars($appointment['email']); ?></small>
                                            <small class="text-muted d-block"><?php echo htmlspecialchars($appointment['phone']); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($appointment['service']); ?></strong>
                                        <?php if (!empty($appointment['notes'])): ?>
                                        <small class="text-muted d-block">
                                            <i class="fas fa-sticky-note"></i> <?php echo htmlspecialchars(substr($appointment['notes'], 0, 50)); ?>...
                                        </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?php echo date('M d, Y', strtotime($appointment['preferred_date'])); ?></strong>
                                            <small class="text-muted d-block">
                                                Booked: <?php echo date('M d, Y g:i A', strtotime($appointment['created_at'])); ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline" onchange="this.submit()">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="booking_id" value="<?php echo $appointment['id']; ?>">
                                            <select name="status" class="form-select form-select-sm status-select <?php echo getStatusBadgeClass($appointment['status']); ?>">
                                                <option value="pending" <?php echo $appointment['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="confirmed" <?php echo $appointment['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                <option value="completed" <?php echo $appointment['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                <option value="cancelled" <?php echo $appointment['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#viewModal"
                                                    onclick="viewAppointment(<?php echo htmlspecialchars(json_encode($appointment)); ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-success"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#notesModal"
                                                    onclick="editNotes(<?php echo $appointment['id']; ?>, '<?php echo htmlspecialchars($appointment['notes']); ?>')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger"
                                                    onclick="deleteAppointment(<?php echo $appointment['id']; ?>, '<?php echo htmlspecialchars($appointment['name']); ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No appointments found</p>
                        <small class="text-muted">Appointments will appear here once customers start booking</small>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="card-footer">
                <nav>
                    <ul class="pagination justify-content-center mb-0">
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&date=<?php echo urlencode($date_filter); ?>&service=<?php echo urlencode($service_filter); ?>">
                                Previous
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page-2); $i <= min($totalPages, $page+2); $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&date=<?php echo urlencode($date_filter); ?>&service=<?php echo urlencode($service_filter); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&date=<?php echo urlencode($date_filter); ?>&service=<?php echo urlencode($service_filter); ?>">
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

    <!-- View Appointment Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Appointment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="appointmentDetails">
                    <!-- Details will be populated here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Notes Modal -->
    <div class="modal fade" id="notesModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Appointment Notes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_notes">
                        <input type="hidden" name="booking_id" id="notesBookingId">
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" id="notesText" rows="4" 
                                      placeholder="Add any notes or special instructions..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Save Notes</button>
                    </div>
                </form>
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
                    <p>Are you sure you want to delete the appointment for <strong id="deleteCustomerName"></strong>?</p>
                    <p class="text-danger"><i class="fas fa-warning"></i> This action cannot be undone!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete_appointment">
                        <input type="hidden" name="booking_id" id="deleteBookingId">
                        <button type="submit" class="btn btn-danger">Delete Appointment</button>
                    </form>
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
        function viewAppointment(appointment) {
            const content = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Customer Information</h6>
                        <table class="table table-borderless">
                            <tr><td><strong>Name:</strong></td><td>${appointment.name}</td></tr>
                            <tr><td><strong>Username:</strong></td><td>@${appointment.username}</td></tr>
                            <tr><td><strong>Email:</strong></td><td>${appointment.email}</td></tr>
                            <tr><td><strong>Phone:</strong></td><td>${appointment.phone}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Appointment Information</h6>
                        <table class="table table-borderless">
                            <tr><td><strong>Service:</strong></td><td>${appointment.service}</td></tr>
                            <tr><td><strong>Preferred Date:</strong></td><td>${new Date(appointment.preferred_date).toLocaleDateString()}</td></tr>
                            <tr><td><strong>Status:</strong></td><td>
                                <span class="badge bg-${getStatusColor(appointment.status)}">${appointment.status.charAt(0).toUpperCase() + appointment.status.slice(1)}</span>
                            </td></tr>
                            <tr><td><strong>Booked:</strong></td><td>${new Date(appointment.created_at).toLocaleString()}</td></tr>
                        </table>
                    </div>
                </div>
                ${appointment.notes ? `
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Notes</h6>
                        <div class="alert alert-info">
                            <i class="fas fa-sticky-note"></i> ${appointment.notes}
                        </div>
                    </div>
                </div>
                ` : ''}
            `;
            document.getElementById('appointmentDetails').innerHTML = content;
        }
        
        function getStatusColor(status) {
            switch(status) {
                case 'pending': return 'warning';
                case 'confirmed': return 'info';
                case 'completed': return 'success';
                case 'cancelled': return 'danger';
                default: return 'secondary';
            }
        }
        
        function editNotes(bookingId, currentNotes) {
            document.getElementById('notesBookingId').value = bookingId;
            document.getElementById('notesText').value = currentNotes;
        }
        
        function deleteAppointment(bookingId, customerName) {
            document.getElementById('deleteBookingId').value = bookingId;
            document.getElementById('deleteCustomerName').textContent = customerName;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
        
        function refreshAppointments() {
            window.location.reload();
        }
        
        function exportAppointments() {
            // You can implement CSV export here
            alert('Export functionality will be implemented soon!');
        }
        
        // Auto-refresh every 30 seconds for real-time updates
        setInterval(() => {
            // Uncomment the next line if you want auto-refresh
            // refreshAppointments();
        }, 30000);
    </script>
</body>
</html>

<?php $conn->close(); ?>