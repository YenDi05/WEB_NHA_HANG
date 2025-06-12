<?php
// Kết nối CSDL
require_once 'db_connect.php';


// Lấy id sản phẩm từ URL
$product_id = $_GET['id'];

// Xóa sản phẩm khỏi cơ sở dữ liệu
$sql = "DELETE FROM product WHERE product_id = $product_id";

if ($conn->query($sql) === TRUE) {
    header("Location: products.php"); // Chuyển hướng về trang danh sách sản phẩm
} else {
    echo "Lỗi: " . $conn->error;
}

$conn->close();
?>
