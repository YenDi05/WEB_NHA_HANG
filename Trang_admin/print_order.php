<?php
// Kết nối tới CSDL
require_once 'db_connect.php';

// Đảm bảo có ID đơn hàng
$orderId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($orderId <= 0) {
    die("Không tìm thấy mã đơn hàng hợp lệ");
}

// Lấy thông tin đơn hàng
$orderSql = "SELECT * FROM orders WHERE order_id = $orderId";
$orderResult = mysqli_query($conn, $orderSql);

if (mysqli_num_rows($orderResult) == 0) {
    die("Không tìm thấy đơn hàng");
}

$order = mysqli_fetch_assoc($orderResult);

// Lấy chi tiết đơn hàng từ bảng order_detail
$detailsSql = "SELECT od.*, p.name 
               FROM `order_details` od 
               LEFT JOIN product p ON od.product_id = p.product_id 
               WHERE od.order_id = $orderId";
$detailsResult = mysqli_query($conn, $detailsSql);

// Nếu có lỗi, kiểm tra cấu trúc bảng
if (!$detailsResult) {
    // Thử lại với tên bảng khác nếu có lỗi
    $detailsSql = "SELECT od.*, p.name 
                  FROM `order_details` od 
                  LEFT JOIN product p ON od.product_id = p.product_id 
                  WHERE od.order_id = $orderId";
    $detailsResult = mysqli_query($conn, $detailsSql);
}

// Lấy lịch sử trạng thái đơn hàng
$historySql = "SELECT * FROM `order_status_history` 
              WHERE order_id = $orderId 
              ORDER BY updated_at DESC";
$historyResult = mysqli_query($conn, $historySql);

