<?php
// Kết nối tới CSDL
require_once 'db_connect.php';

// Lấy thống kê nhân viên
$statsSql = "SELECT 
    COUNT(*) as total_employees,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_employees,
    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_employees,
    COUNT(DISTINCT position) as total_positions
    FROM employees";
$statsResult = mysqli_query($conn, $statsSql);
$stats = mysqli_fetch_assoc($statsResult);

// Xác định bộ lọc
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$positionFilter = isset($_GET['position']) ? $_GET['position'] : 'all';

// Xây dựng câu truy vấn với điều kiện lọc
$sql = "SELECT * FROM employees WHERE 1=1";

// Thêm điều kiện tìm kiếm
if (!empty($searchTerm)) {
    $searchTerm = mysqli_real_escape_string($conn, $searchTerm);
    $sql .= " AND (full_name LIKE '%$searchTerm%' 
               OR phone LIKE '%$searchTerm%' 
               OR email LIKE '%$searchTerm%')";
}

// Thêm điều kiện trạng thái
if ($statusFilter != 'all') { 
    $sql .= " AND status = '$statusFilter'";
}

// Thêm điều kiện vị trí
if ($positionFilter != 'all') {
    $sql .= " AND position = '$positionFilter'";
}

$sql .= " ORDER BY id DESC";

// Đếm tổng số nhân viên để phân trang
$totalEmployeesQuery = preg_replace('/SELECT \* FROM employees/i', 'SELECT COUNT(*) as total FROM employees', $sql);
$totalEmployeesResult = mysqli_query($conn, $totalEmployeesQuery);
$totalEmployees = mysqli_fetch_assoc($totalEmployeesResult)['total'];

// Thiết lập phân trang
$employeesPerPage = 10;
$totalPages = ceil($totalEmployees / $employeesPerPage);
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $employeesPerPage;

// Truy vấn với limit và offset
$sql .= " LIMIT $offset, $employeesPerPage";
$result = mysqli_query($conn, $sql);

