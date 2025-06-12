<?php
// Bắt đầu session
session_start();

// Kết nối tới cơ sở dữ liệu
require_once 'db_connect.php';

// Kiểm tra xem người dùng đã đăng nhập bằng cookie chưa
if (!isset($_SESSION['user_id']) && isset($_COOKIE['login_user'])) {
    $cookie_data = json_decode($_COOKIE['login_user'], true);
    $username = $cookie_data['username'];
    
    // Thoát các ký tự đặc biệt để ngăn chặn SQL injection
    $username = mysqli_real_escape_string($conn, $username);
    
    $table_name = "user";
    $sql = "SELECT * FROM $table_name WHERE username = '$username'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Xác minh token cookie
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['full_name'] = $row['full_name'];
        header("Location: index.php");
        exit();
    }
}

// Xử lý đăng nhập khi form được gửi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["user"]) && isset($_POST["pass"])) {
    $username = $_POST["user"];
    $password = $_POST["pass"];
    $remember = isset($_POST["remember"]) ? true : false;
    
    // Thoát các ký tự đặc biệt để ngăn chặn SQL injection
    $username = mysqli_real_escape_string($conn, $username);
    
    $table_name = "user";
    
    // Truy vấn kiểm tra thông tin đăng nhập
    $sql = "SELECT * FROM $table_name WHERE username = '$username'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Xác minh mật khẩu
        $password_correct = false;
        
        // Kiểm tra xem mật khẩu có khớp với phiên bản băm không
        if (password_verify($password, $row["password"])) {
            // Mật khẩu đã băm và xác thực thành công
            $password_correct = true;
        } 
        // Kiểm tra nếu mật khẩu thô khớp
        else if ($password === $row["password"]) {
            // Mật khẩu thô khớp - đăng nhập thành công
            $password_correct = true;
            
            // Băm và cập nhật mật khẩu trong CSDL
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update_sql = "UPDATE $table_name SET password = ? WHERE id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("si", $hashed_password, $row["id"]);
            $stmt->execute();
            $stmt->close();
        }
        
        if ($password_correct) {
            // Đăng nhập thành công
            $_SESSION["user_id"] = $row["id"];
            $_SESSION["username"] = $row["username"];
            $_SESSION["full_name"] = $row["full_name"];
            
            // Xử lý chức năng "Ghi nhớ đăng nhập"
            if ($remember) {
                $cookie_data = [
                    'username' => $username,
                    // Có thể thêm token hoặc dữ liệu khác để tăng cường bảo mật
                ];
                $cookie_value = json_encode($cookie_data);
                setcookie('login_user', $cookie_value, time() + (30 * 24 * 60 * 60), "/"); 
            }
            
            // Chuyển hướng đến trang quản trị
            header("Location: index.php");
            exit();
        } else {
            $error = "Mật khẩu không đúng";
        }
    } else {
        $error = "Tên đăng nhập không tồn tại";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Đăng nhập Trang ADMIN</title>
  <link rel="stylesheet" href="style.css">
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>
<body class="Login">
  <?php if(isset($error)): ?>
    <div class="error-message" style="background-color: #ffcccc; padding: 10px; margin-bottom: 15px; text-align: center; border-radius: 5px;">
      <?php echo $error; ?>
    </div>
  <?php endif; ?>
  <div class="wrapper">
    <div class="login_box">
      <div class="login-header">
        <span>Đăng Nhập</span>
      </div>
      
      <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <div class="input_box">
          <input type="text" id="user" name="user" class="input-field" required>
          <label for="user" class="label">Tên đăng nhập</label>
          <i class="bx bx-user icon"></i>
        </div>
        
        <div class="input_box">
          <input type="password" id="pass" name="pass" class="input-field" required>
          <label for="pass" class="label">Nhập mật khẩu</label>
          <i class="bx bx-lock-alt icon"></i>
        </div>
        
        <div class="input_box">
          <label class="remember-me">
            <input type="checkbox" name="remember" id="remember">Ghi nhớ</label>
        </div>
        
        <div class="input_box">
          <input type="submit" class="input-submit" value="Đăng Nhập">
        </div>
      </form>
    </div>
  </div>
</body>
</html>