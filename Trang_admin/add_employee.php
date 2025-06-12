<?php
// Kết nối tới CSDL
require_once 'db_connect.php';

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
    <title>Thêm Nhân viên mới</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php include 'menu.php'; ?>

<!-- Main Content -->
<main class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-user-plus"></i> Thêm Nhân viên mới</h1>
        <button class="btn-primary" onclick="window.location.href='employees.php'">
            <i class="fas fa-arrow-left"></i> Quay lại danh sách
        </button>
    </div>
    
    <div class="employee-table-container">
        <form method="POST" action="employee_action.php">
            <input type="hidden" name="action" value="add">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="full_name">Họ và tên <span class="required">*</span></label>
                    <input type="text" id="full_name" name="full_name" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Số điện thoại <span class="required">*</span></label>
                    <input type="text" id="phone" name="phone" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email">
                </div>
                
                <div class="form-group">
                    <label for="position">Vị trí <span class="required">*</span></label>
                    <select id="position" name="position" required>
                        <option value="">-- Chọn vị trí --</option>
                        <?php foreach($positions as $position): ?>
                        <option value="<?php echo $position; ?>"><?php echo $position; ?></option>
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
                    <input type="date" id="hire_date" name="hire_date" required value="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="basic_salary">Lương cơ bản (VNĐ) <span class="required">*</span></label>
                    <input type="number" id="basic_salary" name="basic_salary" required min="0" step="100000">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="address">Địa chỉ</label>
                    <textarea id="address" name="address" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="status">Trạng thái <span class="required">*</span></label>
                    <select id="status" name="status" required>
                        <option value="active" selected>Đang làm việc</option>
                        <option value="inactive">Đã nghỉ việc</option>
                    </select>
                </div>
            </div>
            
            <div class="form-buttons">
                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i> Lưu nhân viên
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
    if (positionSelect.value === 'new') {
        if (newPosition.value.trim() === '') {
            alert('Vui lòng nhập vị trí mới');
            newPosition.focus();
            return false;
        }
        // Tạo một input ẩn chứa giá trị vị trí mới
        var hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'position';
        hiddenInput.value = newPosition.value.trim();
        this.appendChild(hiddenInput);
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