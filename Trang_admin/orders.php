<?php
// Kết nối tới CSDL web_nha_hang
require_once 'db_connect.php';


// Lấy thống kê đơn hàng
$statsSql = "SELECT 
    SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_orders,
    SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing_orders,
    SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders
    FROM orders";
$statsResult = mysqli_query($conn, $statsSql);
$stats = mysqli_fetch_assoc($statsResult);

// Xác định bộ lọc
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$dateFilter = isset($_GET['date']) ? $_GET['date'] : 'all';

// Xây dựng câu truy vấn với điều kiện lọc
$sql = "SELECT * FROM orders WHERE 1=1";


// Thêm điều kiện tìm kiếm
if (!empty($searchTerm)) {
    $searchTerm = mysqli_real_escape_string($conn, $searchTerm);
    $sql .= " AND (order_code LIKE '%$searchTerm%' 
               OR customer_name LIKE '%$searchTerm%' 
               OR customer_phone LIKE '%$searchTerm%'
               OR total_amount LIKE '%$searchTerm%')";
}

// Thêm điều kiện trạng thái
if ($statusFilter != 'all') { 
    $sql .= " AND status = '$statusFilter'";
}


// Thêm điều kiện ngày tháng
if ($dateFilter != 'all') {
    switch($dateFilter) {
        case 'today':
            $sql .= " AND DATE(order_date) = CURDATE()";
            break;
        case 'week':
            $sql .= " AND WEEK(order_date) = WEEK(CURDATE())";
            break;
        case 'month':
            $sql .= " AND MONTH(order_date) = MONTH(CURDATE())";
            break;
    }
}

$sql .= " ORDER BY order_date DESC";

// Thực hiện truy vấn
$result = mysqli_query($conn, $sql);

// Đếm tổng số đơn hàng để phân trang
$totalOrdersQuery = preg_replace('/SELECT \* FROM orders/i', 'SELECT COUNT(*) as total FROM orders', $sql);
$totalOrdersResult = mysqli_query($conn, $totalOrdersQuery);
$totalOrders = mysqli_fetch_assoc($totalOrdersResult)['total'];


// Thiết lập phân trang
$ordersPerPage = 10;
$totalPages = ceil($totalOrders / $ordersPerPage);
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $ordersPerPage;

// Truy vấn với limit và offset
$sql .= " LIMIT $offset, $ordersPerPage";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Đơn hàng</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php include 'menu.php'; ?>
        
        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <h1><i class="fas fa-shopping-cart"></i> Quản lý Đơn hàng</h1>
                <button class="btn-primary" onclick="window.location.href='add_order.php'"><i class="fas fa-plus"></i> Tạo đơn mới</button>
            </div>
            
            <!-- Order Stats -->
            <div class="order-stats">
                <div class="stat-card">
                    <div class="stat-icon new">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Đơn mới</h3>
                        <div class="stat-value"><?php echo $stats['new_orders']; ?></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon processing">
                        <i class="fas fa-spinner"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Đang xử lý</h3>
                        <div class="stat-value"><?php echo $stats['processing_orders']; ?></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon delivered">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Đã giao</h3>
                        <div class="stat-value"><?php echo $stats['delivered_orders']; ?></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon cancelled">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Đã hủy</h3>
                        <div class="stat-value"><?php echo $stats['cancelled_orders']; ?></div>
                    </div>
                </div>
            </div>
            
           <!-- Order Filter -->
<div class="filter-container">
    <div class="search-box">
        <form method="GET" action="">
            <button type="submit"><i class="fas fa-search"></i></button>
            <input type="text" name="search" placeholder="Tìm kiếm đơn hàng..." value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
            
            <!-- Thêm các tham số khác để giữ nguyên các bộ lọc hiện tại khi tìm kiếm -->
            <?php if(isset($_GET['status']) && $_GET['status'] != ''): ?>
            <input type="hidden" name="status" value="<?php echo $_GET['status']; ?>">
            <?php endif; ?>
            
            <?php if(isset($_GET['date']) && $_GET['date'] != ''): ?>
            <input type="hidden" name="date" value="<?php echo $_GET['date']; ?>">
            <?php endif; ?>
            
            <?php if(isset($_GET['page']) && $_GET['page'] != ''): ?>
            <input type="hidden" name="page" value="<?php echo $_GET['page']; ?>">
            <?php endif; ?>
        </form>
    </div>
    
    <div class="filter-options">
        <form method="GET" action="" id="orderFilterForm">
            <!-- Lưu giữ tham số search khi thay đổi bộ lọc khác -->
            <input type="hidden" name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            
            <select name="status" onchange="document.getElementById('orderFilterForm').submit()">
                <option value="all" <?php if($statusFilter == 'all') echo 'selected'; ?>>Tất cả trạng thái</option>
                <option value="new" <?php if($statusFilter == 'new') echo 'selected'; ?>>Mới</option>
                <option value="processing" <?php if($statusFilter == 'processing') echo 'selected'; ?>>Đang xử lý</option>
                <option value="delivered" <?php if($statusFilter == 'delivered') echo 'selected'; ?>>Đã giao</option>
                <option value="cancelled" <?php if($statusFilter == 'cancelled') echo 'selected'; ?>>Đã hủy</option>
            </select>
            
            <select name="date" onchange="document.getElementById('orderFilterForm').submit()">
                <option value="all" <?php if($dateFilter == 'all') echo 'selected'; ?>>Tất cả</option>
                <option value="today" <?php if($dateFilter == 'today') echo 'selected'; ?>>Hôm nay</option>
                <option value="week" <?php if($dateFilter == 'week') echo 'selected'; ?>>Tuần này</option>
                <option value="month" <?php if($dateFilter == 'month') echo 'selected'; ?>>Tháng này</option>
            </select>
            
            <!-- Lưu giữ tham số page khi thay đổi bộ lọc -->
            <?php if(isset($_GET['page'])): ?>
            <input type="hidden" name="page" value="<?php echo $_GET['page']; ?>">
            <?php endif; ?>
        </form>
    </div>
