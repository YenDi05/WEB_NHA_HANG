<?php
// Kết nối tới CSDL web_nha_hang
require_once 'db_connect.php';

// Kiểm tra id đơn hàng
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: orders.php");
    exit();
}

$order_id = (int)$_GET['id'];

// Lấy thông tin đơn hàng
$orderSql = "SELECT * FROM orders WHERE order_id = $order_id";
$orderResult = mysqli_query($conn, $orderSql);

if (mysqli_num_rows($orderResult) == 0) {
    header("Location: orders.php");
    exit();
}

$order = mysqli_fetch_assoc($orderResult);

// Kiểm tra nếu đơn hàng đã giao hoặc đã hủy
if ($order['status'] == 'delivered' || $order['status'] == 'cancelled') {
    header("Location: orders.php?error=1");
    exit();
}

// Xử lý khi form được submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy thông tin từ form
    $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $customer_phone = mysqli_real_escape_string($conn, $_POST['customer_phone']);
    $customer_address = mysqli_real_escape_string($conn, $_POST['customer_address']);
    $notes = isset($_POST['order_note']) ? mysqli_real_escape_string($conn, $_POST['order_note']) : '';
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $total_amount = 0;
    
    // Cập nhật thông tin đơn hàng
    $updateOrderSql = "UPDATE orders SET 
                customer_name = '$customer_name', 
                customer_phone = '$customer_phone', 
                customer_address = '$customer_address', 
                notes = '$notes',
                status = '$status'
                WHERE order_id = $order_id";
                
    if (mysqli_query($conn, $updateOrderSql)) {
        // Xóa chi tiết đơn hàng cũ
        $deleteDetailsSql = "DELETE FROM order_details WHERE order_id = $order_id";
        mysqli_query($conn, $deleteDetailsSql);
        
        // Xử lý chi tiết đơn hàng (món ăn) mới
        if (isset($_POST['product_id']) && is_array($_POST['product_id'])) {
            for ($i = 0; $i < count($_POST['product_id']); $i++) {
                $product_id = (int)$_POST['product_id'][$i];
                $quantity = (int)$_POST['quantity'][$i];
                $price = (float)$_POST['price'][$i];
                $item_total = $quantity * $price;
                
                // Thêm chi tiết đơn hàng
                $detailSql = "INSERT INTO order_details (order_id, product_id, quantity, price, subtotal) 
                    VALUES ($order_id, $product_id, $quantity, $price, $item_total)";
                mysqli_query($conn, $detailSql);
                
                // Cộng vào tổng tiền
                $total_amount += $item_total;
            }
            
            // Cập nhật tổng tiền cho đơn hàng
            $updateTotalSql = "UPDATE orders SET total_amount = $total_amount WHERE order_id = $order_id";
            mysqli_query($conn, $updateTotalSql);
        }
        
        // Chuyển hướng về trang quản lý đơn hàng
        header("Location: orders.php?success=2");
        exit();
    } else {
        $error = "Lỗi: " . mysqli_error($conn);
    }
}

// Lấy chi tiết đơn hàng
$detailsSql = "SELECT od.*, p.name 
              FROM order_details od 
              JOIN product p ON od.product_id = p.product_id 
              WHERE od.order_id = $order_id";
$detailsResult = mysqli_query($conn, $detailsSql);