// Ánh xạ trạng thái từ tiếng Anh sang tiếng Việt
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
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa đơn <?php echo $order['order_code']; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 20px;
        }
        .invoice-header {
            text-align: center;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .invoice-header h1 {
            margin-bottom: 5px;
            color: #3a6ea5;
        }
        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .invoice-details div {
            flex: 1;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .total-row {
            font-weight: bold;
        }
        .summary-box {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
        }
        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
        }
        .new { background-color: #e3f2fd; color: #0d47a1; }
        .processing { background-color: #fff8e1; color: #ff8f00; }
        .shipping { background-color: #e8f5e9; color: #2e7d32; }
        .delivered { background-color: #e8f5e9; color: #2e7d32; }
        .cancelled { background-color: #ffebee; color: #c62828; }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }
        .print-button {
            text-align: center;
            margin-top: 30px;
        }
        .print-button button {
            background-color: #3a6ea5;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin: 0 5px;
        }
        .print-button button:hover {
            background-color: #2c5282;
        }
        @media print {
            .print-button {
                display: none;
            }
            body {
                padding: 0;
            }
            .invoice-container {
                border: none;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="invoice-header">
            <h1>HÓA ĐƠN</h1>
            <p>Mã đơn: #<?php echo $order['order_code']; ?></p>
            <p>Nhà hàng Ẩm Thực Việt</p>
            <p>Địa chỉ: 19/46 Tân Chánh Hiệp, Q.12, TP. Hồ Chí Minh</p>
            <p>SĐT: 0344883755 | Email: giakhanhngo1503@gmail.com</p>
        </div>
        
        <div class="summary-box">
            <div><strong>Ngày đặt hàng:</strong> <?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></div>
            <div><strong>Trạng thái:</strong> <span class="status <?php echo $order['status']; ?>"><?php echo isset($statusMap[$order['status']]) ? $statusMap[$order['status']] : 'Đang xác nhận thanh toán'; ?></span></div>
            <?php if (isset($order['payment_method'])): ?>
            <div><strong>Phương thức thanh toán:</strong> <?php echo $order['payment_method']; ?></div>
            <?php endif; ?>
        </div>
        
        <div class="invoice-details">
            <div>
                <h3>Thông tin khách hàng:</h3>
                <p><strong>Tên:</strong> <?php echo $order['customer_name']; ?></p>
                <?php if (isset($order['delivery_address'])): ?>
                <p><strong>Địa chỉ:</strong> <?php echo $order['delivery_address']; ?></p>
                <?php endif; ?>
                <p><strong>SĐT:</strong> <?php echo $order['customer_phone']; ?></p>
                <?php if (isset($order['customer_email'])): ?>
                <p><strong>Email:</strong> <?php echo $order['customer_email']; ?></p>
                <?php endif; ?>
            </div>
            <div>
                <h3>Thông tin giao hàng:</h3>
                <?php if (isset($order['recipient_name'])): ?>
                <p><strong>Người nhận:</strong> <?php echo $order['recipient_name']; ?></p>
                <?php else: ?>
                <p><strong>Người nhận:</strong> <?php echo $order['customer_name']; ?></p>
                <?php endif; ?>
                
                <?php if (isset($order['delivery_address'])): ?>
                <p><strong>Địa chỉ giao:</strong> <?php echo $order['delivery_address']; ?></p>
                <?php endif; ?>
                
                <?php if (isset($order['delivery_note'])): ?>
                <p><strong>Ghi chú giao hàng:</strong> <?php echo $order['delivery_note']; ?></p>
                <?php endif; ?>
                
                <?php if ($order['status'] == 'delivered' && isset($order['delivery_date'])): ?>
                <p><strong>Đã giao vào:</strong> <?php echo date('d/m/Y H:i', strtotime($order['delivery_date'])); ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <h3>Chi tiết đơn hàng:</h3>
        <?php if ($detailsResult && mysqli_num_rows($detailsResult) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Sản phẩm</th>
                    <th>Đơn giá</th>
                    <th>Số lượng</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $subtotal = 0;
                $i = 1;
                
                // Xác định tên trường
                $firstRow = mysqli_fetch_assoc($detailsResult);
                mysqli_data_seek($detailsResult, 0); // Reset con trỏ
                
                $priceField = isset($firstRow['price']) ? 'price' : 
                             (isset($firstRow['product_price']) ? 'product_price' : 'unit_price');
                             
                $quantityField = isset($firstRow['quantity']) ? 'quantity' : 
                                (isset($firstRow['product_quantity']) ? 'product_quantity' : 'qty');
                
                while ($item = mysqli_fetch_assoc($detailsResult)): 
                    $itemPrice = $item[$priceField];
                    $itemQuantity = $item[$quantityField];
                    $itemTotal = $itemPrice * $itemQuantity;
                    $subtotal += $itemTotal;
                ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo $item['name']; ?></td>
                    <td><?php echo number_format($itemPrice, 0, ',', '.'); ?>đ</td>
                    <td><?php echo $itemQuantity; ?></td>
                    <td><?php echo number_format($itemTotal, 0, ',', '.'); ?>đ</td>
                </tr>
                <?php endwhile; ?>
                
                <tr>
                    <td colspan="4" style="text-align: right;"><strong>Tạm tính:</strong></td>
                    <td><?php echo number_format($subtotal, 0, ',', '.'); ?>đ</td>
                </tr>
                <?php if (isset($order['discount_amount']) && $order['discount_amount'] > 0): ?>
                <tr>
                    <td colspan="4" style="text-align: right;"><strong>Giảm giá:</strong></td>
                    <td>-<?php echo number_format($order['discount_amount'], 0, ',', '.'); ?>đ</td>
                </tr>
                <?php endif; ?>
                <?php if (isset($order['shipping_fee']) && $order['shipping_fee'] > 0): ?>
                <tr>
                    <td colspan="4" style="text-align: right;"><strong>Phí vận chuyển:</strong></td>
                    <td><?php echo number_format($order['shipping_fee'], 0, ',', '.'); ?>đ</td>
                </tr>
                <?php endif; ?>
                <tr class="total-row">
                    <td colspan="4" style="text-align: right;"><strong>Tổng cộng:</strong></td>
                    <td><?php echo number_format($order['total_amount'], 0, ',', '.'); ?>đ</td>
                </tr>
            </tbody>
        </table>
        <?php else: ?>
        <!-- Nếu không có chi tiết sản phẩm, hiển thị chỉ tổng cộng -->
        <table>
            <thead>
                <tr>
                    <th>Mô tả</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Tổng đơn hàng</td>
                    <td><?php echo number_format($order['total_amount'], 0, ',', '.'); ?>đ</td>
                </tr>
            </tbody>
        </table>
        <?php endif; ?>
        
        <?php if (isset($order['note']) && !empty($order['note'])): ?>
        <div>
            <h3>Ghi chú:</h3>
            <p><?php echo $order['note']; ?></p>
        </div>
        <?php endif; ?>
        
        <?php if ($historyResult && mysqli_num_rows($historyResult) > 0): ?>
        <div>
            <h3>Lịch sử đơn hàng:</h3>
            <table>
                <thead>
                    <tr>
                        <th>Thời gian</th>
                        <th>Trạng thái</th>
                        <th>Ghi chú</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($history = mysqli_fetch_assoc($historyResult)): ?>
                    <tr>
                        <td><?php echo date('d/m/Y H:i', strtotime($history['updated_at'])); ?></td>
                        <td><span class="status <?php echo $history['status']; ?>"><?php echo isset($statusMap[$history['status']]) ? $statusMap[$history['status']] : 'Đang xác nhận thanh toán'; ?></span></td>
                        <td><?php echo $history['note'] ?? ''; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <div class="footer">
            <p>Cảm ơn quý khách đã đặt hàng tại nhà hàng chúng tôi!</p>
            <p>Mọi thắc mắc xin vui lòng liên hệ hotline: 0344883755</p>
        </div>
        
        <div class="print-button">
            <button onclick="window.print()"><i class="fas fa-print"></i> In hóa đơn</button>
            <button onclick="window.close()">Đóng</button>
        </div>
    </div>

    <script>
        // Tự động in khi trang tải xong
        window.onload = function() {
            // Sau 1 giây để trang load hết
            setTimeout(function() {
                window.print();
            }, 1000);
        };
    </script>
</body>
</html>