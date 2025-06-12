<?php
// Kết nối tới CSDL web_nha_hang
require_once 'db_connect.php';

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Lấy thông tin đơn hàng
$orderSql = "SELECT * FROM orders WHERE order_id = $orderId";
$orderResult = mysqli_query($conn, $orderSql);
$order = mysqli_fetch_assoc($orderResult);

if (!$order) {
    echo "Không tìm thấy đơn hàng";
    exit;
}

// Lấy chi tiết đơn hàng
$detailsSql = "SELECT od.*, p.name as product_name 
               FROM order_details od 
               JOIN product p ON od.product_id = p.product_id 
               WHERE od.order_id = $orderId";
$detailsResult = mysqli_query($conn, $detailsSql);

// Ánh xạ trạng thái
$statusMap = [
    'new' => 'Mới',
    'processing' => 'Đang xử lý',
    'shipping' => 'Đang giao',
    'delivered' => 'Đã giao',
    'cancelled' => 'Đã hủy',
    'pending' => 'Chờ thanh toán',
    'payment_review' => 'Đang xác nhận thanh toán',
    'pending_verification' => 'Đang xác nhận thanh toán',
    '' => 'Không xác định'  // Thêm trường hợp giá trị rỗng
];
?>

<input type="hidden" id="orderCode" value="<?php echo $order['order_code']; ?>">

<!-- Order Info -->
<div class="order-info">
    <div class="info-section">
        <h3>Thông tin khách hàng</h3>
        <p><strong>Tên:</strong> <?php echo $order['customer_name']; ?></p>
        <p><strong>SĐT:</strong> <?php echo $order['customer_phone']; ?></p>
        <p><strong>Email:</strong> <?php echo $order['customer_email']; ?></p>
        <p><strong>Địa chỉ:</strong> <?php echo $order['customer_address']; ?></p>
    </div>
    
    <div class="info-section">
    <h3>Thông tin đơn hàng</h3>
    <p><strong>Mã đơn:</strong> <?php echo $order['order_code']; ?></p>
    <p><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></p>
    <p><strong>Trạng thái:</strong> 
        <span class="status <?php echo $order['status']; ?>">
        <?php 
            // Kiểm tra xem trạng thái có tồn tại trong mảng $statusMap không
            echo (isset($order['status']) && isset($statusMap[$order['status']])) 
                ? $statusMap[$order['status']] 
                : 'Đang xác nhận thanh toán'; 
        ?>
        </span>
    </p>
    <p><strong>Ghi chú:</strong> <?php echo $order['notes'] ? $order['notes'] : 'Không có'; ?></p>
</div>
</div>

<!-- Order Items -->
<div class="order-items">
    <h3>Món ăn đã đặt</h3>
    <table class="items-table">
        <thead>
            <tr>
                <th>Món ăn</th>
                <th>Số lượng</th>
                <th>Đơn giá</th>
                <th>Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $subtotal = 0;
            while ($item = mysqli_fetch_assoc($detailsResult)) {
                $itemTotal = $item['price'] * $item['quantity'];
                $subtotal += $itemTotal;
            ?>
            <tr>
                <td><?php echo $item['product_name']; ?></td>
                <td><?php echo $item['quantity']; ?></td>
                <td><?php echo number_format($item['price']/1000, 0, ',', '.') . 'K'; ?></td>
                <td><?php echo number_format($itemTotal/1000, 0, ',', '.') . 'K'; ?></td>
            </tr>
            <?php } ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right">Phí giao hàng</td>
                <td><?php echo number_format($order['shipping_fee']/1000, 0, ',', '.') . 'K'; ?></td>
            </tr>
            <tr>
                <td colspan="3" class="text-right"><strong>Tổng cộng</strong></td>
                <td><strong><?php echo number_format(($subtotal + $order['shipping_fee'])/1000, 0, ',', '.') . 'K'; ?></strong></td>
            </tr>
        </tfoot>
    </table>
</div>

<?php if ($order['status'] != 'delivered' && $order['status'] != 'cancelled') { ?>
<!-- Update Status -->
<div class="update-section">
    <h3>Cập nhật trạng thái</h3>
    <form method="post" action="update_order_status.php">
        <input type="hidden" name="order_id" value="<?php echo $orderId; ?>">
        <div class="status-update">
            <select name="new_status">
                <option value="new" <?php if($order['status'] == 'new') echo 'selected'; ?>>Mới</option>
                <option value="processing" <?php if($order['status'] == 'processing') echo 'selected'; ?>>Đang xử lý</option>
                <option value="shipping" <?php if($order['status'] == 'shipping') echo 'selected'; ?>>Đang giao</option>
                <option value="delivered" <?php if($order['status'] == 'delivered') echo 'selected'; ?>>Đã giao</option>
                <option value="cancelled" <?php if($order['status'] == 'cancelled') echo 'selected'; ?>>Hủy đơn</option>
            </select>
            <button type="submit" class="btn-update">Cập nhật</button>
        </div>

    </form>
</div>

<div class="modal-footer">
    <?php if ($order['status'] != 'new') { ?>
    <button class="btn-print" onclick="printOrder(<?php echo $orderId; ?>)"><i class="fas fa-print"></i> In hóa đơn</button>
    <?php } ?>
</div>
<?php } else { ?>
<div class="modal-footer">
    <button class="btn-print" onclick="printOrder(<?php echo $orderId; ?>)"><i class="fas fa-print"></i> In hóa đơn</button>
</div>
<?php } ?>