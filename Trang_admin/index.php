<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ Admin</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php include 'menu.php'; ?>

<?php
// Kết nối database
require_once 'db_connect.php';

// Lấy thống kê hóa đơn tháng hiện tại
$sql_orders = "SELECT COUNT(*) as total_orders FROM orders WHERE MONTH(order_date) = MONTH(CURRENT_DATE())";
$sql_orders_prev = "SELECT COUNT(*) as prev_orders FROM orders WHERE MONTH(order_date) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH)";

$result_orders = $conn->query($sql_orders);
$result_orders_prev = $conn->query($sql_orders_prev);

$orders_count = $result_orders->fetch_assoc()['total_orders'];
$prev_orders_count = $result_orders_prev->fetch_assoc()['prev_orders'];

$orders_percentage = 0;
if ($prev_orders_count > 0) {
    $orders_percentage = round((($orders_count - $prev_orders_count) / $prev_orders_count) * 100);
}

// Lấy thống kê doanh thu tháng hiện tại
$sql_revenue = "SELECT SUM(total_amount) as total_revenue FROM orders WHERE MONTH(order_date) = MONTH(CURRENT_DATE()) AND status != 'cancelled'";
$sql_revenue_prev = "SELECT SUM(total_amount) as prev_revenue FROM orders WHERE MONTH(order_date) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH) AND status != 'cancelled'";

$result_revenue = $conn->query($sql_revenue);
$result_revenue_prev = $conn->query($sql_revenue_prev);

$revenue = $result_revenue->fetch_assoc()['total_revenue'] ?: 0;
$prev_revenue = $result_revenue_prev->fetch_assoc()['prev_revenue'] ?: 0;

$revenue_percentage = 0;
if ($prev_revenue > 0) {
    $revenue_percentage = round((($revenue - $prev_revenue) / $prev_revenue) * 100);
}

// Lấy thống kê khách hàng (dựa trên khách hàng độc nhất theo số điện thoại)
$sql_customers = "SELECT COUNT(DISTINCT customer_phone) as total_customers FROM orders WHERE MONTH(order_date) = MONTH(CURRENT_DATE())";
$sql_customers_prev = "SELECT COUNT(DISTINCT customer_phone) as prev_customers FROM orders WHERE MONTH(order_date) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH)";

$result_customers = $conn->query($sql_customers);
$result_customers_prev = $conn->query($sql_customers_prev);

$customers_count = $result_customers->fetch_assoc()['total_customers'];
$prev_customers_count = $result_customers_prev->fetch_assoc()['prev_customers'];

$customers_percentage = 0;
if ($prev_customers_count > 0) {
    $customers_percentage = round((($customers_count - $prev_customers_count) / $prev_customers_count) * 100);
}

// Lấy dữ liệu cho biểu đồ doanh thu 10 ngày gần đây
$sql_chart = "SELECT DATE(order_date) as date, SUM(total_amount) as revenue 
              FROM orders 
              WHERE order_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 10 DAY)
              AND status != 'cancelled'
              GROUP BY DATE(order_date) 
              ORDER BY date";

$result_chart = $conn->query($sql_chart);
$chart_dates = [];
$chart_revenue = [];

while ($row = $result_chart->fetch_assoc()) {
    $chart_dates[] = date('d/m', strtotime($row['date']));
    $chart_revenue[] = $row['revenue'];
}

// Lấy thống kê top 5 sản phẩm bán chạy
$sql_top_products = "SELECT p.name, SUM(od.quantity) as total_sold
                    FROM order_details od
                    JOIN product p ON od.product_id = p.product_id
                    JOIN orders o ON od.order_id = o.order_id
                    WHERE o.status != 'cancelled'
                    AND MONTH(o.order_date) = MONTH(CURRENT_DATE())
                    GROUP BY od.product_id
                    ORDER BY total_sold DESC
                    LIMIT 5";

$result_top_products = $conn->query($sql_top_products);
$top_products = [];
$top_products_qty = [];

while ($row = $result_top_products->fetch_assoc()) {
    $top_products[] = $row['name'];
    $top_products_qty[] = $row['total_sold'];
}

