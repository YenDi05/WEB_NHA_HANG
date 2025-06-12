<?php
// Ẩn lỗi PHP để tránh trả về HTML thay vì JSON
error_reporting(0);
ini_set('display_errors', 0);
// Log lỗi thay vì hiển thị
ini_set('log_errors', 1);
ini_set('error_log', 'momo_payment_errors.log');

// Kết nối tới CSDL web_nha_hang
$conn = new mysqli("localhost", "root", "", "web_nha_hang");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Kết nối thất bại: ' . $conn->connect_error]);
    exit;
}
mysqli_set_charset($conn, "utf8");

// Nhận dữ liệu đơn hàng
$customer_name = $_POST['customer_name'] ?? '';
$customer_phone = $_POST['customer_phone'] ?? '';
$customer_address = $_POST['customer_address'] ?? '';
$customer_email = $_POST['customer_email'] ?? '';
$order_note = $_POST['order_note'] ?? '';
$cart_data = $_POST['cart_data'] ?? '';
$total_amount = floatval($_POST['total_amount'] ?? 0); // Giá trị sản phẩm, không bao gồm phí ship
$shipping_fee = 30000; // Phí ship cố định 30.000đ


// Kiểm tra dữ liệu
if (empty($customer_name) || empty($customer_phone) || empty($customer_address) || empty($cart_data)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin bắt buộc']);
    exit;
}

if ($total_amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Tổng tiền không hợp lệ']);
    exit;
}

// Tạo mã đơn hàng
$order_code = 'DH' . date('dmy') . rand(1000, 9999);

// Bắt đầu transaction
mysqli_begin_transaction($conn);

try {
    // Tính toán tổng tiền sản phẩm (chuyển đổi sang đơn vị đồng)
    $product_amount = $total_amount * 1000;
    
    // Tổng thanh toán = giá trị sản phẩm + phí ship
    $total_payment = $product_amount + $shipping_fee;
    
    // Lưu thông tin đơn hàng vào database
    $sql = "INSERT INTO orders (order_code, order_date, customer_name, customer_phone, customer_email, customer_address, total_amount, shipping_fee, notes, status, payment_method) 
            VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?, 'pending', 'momo')";
    
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception("Lỗi chuẩn bị SQL (orders): " . mysqli_error($conn));
    }
    
     // Lưu tổng giá trị sản phẩm vào total_amount
     mysqli_stmt_bind_param($stmt, 'sssssids', $order_code, $customer_name, $customer_phone, $customer_email, $customer_address, $product_amount, $shipping_fee, $order_note);
    $result = mysqli_stmt_execute($stmt);
    if (!$result) {
        throw new Exception("Lỗi thực thi SQL (orders): " . mysqli_stmt_error($stmt));
    }
    
    // Lấy order_id vừa thêm
    $order_id = mysqli_insert_id($conn);
    
    // Thêm vào bảng order_details
    $cart = json_decode($cart_data, true);
    if (!is_array($cart)) {
        throw new Exception("Dữ liệu giỏ hàng không hợp lệ");
    }
    
    foreach ($cart as $item) {
        $product_id = intval($item['id']);
        $quantity = intval($item['quantity']);
        $price = floatval($item['price']) * 1000; // Chuyển về đồng
        $subtotal = $price * $quantity;
        
        $sql = "INSERT INTO order_details (order_id, product_id, quantity, price, subtotal) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Lỗi chuẩn bị SQL (chi tiết): " . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt, 'iiddd', $order_id, $product_id, $quantity, $price, $subtotal);
        
        $result = mysqli_stmt_execute($stmt);
        if (!$result) {
            throw new Exception("Lỗi thực thi SQL (chi tiết): " . mysqli_stmt_error($stmt));
        }
    }
    
    // Thêm vào bảng order_status_history
    $sql = "INSERT INTO order_status_history (order_id, status, notes, updated_by) 
            VALUES (?, 'pending', 'Đơn hàng đang chờ thanh toán qua MoMo', 'system')";
    
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception("Lỗi chuẩn bị SQL (lịch sử): " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, 'i', $order_id);
    
    $result = mysqli_stmt_execute($stmt);
    if (!$result) {
        throw new Exception("Lỗi thực thi SQL (lịch sử): " . mysqli_stmt_error($stmt));
    }
    
    // Commit transaction
    mysqli_commit($conn);
    
// Trả về thông tin để hiển thị trang thanh toán QR
echo json_encode([
    'success' => true,
    'order_code' => $order_code,
    'total_amount' => $total_payment, // Sửa từ $amount thành $total_payment
    'redirect' => 'momo_qr_page.php?order_code=' . $order_code
]);
    
} catch (Exception $e) {
    // Rollback nếu có lỗi
    mysqli_rollback($conn);
    echo json_encode(['success' => false, 'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()]);
} finally {
    // Đóng kết nối
    mysqli_close($conn);
}
?>