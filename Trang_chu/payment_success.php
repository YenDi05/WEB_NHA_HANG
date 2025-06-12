<?php
// Kết nối tới CSDL web_nha_hang
$conn = new mysqli("localhost", "root", "", "web_nha_hang");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
mysqli_set_charset($conn, "utf8");

// Lấy mã đơn hàng
$order_code = $_GET['order_code'] ?? '';

if (empty($order_code)) {
    header('Location: index.php');
    exit;
}

// Lấy thông tin đơn hàng
$sql = "SELECT * FROM orders WHERE order_code = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 's', $order_code);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    header('Location: index.php');
    exit;
}

$order = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán thành công - Nhà hàng ẩm thực Việt</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="success-container">
        <i class="fas fa-check-circle success-icon"></i>
        <h2>Cảm ơn bạn đã đặt hàng!</h2>
        <p>Chúng tôi đã nhận được xác nhận thanh toán của bạn và sẽ sớm xác minh giao dịch.</p>
        
        <div class="order-details">
            <p><strong>Mã đơn hàng:</strong> <?php echo htmlspecialchars($order_code); ?></p>
            <p><strong>Giá trị đơn hàng:</strong> <?php echo number_format($order['total_amount'] / 1000, 0, ',', '.'); ?>k</p>
            <p><strong>Phí giao hàng:</strong> <?php echo number_format($order['shipping_fee'] / 1000, 0, ',', '.'); ?>k</p>
            <p><strong>Tổng tiền:</strong> <?php echo number_format(($order['total_amount'] + $order['shipping_fee']) / 1000, 0, ',', '.'); ?>k</p>
            <p><strong>Phương thức thanh toán:</strong> Ví MoMo</p>
            <p><strong>Trạng thái:</strong> Đang chờ xác nhận thanh toán</p>
        </div>
        
        <p>Chúng tôi sẽ liên hệ với bạn qua số điện thoại để xác nhận đơn hàng trong thời gian sớm nhất.</p>
        
        <div class="actions">
            <a href="index.php" class="btn">Quay lại trang chủ</a>
        </div>
    </div>
    
    <script>
        // Xóa giỏ hàng trong localStorage
        localStorage.removeItem('cart');
    </script>
</body>
</html>