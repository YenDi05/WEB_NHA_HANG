<!-- Xử lý thêm/sửa/xóa  -->
<?php
require_once 'db_connect.php';

// Lấy action từ form
$action = isset($_POST['action']) ? $_POST['action'] : '';

// Xử lý theo action
switch ($action) {
    case 'add':
        addEmployee($conn);
        break;
    case 'edit':
        editEmployee($conn);
        break;
    case 'delete':
        deleteEmployee($conn);
        break;
    default:
        header('Location: employees.php');
        exit;
}

// Hàm thêm nhân viên mới
function addEmployee($conn) {
    // Lấy dữ liệu từ form
    $full_name = $_POST['full_name'];
    $phone = $_POST['phone'];
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $address = isset($_POST['address']) ? $_POST['address'] : '';
    $position = $_POST['position'];
    // Kiểm tra xem có vị trí mới không
    if (!isset($_POST['position']) || $_POST['position'] === 'new') {
        if (isset($_POST['new-position']) && !empty($_POST['new-position'])) {
            $position = $_POST['new-position'];
        } else {
            header('Location: add_employee.php?error=missing_position');
            exit;
        }
    } else {
        $position = $_POST['position'];
    }
    $hire_date = $_POST['hire_date'];
    $basic_salary = $_POST['basic_salary'];
    $status = $_POST['status'];
    
    // Chuẩn bị câu truy vấn
    $sql = "INSERT INTO employees (full_name, phone, email, address, position, hire_date, basic_salary, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssds", $full_name, $phone, $email, $address, $position, $hire_date, $basic_salary, $status);
    
    // Thực hiện truy vấn
    if ($stmt->execute()) {
        // Chuyển hướng về trang nhân viên với thông báo thành công
        header('Location: employees.php?success=add');
    } else {
        // Chuyển hướng về trang nhân viên với thông báo lỗi
        header('Location: employees.php?error=add');
    }
    
    $stmt->close();
    exit;
}

// Hàm cập nhật thông tin nhân viên
function editEmployee($conn) {
    // Lấy dữ liệu từ form
    $id = $_POST['id'];
    $full_name = $_POST['full_name'];
    $phone = $_POST['phone'];
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $address = isset($_POST['address']) ? $_POST['address'] : '';
    $position = $_POST['position'];
    $hire_date = $_POST['hire_date'];
    $basic_salary = $_POST['basic_salary'];
    $status = $_POST['status'];
    
    // Chuẩn bị câu truy vấn
    $sql = "UPDATE employees SET full_name=?, phone=?, email=?, address=?, position=?, 
            hire_date=?, basic_salary=?, status=? WHERE id=?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssdsi", $full_name, $phone, $email, $address, $position, $hire_date, $basic_salary, $status, $id);
    
    // Thực hiện truy vấn
    if ($stmt->execute()) {
        // Chuyển hướng về trang nhân viên với thông báo thành công
        header('Location: employees.php?success=edit');
    } else {
        // Chuyển hướng về trang nhân viên với thông báo lỗi
        header('Location: employees.php?error=edit');
    }
    
    $stmt->close();
    exit;
}

// Hàm xóa nhân viên
function deleteEmployee($conn) {
    // Lấy ID nhân viên cần xóa
    $id = $_POST['id'];
    
    // Chuẩn bị câu truy vấn
    $sql = "DELETE FROM employees WHERE id=?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    // Thực hiện truy vấn
    if ($stmt->execute()) {
        // Chuyển hướng về trang nhân viên với thông báo thành công
        header('Location: employees.php?success=delete');
    } else {
        // Chuyển hướng về trang nhân viên với thông báo lỗi
        header('Location: employees.php?error=delete');
    }
    
    $stmt->close();
    exit;
}
?>