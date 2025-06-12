<?php
// Kết nối tới CSDL web_nha_hang
require_once 'db_connect.php';

// Lấy dữ liệu từ bảng product
$sql = "SELECT * FROM product";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Danh Sách Món Ăn </title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>
<body>
<?php include 'menu.php'; ?>
     <div class="product-container">
    <h1 class="page-title">Danh Sách Món Ăn</h1> 
    <div class="product-grid">
      <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
              <div class="product-card">

                  <img src="<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>" class="product-image">
                  <div class="card-body">
                      <h4 class="product-name"><?php echo $row['name']; ?></h4>
                      <p class="product-price"><?php echo number_format($row['price'], 0, ',', '.') . 'K'; ?></p>
                      <span class="product-status <?php echo ($row['status'] == 1) ? 'in-stock' : 'out-of-stock'; ?>">
                          <?php echo ($row['status'] == 1) ? 'Còn bán' : 'Hết món'; ?>
                      </span>
                      <div class="product-actions">
                          <a href="edit_product.php?id=<?php echo $row['product_id']; ?>" class="edit-btn">Sửa</a>
                          <a href="delete_product.php?id=<?php echo $row['product_id']; ?>" class="delete-btn" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?');">Xóa</a>
                      </div>
                  </div>
              </div>
          <?php endwhile; ?>
      <?php else: ?>
          <p>Không có sản phẩm nào.</p>
      <?php endif; ?>
    </div>
    <div class="add-product-btn-container">
      <a href="add_product.php" class="add-product-btn">Thêm Món Mới</a>
    </div>
  </div>



  <script src="https://unpkg.com/chart.js@4.3.0/dist/chart.umd.js"></script>
  <script src="script.js"></script>
</body>
</html>
<?php $conn->close(); ?>
