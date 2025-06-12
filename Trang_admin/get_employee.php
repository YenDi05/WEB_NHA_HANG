<?php
require_once 'db_connect.php';

// Kiểm tra có ID được truyền vào không
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Chuẩn bị câu truy vấn
    $sql = "SELECT * FROM employees WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Trả về dữ liệu nhân viên dưới dạng JSON
        $employee = $result->fetch_assoc();
        echo json_encode($employee);
    } else {
        // Trả về lỗi nếu không tìm thấy nhân viên
        http_response_code(404);
        echo json_encode(['error' => 'Không tìm thấy nhân viên']);
    }
    
    $stmt->close();
} else {
    // Trả về lỗi nếu không có ID
    http_response_code(400);
    echo json_encode(['error' => 'Thiếu thông tin ID nhân viên']);
}

$conn->close();
?>