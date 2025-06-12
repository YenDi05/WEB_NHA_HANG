<?php
// Kết nối tới CSDL web_nha_hang
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $orderId = (int)$_POST['order_id'];
    $newStatus = mysqli_real_escape_string($conn, $_POST['new_status']);
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);
    
    // Cập nhật trạng thái đơn hàng
    $updateSql = "UPDATE orders SET status = '$newStatus' WHERE order_id = $orderId";
    
    if (mysqli_query($conn, $updateSql)) {
        // Thêm vào lịch sử trạng thái
        $historySQL = "INSERT INTO order_status_history (order_id, status, notes, updated_by) 
                      VALUES ($orderId, '$newStatus', '$notes', 'Admin')";
        mysqli_query($conn, $historySQL);
        
        header("Location: orders.php?message=Đã cập nhật trạng thái đơn hàng thành công");
    } else {
        header("Location: orders.php?error=Không thể cập nhật trạng thái đơn hàng");
    }
}