// Lấy danh sách vị trí để hiển thị trong dropdown
$positionsSql = "SELECT DISTINCT position FROM employees ORDER BY position";
$positionsResult = mysqli_query($conn, $positionsSql);
$positions = [];
while ($row = mysqli_fetch_assoc($positionsResult)) {
    $positions[] = $row['position'];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Nhân viên</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php include 'menu.php'; ?>
        
<!-- Main Content -->
<main class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-user-tie"></i> Quản lý Nhân viên</h1>
        <button class="btn-primary" onclick="window.location.href='add_employee.php'">
            <i class="fas fa-plus"></i> Thêm nhân viên mới
        </button>
    </div>
    
    <!-- Employee Stats-->
    <div class="employee-stats">
        <div class="stat-card">
            <div class="stat-icon total">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <h3>Tổng nhân viên</h3>
                <div class="stat-value"><?php echo $stats['total_employees']; ?></div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon active">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="stat-info">
                <h3>Đang làm việc</h3>
                <div class="stat-value"><?php echo $stats['active_employees']; ?></div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon inactive">
                <i class="fas fa-user-times"></i>
            </div>
            <div class="stat-info">
                <h3>Đã nghỉ việc</h3>
                <div class="stat-value"><?php echo $stats['inactive_employees']; ?></div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon positions">
                <i class="fas fa-sitemap"></i>
            </div>
            <div class="stat-info">
                <h3>Vị trí</h3>
                <div class="stat-value"><?php echo $stats['total_positions']; ?></div>
            </div>
        </div>
    </div>
    
    <!-- Employee Filter -->
    <div class="employee-filter-container">
        <div class="employee-search-box">
            <form method="GET" action="">
                <button type="submit"><i class="fas fa-search"></i></button>
                <input type="text" name="search" placeholder="Tìm kiếm nhân viên..." value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
            </form>
        </div>
        
        <div class="employee-filter-options">
            <form method="GET" action="" id="filterForm">
                <input type="hidden" name="search" value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
                
                <select name="status" onchange="document.getElementById('filterForm').submit()">
                    <option value="all" <?php if($statusFilter == 'all') echo 'selected'; ?>>Tất cả trạng thái</option>
                    <option value="active" <?php if($statusFilter == 'active') echo 'selected'; ?>>Đang làm việc</option>
                    <option value="inactive" <?php if($statusFilter == 'inactive') echo 'selected'; ?>>Đã nghỉ việc</option>
                </select>
                
                <select name="position" onchange="document.getElementById('filterForm').submit()">
                    <option value="all" <?php if($positionFilter == 'all') echo 'selected'; ?>>Tất cả vị trí</option>
                    <?php foreach($positions as $position): ?>
                    <option value="<?php echo $position; ?>" <?php if($positionFilter == $position) echo 'selected'; ?>><?php echo $position; ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>
    
    <!-- Employees Table  -->
    <div class="employee-table-container">
        <table class="employee-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Họ và tên</th>
                    <th>Số điện thoại</th>
                    <th>Vị trí</th>
                    <th>Ngày bắt đầu</th>
                    <th>Lương cơ bản</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if (mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_assoc($result)) {
                        $statusText = $row['status'] == 'active' ? 'Đang làm việc' : 'Đã nghỉ việc';
                        $statusClass = $row['status'];
                ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['full_name']; ?></td>
                    <td><?php echo $row['phone']; ?></td>
                    <td><?php echo $row['position']; ?></td>
                    <td><?php echo date('d/m/Y', strtotime($row['hire_date'])); ?></td>
                    <td><?php echo number_format($row['basic_salary'], 0, ',', '.') . ' VNĐ'; ?></td>
                    <td><span class="employee-status <?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
                    <td class="actions">
                        <button class="btn-view" title="Xem chi tiết" onclick="viewEmployeeDetails(<?php echo $row['id']; ?>)"><i class="fas fa-eye"></i></button>
                        <button class="btn-edit" title="Chỉnh sửa" onclick="window.location.href='edit_employee.php?id=<?php echo $row['id']; ?>'"><i class="fas fa-edit"></i></button>
                        <button class="btn-delete" title="Xóa" onclick="deleteEmployee(<?php echo $row['id']; ?>)"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                <?php 
                    }
                } else {
                    echo "<tr><td colspan='8' class='text-center'>Không có nhân viên nào</td></tr>";
                }
                ?>
            </tbody>
        </table>
        
        <div class="pagination">
            <button <?php if($currentPage <= 1) echo 'disabled'; ?> onclick="window.location.href='?page=<?php echo $currentPage-1; ?>&status=<?php echo $statusFilter; ?>&position=<?php echo $positionFilter; ?>&search=<?php echo urlencode($searchTerm); ?>'"><i class="fas fa-chevron-left"></i></button>
            <span class="page-indicator">Trang <?php echo $currentPage; ?> / <?php echo $totalPages; ?></span>
            <button <?php if($currentPage >= $totalPages) echo 'disabled'; ?> onclick="window.location.href='?page=<?php echo $currentPage+1; ?>&status=<?php echo $statusFilter; ?>&position=<?php echo $positionFilter; ?>&search=<?php echo urlencode($searchTerm); ?>'"><i class="fas fa-chevron-right"></i></button>
        </div>
    </div>
</main>

<!-- Employee Detail Modal -->
<div class="employee-modal" id="employeeDetailModal">
    <div class="employee-modal-content">
        <div class="employee-modal-header">
            <h2 id="modalEmployeeTitle">Chi tiết nhân viên</h2>
            <button class="close-employee-modal" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="employee-modal-body" id="modalContent">
            <!-- Content will be loaded via AJAX -->
            <div class="loading">Đang tải...</div>
        </div>
        <div class="employee-modal-footer">
            <button class="btn-edit-employee" id="editEmployeeBtn">
                <i class="fas fa-edit"></i> Chỉnh sửa
            </button>
            <button class="btn-print-employee" id="printEmployeeBtn">
                <i class="fas fa-print"></i> In thông tin
            </button>
        </div>
    </div>
