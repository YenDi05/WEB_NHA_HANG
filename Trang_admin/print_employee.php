<?php
// Kết nối tới CSDL
require_once 'db_connect.php';

// Kiểm tra id nhân viên
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: employees.php');
    exit;
}

$employeeId = intval($_GET['id']);

// Lấy thông tin nhân viên
$sql = "SELECT * FROM employees WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employeeId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: employees.php?error=notfound');
    exit;
}

$employee = $result->fetch_assoc();
$stmt->close();

// Format dữ liệu
$hireDate = date('d/m/Y', strtotime($employee['hire_date']));
$salary = number_format($employee['basic_salary'], 0, ',', '.') . ' VNĐ';
$status = $employee['status'] === 'active' ? 'Đang làm việc' : 'Đã nghỉ việc';

// Lấy ngày hiện tại
$currentDate = date('d/m/Y');
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In thông tin nhân viên - <?php echo $employee['full_name']; ?></title>
    <style>
        @media print {
            body {
                font-family: 'Arial', sans-serif;
                margin: 0;
                padding: 20px;
                color: #333;
            }
            
            .no-print {
                display: none !important;
            }
            
            @page {
                size: A4;
                margin: 15mm;
            }
        }
        
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            background-color: #f9f9f9;
        }
        
        .print-container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        
        .header h1 {
            font-size: 24px;
            margin: 0 0 5px 0;
        }
        
        .header p {
            font-size: 14px;
            margin: 5px 0;
        }
        
        .company-info {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .company-info h2 {
            font-size: 18px;
            margin: 0 0 5px 0;
        }
        
        .employee-title {
            font-size: 20px;
            text-align: center;
            margin: 20px 0;
            font-weight: bold;
        }
        
        .info-section {
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 18px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 12px;
        }
        
        .info-label {
            width: 200px;
            font-weight: bold;
        }
        
        .info-value {
            flex: 1;
        }
        
        .signature-section {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-box {
            width: 45%;
            text-align: center;
        }
        
        .signature-title {
            font-weight: bold;
            margin-bottom: 60px;
        }
        
        .print-date {
            text-align: right;
            font-style: italic;
            margin-top: 20px;
            font-size: 14px;
        }
        
        .print-button {
            text-align: center;
            margin-top: 30px;
        }
        
        .print-button button {
            background-color: #3f7b69;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin: 0 5px;
        }
        
        .print-button button:hover {
            background-color: #346857;
        }
    </style>
</head>
<body>
    <div class="print-container">
        <div class="header">
            <h1>THÔNG TIN NHÂN VIÊN</h1>
            <p>Mã nhân viên: NV<?php echo str_pad($employee['id'], 4, '0', STR_PAD_LEFT); ?></p>
        </div>
        
        <div class="company-info">
            <h2>Nhà hàng Ẩm Thực Việt</h2>
            <p>Địa chỉ: 19/46 Tân Chánh Hiệp, Q.12, TP. Hồ Chí Minh</p>
            <p>SĐT: 0344883755 | Email: giakhanhngo1503@gmail.com</p>
        </div>
        
        <div class="employee-title">PHIẾU THÔNG TIN NHÂN VIÊN</div>
        
        <div class="info-section">
            <div class="section-title">THÔNG TIN CÁ NHÂN</div>
            
            <div class="info-row">
                <div class="info-label">Họ và tên:</div>
                <div class="info-value"><?php echo htmlspecialchars($employee['full_name']); ?></div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Số điện thoại:</div>
                <div class="info-value"><?php echo htmlspecialchars($employee['phone']); ?></div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Email:</div>
                <div class="info-value"><?php echo htmlspecialchars($employee['email'] ?: 'Không có'); ?></div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Địa chỉ:</div>
                <div class="info-value"><?php echo htmlspecialchars($employee['address'] ?: 'Không có'); ?></div>
            </div>
        </div>
        
        <div class="info-section">
            <div class="section-title">THÔNG TIN CÔNG VIỆC</div>
            
            <div class="info-row">
                <div class="info-label">Vị trí:</div>
                <div class="info-value"><?php echo htmlspecialchars($employee['position']); ?></div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Ngày bắt đầu:</div>
                <div class="info-value"><?php echo $hireDate; ?></div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Lương cơ bản:</div>
                <div class="info-value"><?php echo $salary; ?></div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Trạng thái:</div>
                <div class="info-value"><?php echo $status; ?></div>
            </div>
        </div>
        
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-title">Người lập phiếu</div>
                <div class="signature-name">.........................</div>
            </div>
            
            <div class="signature-box">
                <div class="signature-title">Nhân viên</div>
                <div class="signature-name">.........................</div>
            </div>
        </div>
        
        <div class="print-date">
            Ngày in: <?php echo $currentDate; ?>
        </div>
    </div>
    
    <div class="print-button no-print">
        <button onclick="window.print()">In Thông Tin</button>
        <button onclick="window.location.href='employees.php'">Quay Lại</button>
    </div>
    
    <script>
        // Tự động hiển thị hộp thoại in khi trang được tải
        window.onload = function() {
            // Chờ 1 giây trước khi hiển thị hộp thoại in
            setTimeout(function() {
                window.print();
            }, 1000);
        };
    </script>
</body>
</html>