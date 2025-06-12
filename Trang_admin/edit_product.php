<?php
// Kết nối CSDL
require_once 'db_connect.php';


// Lấy id sản phẩm từ URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Lấy thông tin sản phẩm hiện tại
$product = null;
if ($product_id > 0) {
    $sql = $conn->prepare("SELECT * FROM product WHERE product_id = ?");
    $sql->bind_param("i", $product_id);
    $sql->execute();
    $result = $sql->get_result();
    $product = $result->fetch_assoc();
    $sql->close();
}

if (!$product) {
    echo "Không tìm thấy sản phẩm.";
    exit;
}

// Xử lý khi form được gửi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $status = $_POST['status'];

    // Chuyển đổi status thành kiểu integer
    $statusBit = (int)$status;

    $image = $product['image']; // Mặc định giữ ảnh cũ
    // Kiểm tra nếu có ảnh mới được tải lên
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_dir = 'image/'; // Thư mục lưu ảnh
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true); // Tạo thư mục nếu chưa có
        }
        // Tạo tên ảnh mới (có thêm thời gian để tránh trùng tên)
        $image_name = time() . '_' . basename($_FILES['image']['name']);
        $target = $upload_dir . $image_name;
        // Di chuyển ảnh tải lên vào thư mục
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            // Lưu đường dẫn đầy đủ của ảnh vào cơ sở dữ liệu
            $image = $upload_dir . $image_name; // Lưu đường dẫn đầy đủ
        } else {
            echo "Lỗi khi tải ảnh lên!";
        }
    }
    
    // Cập nhật thông tin sản phẩm
    $update = $conn->prepare("UPDATE product SET name = ?, price = ?, status = ?, image = ? WHERE product_id = ?");
    $update->bind_param("sdisi", $name, $price, $statusBit, $image, $product_id);
    if ($update->execute()) {
        header("Location: products.php");
        exit;
    } else {
        echo "Lỗi cập nhật: " . $conn->error;
    }

    $update->close();
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Món Ăn</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
</head>
<body>
<?php include 'menu.php'; ?>
    <div class="edit_product_container">
        <h1 class="edit_product_page-title">Sửa Món Ăn</h1>

        <form action="edit_product.php?id=<?php echo $product['product_id']; ?>" method="POST" enctype="multipart/form-data">
            <div class="edit_product_form-group">
                <label for="name">Tên Món Ăn</label>
                <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
            </div>

            <div class="edit_product_form-group">
                <label for="price">Giá</label>
                <input type="number" name="price" id="price" value="<?php echo htmlspecialchars($product['price']); ?>" required>
            </div>

            <div class="edit_product_form-group">
                <label for="status">Trạng thái</label>
                <select name="status" id="status">
                    <option value="1" <?php echo $product['status'] == '1' ? 'selected' : ''; ?>>Còn bán</option>
                    <option value="0" <?php echo $product['status'] == '0' ? 'selected' : ''; ?>>Hết món</option>
                </select>
            </div>
            <div class="edit_product_form-group">
             <label for="image">Ảnh Món Ăn</label>
             <input type="file" name="image" id="image">
             <div style="margin-top:10px;">
             <img id="preview" src="<?php echo !empty($product['image']) ? 'image/'.htmlspecialchars($product['image']) : ''; ?>" 
             style="max-width:200px; <?php echo empty($product['image']) ? 'display:none;' : ''; ?>">
               </div>
         </div>

            <button type="submit">Cập Nhật</button>
        </form>
    </div>

</body>
<script src="script.js"></script>
</html>
