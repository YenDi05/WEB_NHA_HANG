<?php
// Kết nối tới CSDL
require_once 'db_connect.php';

// Kiểm tra id nhân viên
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: employees.php');
    exit;
}

$employeeId = intval($_GET['id']);

// Lấy thông tin nhân viên
$sql = "SELECT * FROM employees WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employeeId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: employees.php?error=notfound');
    exit;
}

$employee = $result->fetch_assoc();
$stmt->close();

// Lấy danh sách vị trí công việc cho dropdown
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
    <title>Chỉnh sửa Nhân viên</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php include 'menu.php'; ?>

<!-- Main Content -->
<main class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-user-edit"></i> Chỉnh sửa Nhân viên</h1>
        <button class="btn-primary" onclick="window.location.href='employees.php'">
            <i class="fas fa-arrow-left"></i> Quay lại danh sách
        </button>
    </div>
    
    <div class="employee-table-container">
        <form method="POST" action="employee_action.php">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="<?php echo $employee['id']; ?>">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="full_name">Họ và tên <span class="required">*</span></label>
                    <input type="text" id="full_name" name="full_name" required value="<?php echo htmlspecialchars($employee['full_name']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone">Số điện thoại <span class="required">*</span></label>
                    <input type="text" id="phone" name="phone" required value="<?php echo htmlspecialchars($employee['phone']); ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($employee['email']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="position">Vị trí <span class="required">*</span></label>
                    <select id="position" name="position" required>
                        <?php 
                        // Kiểm tra xem vị trí hiện tại có trong danh sách không
                        $currentPositionExists = false;
                        foreach($positions as $position) {
                            if ($position === $employee['position']) {
                                $currentPositionExists = true;
                            }
                        }
                        
                        // Nếu không có trong danh sách, thêm vào
                        if (!$currentPositionExists) {
                            $positions[] = $employee['position'];
                        }
                        
                        // Hiển thị tất cả vị trí, với vị trí hiện tại được chọn
                        foreach($positions as $position): 
                        ?>
                        <option value="<?php echo $position; ?>" <?php if($position === $employee['position']) echo 'selected'; ?>><?php echo $position; ?></option>
                        <?php endforeach; ?>
                        <option value="new">Thêm vị trí mới...</option>
                    </select>
                    <div id="new-position-container" style="display: none; margin-top: 10px;">
                        <input type="text" id="new-position" placeholder="Nhập vị trí mới">
                    </div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="hire_date">Ngày bắt đầu <span class="required">*</span></label>
                    <input type="date" id="hire_date" name="hire_date" required value="<?php echo date('Y-m-d', strtotime($employee['hire_date'])); ?>">
                </div>
                
                <div class="form-group">
                    <label for="basic_salary">Lương cơ bản (VNĐ) <span class="required">*</span></label>
                    <input type="number" id="basic_salary" name="basic_salary" required min="0" step="100000" value="<?php echo $employee['basic_salary']; ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="address">Địa chỉ</label>
                    <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($employee['address']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="status">Trạng thái <span class="required">*</span></label>
                    <select id="status" name="status" required>
                        <option value="active" <?php if($employee['status'] === 'active') echo 'selected'; ?>>Đang làm việc</option>
                        <option value="inactive" <?php if($employee['status'] === 'inactive') echo 'selected'; ?>>Đã nghỉ việc</option>
                    </select>
                </div>
            </div>
            
            <div class="form-buttons">
                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i> Lưu thay đổi
                </button>
            </div>
        </form>
    </div>
</main>

<script>
// Xử lý thêm vị trí mới
document.getElementById('position').addEventListener('change', function() {
    var newPositionContainer = document.getElementById('new-position-container');
    if (this.value === 'new') {
        newPositionContainer.style.display = 'block';
        document.getElementById('new-position').focus();
    } else {
        newPositionContainer.style.display = 'none';
    }
});

// Xử lý khi submit form
document.querySelector('form').addEventListener('submit', function(e) {
    var positionSelect = document.getElementById('position');
    var newPosition = document.getElementById('new-position');
    
    // Nếu chọn thêm vị trí mới và đã nhập vị trí mới
    if (positionSelect.value === 'new' && newPosition.value.trim() !== '') {
        // Thay đổi giá trị của select thành vị trí mới
        positionSelect.value = newPosition.value.trim();
    }
    
    // Kiểm tra số điện thoại
    var phone = document.getElementById('phone').value;
    if (!/^[0-9]{10,11}$/.test(phone)) {
        alert('Số điện thoại phải có 10-11 chữ số');
        e.preventDefault();
        return false;
    }
});
</script>
</body>
</html>