// Lấy danh sách món ăn từ CSDL
$productsSql = "SELECT * FROM product ORDER BY product_id, name"; 
$productsResult = mysqli_query($conn, $productsSql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cập Nhật Đơn Hàng</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <?php include 'menu.php'; ?>
    
    <!-- Main Content -->
    <main class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-edit"></i> Cập Nhật Đơn Hàng #<?php echo $order['order_code']; ?></h1>
            <button class="btn-secondary" onclick="window.location.href='orders.php'">
                <i class="fas fa-arrow-left"></i> Quay lại
            </button>
        </div>
        
        <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <div class="order-form-container">
            <form method="POST" action="" id="orderForm">
                <div class="form-row">
                    <div class="form-section customer-info">
                        <h2>Thông tin khách hàng</h2>
                        <div class="form-group">
                            <label for="customer_name">Tên khách hàng <span class="required">*</span></label>
                            <input type="text" id="customer_name" name="customer_name" value="<?php echo $order['customer_name']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="customer_phone">Số điện thoại <span class="required">*</span></label>
                            <input type="tel" 
                             id="customer_phone" 
                             name="customer_phone" 
                             pattern="[0-9]{10}" 
                             placeholder="Nhập số điện thoại 10 số"
                             title="Số điện thoại phải có 10 chữ số"
                             value="<?php echo htmlspecialchars($order['customer_phone']); ?>" 
                             required>
                         </div>
                        
                        <div class="form-group">
                            <label for="customer_address">Địa chỉ</label>
                            <textarea id="customer_address" name="customer_address" rows="3"><?php echo $order['customer_address']; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="order_note">Ghi chú</label>
                            <textarea id="order_note" name="order_note" rows="3"><?php echo htmlspecialchars($order['notes']); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="status">Trạng thái</label>
                            <select id="status" name="status">
                                <option value="new" <?php if($order['status'] == 'new') echo 'selected'; ?>>Mới</option>
                                <option value="processing" <?php if($order['status'] == 'processing') echo 'selected'; ?>>Đang xử lý</option>
                                <option value="shipping" <?php if($order['status'] == 'shipping') echo 'selected'; ?>>Đang giao</option>
                                <option value="delivered" <?php if($order['status'] == 'delivered') echo 'selected'; ?>>Đã giao</option>
                                <option value="cancelled" <?php if($order['status'] == 'cancelled') echo 'selected'; ?>>Đã hủy</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-section order-items">
                        <h2>Món ăn</h2>
                        <div class="product-search">
                            <input type="text" id="productSearch" placeholder="Tìm kiếm món ăn...">
                            <div id="searchResults" class="search-results"></div>
                        </div>
                        
                        <table class="order-items-table">
                            <thead>
                                <tr>
                                    <th>Món ăn</th>
                                    <th width="100">Số lượng</th>
                                    <th width="150">Đơn giá</th>
                                    <th width="150">Thành tiền</th>
                                    <th width="50"></th>
                                </tr>
                            </thead>
                            <tbody id="orderItems">
                                <!-- Các món ăn sẽ được thêm vào đây qua JavaScript -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-right"><strong>Tổng cộng:</strong></td>
                                    <td><span id="totalAmount"><?php echo number_format($order['total_amount'], 0, ',', '.') . 'đ'; ?></span></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                        
                        <div class="no-items-message" id="noItemsMessage">
                            Chưa có món ăn nào được thêm vào đơn hàng
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Cập nhật đơn hàng</button>
                </div>
            </form>
        </div>
    </main>
    
    <script>
    // Danh sách sản phẩm từ CSDL
    const products = [
        <?php 
        mysqli_data_seek($productsResult, 0);
        while($product = mysqli_fetch_assoc($productsResult)) {
            echo "{
                id: " . $product['product_id'] . ",
                name: '" . addslashes($product['name']) . "',
                price: " . $product['price'] . "
            },";
        }
        ?>
    ];
    
    // Biến lưu trữ danh sách món ăn trong đơn hàng
    let orderItems = [
        <?php 
        mysqli_data_seek($detailsResult, 0);
        while($detail = mysqli_fetch_assoc($detailsResult)) {
            echo "{
                id: " . $detail['product_id'] . ",
                name: '" . addslashes($detail['name']) . "',
                price: " . $detail['price'] . ",
                quantity: " . $detail['quantity'] . "
            },";
        }
        ?>
    ];
    
    let totalAmount = <?php echo $order['total_amount']; ?>;
    
    // Hiển thị các món ăn hiện có trong đơn hàng
    window.addEventListener('DOMContentLoaded', function() {
        orderItems.forEach((item, index) => {
            addOrderItemRow(index);
        });
        
        document.getElementById('noItemsMessage').style.display = orderItems.length ? 'none' : 'block';
    });
    
    // Hàm tìm kiếm sản phẩm
    document.getElementById('productSearch').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const searchResults = document.getElementById('searchResults');
        searchResults.innerHTML = '';
        
        if (searchTerm.length < 2) {
            searchResults.style.display = 'none';
            return;
        }
        
        const matchedProducts = products.filter(product => 
            product.name.toLowerCase().includes(searchTerm)
        );
        
        if (matchedProducts.length > 0) {
            matchedProducts.forEach(product => {
                const resultItem = document.createElement('div');
                resultItem.className = 'search-result-item';
                resultItem.textContent = product.name;
                resultItem.addEventListener('click', function() {
                    addProductToOrder(product);
                    searchResults.style.display = 'none';
                    document.getElementById('productSearch').value = '';
                });
                searchResults.appendChild(resultItem);
            });
            searchResults.style.display = 'block';
        } else {
            searchResults.style.display = 'none';
        }
    });
    
    // Thêm sản phẩm vào đơn hàng
    function addProductToOrder(product) {
        // Kiểm tra nếu sản phẩm đã có trong đơn hàng
        const existingItemIndex = orderItems.findIndex(item => item.id === product.id);
        
        if (existingItemIndex !== -1) {
            // Tăng số lượng nếu đã có
            orderItems[existingItemIndex].quantity += 1;
            updateOrderItemRow(existingItemIndex);
        } else {
            // Thêm mới nếu chưa có
            const newItem = {
                id: product.id,
                name: product.name,
                price: product.price,
                quantity: 1
            };
            
            orderItems.push(newItem);
            addOrderItemRow(orderItems.length - 1);
        }
        
        updateTotalAmount();
        document.getElementById('noItemsMessage').style.display = orderItems.length ? 'none' : 'block';
    }
    
    // Thêm dòng sản phẩm vào bảng
    function addOrderItemRow(index) {
        const item = orderItems[index];
        const tbody = document.getElementById('orderItems');
        
        const row = document.createElement('tr');
        row.setAttribute('data-index', index);
        
        const itemTotal = item.price * item.quantity;
        
        row.innerHTML = `
            <td>
                ${item.name}
                <input type="hidden" name="product_id[]" value="${item.id}">
            </td>
            <td>
                <input type="number" name="quantity[]" min="1" value="${item.quantity}" 
                       class="quantity-input" onchange="updateQuantity(${index}, this.value)">
            </td>
            <td>
                ${formatCurrency(item.price)}
                <input type="hidden" name="price[]" value="${item.price}">
            </td>
            <td>${formatCurrency(itemTotal)}</td>
            <td>
                <button type="button" class="btn-icon btn-delete" onclick="removeItem(${index})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        
        tbody.appendChild(row);
    }
    
    // Cập nhật dòng sản phẩm
    function updateOrderItemRow(index) {
        const item = orderItems[index];
        const row = document.querySelector(`tr[data-index="${index}"]`);
        
        if (row) {
            const itemTotal = item.price * item.quantity;
            
            // Cập nhật số lượng
            row.querySelector('input[name="quantity[]"]').value = item.quantity;
            
            // Cập nhật thành tiền
            row.cells[3].textContent = formatCurrency(itemTotal);
        }
    }
    
    // Cập nhật số lượng
    function updateQuantity(index, newQuantity) {
        newQuantity = parseInt(newQuantity, 10);
        if (newQuantity > 0) {
            orderItems[index].quantity = newQuantity;
            updateOrderItemRow(index);
            updateTotalAmount();
        }
    }
    
    // Xóa món ăn khỏi đơn hàng
    function removeItem(index) {
        orderItems.splice(index, 1);
        
        // Xóa tất cả các hàng và vẽ lại
        const tbody = document.getElementById('orderItems');
        tbody.innerHTML = '';
        
        orderItems.forEach((item, i) => {
            addOrderItemRow(i);
        });
        
        updateTotalAmount();
        document.getElementById('noItemsMessage').style.display = orderItems.length ? 'none' : 'block';
    }
    
    // Cập nhật tổng tiền
    function updateTotalAmount() {
        totalAmount = orderItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        document.getElementById('totalAmount').textContent = formatCurrency(totalAmount);
    }
    
    // Định dạng số tiền
    function formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN', { 
            style: 'currency', 
            currency: 'VND',
            minimumFractionDigits: 0
        }).format(amount);
    }
    
    // Kiểm tra trước khi submit form
    document.getElementById('orderForm').addEventListener('submit', function(e) {
        if (orderItems.length === 0) {
            e.preventDefault();
            alert('Vui lòng thêm ít nhất một món ăn vào đơn hàng');
        }
    });
    
    // Bắt sự kiện click bên ngoài kết quả tìm kiếm để đóng dropdown
    document.addEventListener('click', function(e) {
        const searchResults = document.getElementById('searchResults');
        const productSearch = document.getElementById('productSearch');
        
        if (e.target !== searchResults && e.target !== productSearch) {
            searchResults.style.display = 'none';
        }
    });
    </script>
</body>
</html>