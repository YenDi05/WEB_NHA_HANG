<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra người dùng đã đăng nhập chưa
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Lấy tên người dùng an toàn
$full_name = isset($_SESSION["full_name"]) ? $_SESSION["full_name"] : "người dùng";
?>

<div class="header">
<div class="logo">
        <img src="image/logo.png" alt="Logo"/>
        <h2><a href="index.php">Nhà hàng ẩm thực Việt</a></h2>
    </div>
    <div class="admin-container">
        <span>Xin chào, <?php echo $full_name; ?></span>
        <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
    </div>
</div>


    <div class="menu">
        <nav class="sidebar">
            <ul>
                <li><a href="index.php"><i class="fas fa-home"></i> Trang chủ</a></li>
                <li><a href="products.php"><i class="fas fa-fish"></i> Mặt hàng</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Đơn hàng</a></li>
                <li><a href="employees.php"><i class="fas fa-user-tie"></i> Nhân viên</a></li>
            </ul>
        </nav>
    </div>
