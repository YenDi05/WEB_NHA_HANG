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
    <title>Thanh toán MoMo - Nhà hàng ẩm thực Việt</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="momo-payment-container">
        <img src="image/momo-logo.png" alt="MoMo Logo" class="momo-logo">
        <h2>Thanh toán đơn hàng</h2>
        
        <div class="payment-info">
    <p>
        <span>Mã đơn hàng:</span>
        <strong><?php echo htmlspecialchars($order_code); ?></strong>
    </p>
    <p>
    <span>Giá trị đơn hàng:</span>
    <strong><?php echo number_format($order['total_amount'] / 1000, 0, ',', '.'); ?>k</strong>
</p>
<p>
    <span>Phí giao hàng:</span>
    <strong><?php echo number_format($order['shipping_fee'] / 1000, 0, ',', '.'); ?>k</strong>
</p>
<p>
    <span>Tổng thanh toán:</span>
    <strong><?php echo number_format(($order['total_amount'] + $order['shipping_fee']) / 1000, 0, ',', '.'); ?>k</strong>
</p>
</div>
        
        <div class="qr-container">
            <p>Quét mã QR bằng ứng dụng MoMo</p>
            <img src="image/momo-qr.jpg" alt="MoMo QR Code" class="qr-code">
        </div>
        
        <div class="note">
            <p><strong>Lưu ý:</strong></p>
            <p>1. Vui lòng nhập chính xác nội dung chuyển khoản: <strong><?php echo htmlspecialchars($order_code); ?></strong></p>
            <p>2. Sau khi thanh toán thành công, bạn vui lòng nhấn "Tôi đã thanh toán" để xác nhận.</p>
        </div>
        
        <div class="timer">
            <p>Đơn hàng sẽ hết hạn sau: <span id="countdown">15:00</span></p>
        </div>
        
        <div class="actions">
            <a href="index.php" class="btn">Hủy và quay lại</a>
            <button class="btn" id="check-payment-btn">Tôi đã thanh toán</button>
        </div>
    </div>
    
    <script>
        // Countdown timer
        function startCountdown(duration, display) {
            let timer = duration, minutes, seconds;
            const interval = setInterval(function () {
                minutes = parseInt(timer / 60, 10);
                seconds = parseInt(timer % 60, 10);

                minutes = minutes < 10 ? "0" + minutes : minutes;
                seconds = seconds < 10 ? "0" + seconds : seconds;

                display.textContent = minutes + ":" + seconds;

                if (--timer < 0) {
                    clearInterval(interval);
                    display.textContent = "Hết hạn";
                    // Redirect to expired page or show message
                    alert("Đơn hàng đã hết hạn thanh toán!");
                    window.location.href = "index.php";
                }
            }, 1000);
        }

        // Start countdown
        window.onload = function () {
            const fifteenMinutes = 60 * 15;
            const display = document.querySelector('#countdown');
            startCountdown(fifteenMinutes, display);
        };
        

document.getElementById('check-payment-btn').addEventListener('click', function() {
    if (confirm('Bạn đã hoàn thành thanh toán?')) {
        // Hiển thị thông báo đang xử lý
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
        this.disabled = true;
        
        // Gửi xác nhận đến server
        fetch('confirm_payment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'order_code=<?php echo $order_code; ?>'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Lỗi kết nối: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('Cảm ơn bạn! Chúng tôi sẽ xác nhận thanh toán và chuẩn bị đơn hàng của bạn.');
                window.location.href = 'payment_success.php?order_code=<?php echo $order_code; ?>';
            } else {
                alert('Có lỗi xảy ra: ' + data.message);
                // Khôi phục nút
                this.innerHTML = 'Tôi đã thanh toán';
                this.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi xử lý yêu cầu của bạn. Vui lòng thử lại sau.');
            // Khôi phục nút
            this.innerHTML = 'Tôi đã thanh toán';
            this.disabled = false;
        });
    }
});
    </script>

    
</body>
</html>