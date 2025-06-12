<?php
// Ẩn lỗi PHP để tránh trả về HTML thay vì JSON
error_reporting(0);
ini_set('display_errors', 0);

// Log lỗi thay vì hiển thị
ini_set('log_errors', 1);
ini_set('error_log', 'payment_confirmation_errors.log');
header('Content-Type: application/json');

// Kết nối tới CSDL web_nha_hang
$conn = new mysqli("localhost", "root", "", "web_nha_hang");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Kết nối thất bại: ' . $conn->connect_error]);
    exit;
}
mysqli_set_charset($conn, "utf8");

try {
    // Nhận mã đơn hàng
    $order_code = $_POST['order_code'] ?? '';
    if (empty($order_code)) {
        echo json_encode(['success' => false, 'message' => 'Mã đơn hàng không hợp lệ']);
        exit;
    }

    // Cập nhật trạng thái đơn hàng sang "Chờ xác nhận thanh toán"
    $sql = "UPDATE orders SET status = 'payment_review', payment_status = 'pending_verification' WHERE order_code = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception("Lỗi chuẩn bị câu lệnh cập nhật: " . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmt, 's', $order_code);
    $result = mysqli_stmt_execute($stmt);
    if (!$result) {
        throw new Exception("Lỗi cập nhật trạng thái đơn hàng: " . mysqli_stmt_error($stmt));
    }
    mysqli_stmt_close($stmt);

    // Thêm vào lịch sử trạng thái - Sử dụng truy vấn JOIN để lấy order_id
    $sql = "INSERT INTO order_status_history (order_id, status, notes, updated_by)
            SELECT order_id, 'payment_review', 'Khách hàng đã xác nhận thanh toán qua MoMo, chờ xác minh', 'customer'
            FROM orders WHERE order_code = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception("Lỗi chuẩn bị câu lệnh thêm lịch sử: " . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmt, 's', $order_code);
    $result = mysqli_stmt_execute($stmt);
    if (!$result) {
        throw new Exception("Lỗi thêm lịch sử trạng thái: " . mysqli_stmt_error($stmt));
    }

    echo json_encode(['success' => true, 'message' => 'Xác nhận thanh toán thành công']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi xử lý: ' . $e->getMessage()]);
} finally {
    mysqli_close($conn);
}
?>