$conn->close();
?>

<div class="content">
    <h1>Thống Kê & Báo Cáo</h1>
    <div class="container">
        <div class="info-box orders">
            <div class="info-box-content">
                <i class="fas fa-file-invoice icon"></i>
                <h3>Hóa đơn</h3>
                <div class="value"><?php echo number_format($orders_count); ?></div>
                <div class="percentage <?php echo ($orders_percentage >= 0) ? 'positive' : 'negative'; ?>">
                    <i class="fas fa-<?php echo ($orders_percentage >= 0) ? 'arrow-up' : 'arrow-down'; ?>"></i>
                    <?php echo abs($orders_percentage); ?>%
                </div>
            </div>
        </div>
        
        <div class="info-box revenue">
            <div class="info-box-content">
                <i class="fas fa-dollar-sign icon"></i>
                <h3>Doanh thu</h3>
                <div class="value"><?php echo number_format($revenue/1000, 0, ',', '.'); ?>K</div>
                <div class="percentage <?php echo ($revenue_percentage >= 0) ? 'positive' : 'negative'; ?>">
                    <i class="fas fa-<?php echo ($revenue_percentage >= 0) ? 'arrow-up' : 'arrow-down'; ?>"></i>
                    <?php echo abs($revenue_percentage); ?>%
                </div>
            </div>
        </div>
        
        <div class="info-box customers">
            <div class="info-box-content">
                <i class="fas fa-users icon"></i>
                <h3>Khách hàng</h3>
                <div class="value"><?php echo number_format($customers_count); ?></div>
                <div class="percentage <?php echo ($customers_percentage >= 0) ? 'positive' : 'negative'; ?>">
                    <i class="fas fa-<?php echo ($customers_percentage >= 0) ? 'arrow-up' : 'arrow-down'; ?>"></i>
                    <?php echo abs($customers_percentage); ?>%
                </div>
            </div>
        </div>
    </div>
    
    <div class="charts-container">
        <div class="chart revenue-chart">
            <h2>Doanh thu 10 ngày gần đây</h2>
            <canvas id="revenueChart"></canvas>
        </div>
        
        <div class="chart products-chart">
            <h2>Top 5 sản phẩm bán chạy</h2>
            <canvas id="productsChart"></canvas>
        </div>
    </div>
</div>

<script src="https://unpkg.com/chart.js@4.3.0/dist/chart.umd.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Biểu đồ doanh thu
    const ctxRevenue = document.getElementById('revenueChart').getContext('2d');
    
    const revenueData = {
        labels: <?php echo json_encode($chart_dates); ?>,
        datasets: [{
            label: 'Doanh thu (nghìn đồng)',
            data: <?php echo json_encode($chart_revenue); ?>,
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1,
            tension: 0.3
        }]
    };
    
    const revenueConfig = {
        type: 'line',
        data: revenueData,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + new Intl.NumberFormat('vi-VN').format(context.parsed.y) + 'K';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            const valueInThousands = value / 1000;
                        return new Intl.NumberFormat('vi-VN').format(valueInThousands) + 'K';
                        }
                    }
                }
            }
        }
    };
    
    new Chart(ctxRevenue, revenueConfig);
    
    // Biểu đồ sản phẩm bán chạy
    const ctxProducts = document.getElementById('productsChart').getContext('2d');
    
    const productsData = {
        labels: <?php echo json_encode($top_products); ?>,
        datasets: [{
            label: 'Số lượng bán ra',
            data: <?php echo json_encode($top_products_qty); ?>,
            backgroundColor: [
                'rgba(255, 99, 132, 0.7)',
                'rgba(54, 162, 235, 0.7)',
                'rgba(255, 206, 86, 0.7)',
                'rgba(75, 192, 192, 0.7)',
                'rgba(153, 102, 255, 0.7)'
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)'
            ],
            borderWidth: 1
        }]
    };
    
    const productsConfig = {
        type: 'bar',
        data: productsData,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    };
    
    new Chart(ctxProducts, productsConfig);
});
</script>


</body>
</html>