</div>

<script>
    // Hiển thị modal chi tiết nhân viên
    function viewEmployeeDetails(id) {
        const modal = document.getElementById('employeeDetailModal');
        const modalContent = document.getElementById('modalContent');
        const editBtn = document.getElementById('editEmployeeBtn');
        const printBtn = document.getElementById('printEmployeeBtn');
        
        modal.style.display = 'flex'; 
        modalContent.innerHTML = '<div class="loading">Đang tải...</div>';
        
        // Thiết lập link cho nút chỉnh sửa
        editBtn.onclick = function() {
            window.location.href = 'edit_employee.php?id=' + id;
        };
        
     // Thiết lập link cho nút in
       printBtn.onclick = function() {
        window.location.href = 'print_employee.php?id=' + id;
       };

        // Gọi AJAX để lấy thông tin nhân viên
        fetch(`get_employee.php?id=${id}`)
            .then(response => response.json())
            .then(employee => {
                // Format dữ liệu
                const hireDate = new Date(employee.hire_date).toLocaleDateString('vi-VN');
                const salary = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(employee.basic_salary);
                const status = employee.status === 'active' ? 'Đang làm việc' : 'Đã nghỉ việc';
                
                // Hiển thị chi tiết nhân viên theo cấu trúc mới
                modalContent.innerHTML = `
                    <div class="employee-info">
                        <div class="employee-details">
                            <h3>Thông tin cá nhân</h3>
                            <p><strong>Họ và tên:</strong> ${employee.full_name}</p>
                            <p><strong>Số điện thoại:</strong> ${employee.phone}</p>
                            <p><strong>Email:</strong> ${employee.email || 'Không có'}</p>
                            <p><strong>Địa chỉ:</strong> ${employee.address || 'Không có'}</p>
                        </div>
                        
                        <div class="employee-details">
                            <h3>Thông tin công việc</h3>
                            <p><strong>Vị trí:</strong> ${employee.position}</p>
                            <p><strong>Ngày bắt đầu:</strong> ${hireDate}</p>
                            <p><strong>Lương cơ bản:</strong> ${salary}</p>
                            <p><strong>Trạng thái:</strong> <span class="employee-status ${employee.status}">${status}</span></p>
                        </div>
                    </div>
                    
                    <div class="employee-timeline">
                        <h3>Lịch sử làm việc</h3>
                        <div class="timeline-item">
                            <div class="timeline-date">${hireDate}</div>
                            <div class="timeline-content">Bắt đầu làm việc tại công ty với vị trí ${employee.position}</div>
                        </div>
                    </div>
                `;
            })
            .catch(error => {
                modalContent.innerHTML = '<div class="error">Đã xảy ra lỗi khi tải thông tin nhân viên!</div>';
                console.error('Error:', error);
            });
    }
    
    // Đóng modal
    function closeModal() {
        document.getElementById('employeeDetailModal').style.display = 'none';
    }
    
    // Xóa nhân viên
    function deleteEmployee(id) {
        if (confirm('Bạn có chắc chắn muốn xóa nhân viên này?')) {
            // Gửi request xóa nhân viên
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'employee_action.php';
            form.style.display = 'none';
            
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            idInput.value = id;
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'delete';
            
            form.appendChild(idInput);
            form.appendChild(actionInput);
            document.body.appendChild(form);
            form.submit();
        }
    }
    
    // Đóng modal khi click bên ngoài
    window.onclick = function(event) {
        const modal = document.getElementById('employeeDetailModal');
        if (event.target == modal) {
            closeModal();
        }
    }
</script>
</body>
</html>