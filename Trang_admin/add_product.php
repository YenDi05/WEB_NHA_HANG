<?php
// Kết nối tới CSDL web_nha_hang
require_once 'db_connect.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy dữ liệu từ form
    $name = $_POST['name'];
    $price = $_POST['price'];
    $status = $_POST['status'];
    $image = $_FILES['image']['name'];

    // Kiểm tra nếu có ảnh được tải lên
    if ($image) {
        // Tạo đường dẫn đầy đủ cho ảnh
        $imagePath = 'image/' . $image;
        
        // Di chuyển ảnh tải lên vào thư mục
        if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            // Ảnh đã được tải lên thành công
        } else {
            echo "<p style='color: red; font-weight: bold;'>Lỗi khi tải ảnh lên!</p>";
            exit; // Dừng script nếu có lỗi khi tải ảnh
        }
    } else {
        // Nếu không có ảnh mới, giữ ảnh cũ (nếu có)
        $imagePath = ''; // Chỗ này sẽ cập nhật ảnh cũ nếu cần
    }

    // Sử dụng prepared statement để bảo vệ khỏi SQL Injection
    $sql = "INSERT INTO product (name, price, status, image) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // Liên kết các tham số
        $stmt->bind_param("ssss", $name, $price, $status, $imagePath); // Sử dụng $imagePath để lưu đường dẫn đầy đủ

        // Thực hiện truy vấn
        if ($stmt->execute()) {
            // Chuyển hướng sau khi thêm sản phẩm thành công
            header("Location: products.php");
            exit;  // Đảm bảo dừng script sau khi chuyển hướng
        } else {
            echo "<p style='color: red; font-weight: bold;'>Lỗi: " . $stmt->error . "</p>";
        }

        // Đóng statement
        $stmt->close();
    } else {
        echo "<p style='color: red; font-weight: bold;'>Lỗi chuẩn bị câu lệnh: " . $conn->error . "</p>";
    }

    // Đóng kết nối
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Món Mới</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <?php include 'menu.php'; ?>
    <div class="add_product_container">
        <h1 class="add_product_page-title">Thêm Món Mới</h1>

        <form action="add_product.php" method="POST" enctype="multipart/form-data">
            <div class="add_product_form-group">
                <label for="name">Tên Món Ăn</label>
                <input type="text" name="name" id="name" required placeholder="Nhập tên món ăn">
            </div>

            <div class="add_product_form-group">
                <label for="price">Giá</label>
                <input type="number" name="price" id="price" required placeholder="Nhập giá món ăn">
            </div>

            <div class="add_product_form-group">
                <label for="status">Trạng thái</label>
                <select name="status" id="status" required>
                    <option value="in_stock">Còn bán</option>
                    <option value="out_of_stock">Hết món</option>
                </select>
            </div>

            <div class="add_product_form-group">
                 <label for="image">Ảnh Món Ăn</label>
                 <input type="file" name="image" id="image" required>
                 <div style="margin-top:10px;">
                 <img id="preview" src="" width="120">  
             </div>
            </div>

            <button type="submit"><i class="fa fa-plus"></i> Thêm Món</button>
        </form>
    </div>

</body>
<script src="script.js"></script>
</html>