</div>
            <!-- Orders Table -->
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Thời gian</th>
                            <th>Khách hàng</th>
                            <th>Số điện thoại</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if (mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                                // Ánh xạ trạng thái từ tiếng Anh sang tiếng Việt
                                $statusMap = [
                                    'new' => 'Mới',
                                    'processing' => 'Đang xử lý',
                                    'shipping' => 'Đang giao',
                                    'delivered' => 'Đã giao',
                                    'cancelled' => 'Đã hủy'
                                ];
                                
                                $statusClass = $row['status'];
                                $statusText = isset($statusMap[$row['status']]) ? $statusMap[$row['status']] : $row['status'];
                        ?>
                        <tr>
                            <td><?php echo $row['order_code']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['order_date'])); ?></td>
                            <td><?php echo $row['customer_name']; ?></td>
                            <td><?php echo $row['customer_phone']; ?></td>
                            <td><?php echo number_format($row['total_amount']/1000, 0, ',', '.') . 'K'; ?></td>
                            <td><span class="status <?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
                            <td class="actions">
                                <button class="btn-view" title="Xem chi tiết" onclick="viewOrderDetails(<?php echo $row['order_id']; ?>)"><i class="fas fa-eye"></i></button>
                                <?php if($row['status'] != 'delivered' && $row['status'] != 'cancelled') { ?>
                                <button class="btn-edit" title="Cập nhật" onclick="window.location.href='edit_order.php?id=<?php echo $row['order_id']; ?>'"><i class="fas fa-edit"></i></button>
                                <button class="btn-delete" title="Hủy đơn" onclick="cancelOrder(<?php echo $row['order_id']; ?>)"><i class="fas fa-trash"></i></button>
                                <?php } else if($row['status'] == 'delivered') { ?>
                                <button class="btn-print" title="In hóa đơn" onclick="printOrder(<?php echo $row['order_id']; ?>)"><i class="fas fa-print"></i></button>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php 
                            }
                        } else {
                            echo "<tr><td colspan='7' class='text-center'>Không có đơn hàng nào</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
                
               
                <div class="pagination">
                    <button class="prev-page" <?php if($currentPage <= 1) echo 'disabled'; ?> 
                            onclick="window.location.href='?page=<?php echo $currentPage-1; ?>&status=<?php echo $statusFilter; ?>&date=<?php echo $dateFilter; ?>&search=<?php echo htmlspecialchars($searchTerm); ?>'">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <span class="page-indicator">Trang <?php echo $currentPage; ?> / <?php echo max(1, $totalPages); ?></span>
                    <button class="next-page" <?php if($currentPage >= $totalPages) echo 'disabled'; ?> 
                            onclick="window.location.href='?page=<?php echo $currentPage+1; ?>&status=<?php echo $statusFilter; ?>&date=<?php echo $dateFilter; ?>&search=<?php echo htmlspecialchars($searchTerm); ?>'">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </main>
    
    
    <!-- Order Detail Modal (Hidden by default) -->
    <div class="modal" id="orderDetailModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalOrderTitle">Chi tiết đơn hàng</h2>
                <button class="close-modal" onclick="closeModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body" id="modalContent">
                <!-- Content will be loaded via AJAX -->
                <div class="loading">Đang tải...</div>
            </div>
        </div>
    </div>


</body>
<script src="https://unpkg.com/chart.js@4.3.0/dist/chart.umd.js"></script>
<script src="script.js"></script>
</html>