<?php
// Kết nối tới CSDL web_nha_hang
require_once 'db_connect.php';

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Cập nhật trạng thái đơn hàng thành "cancelled"
$updateSql = "UPDATE orders SET status = 'cancelled' WHERE order_id = $orderId";

if (mysqli_query($conn, $updateSql)) {
    // Thêm vào lịch sử trạng thái
    $historySQL = "INSERT INTO order_status_history (order_id, status, notes, updated_by) 
                  VALUES ($orderId, 'cancelled', 'Đơn hàng đã bị hủy', 'Admin')";
    mysqli_query($conn, $historySQL);
    
    header("Location: orders.php?message=Đã hủy đơn hàng thành công");
} else {
    header("Location: orders.php?error=Không thể hủy đơn hàng");
}