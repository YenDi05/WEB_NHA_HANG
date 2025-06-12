<?php
// Kết nối tới CSDL web_nha_hang
$conn = new mysqli("localhost", "root", "", "web_nha_hang");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
// Đặt charset
mysqli_set_charset($conn, "utf8");

// Lấy danh sách sản phẩm
$sql = "SELECT * FROM product WHERE status = 1";
$result = mysqli_query($conn, $sql);

$products = array();
if(mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
}

// Xử lý tìm kiếm
$search_results = array();
if(isset($_GET['search']) && !empty($_GET['search'])) {
    $keyword = mysqli_real_escape_string($conn, $_GET['search']);
    $search_sql = "SELECT * FROM product WHERE (name LIKE '%$keyword%' OR description LIKE '%$keyword%') AND status = 1";
    $search_result = mysqli_query($conn, $search_sql);
    
    if(mysqli_num_rows($search_result) > 0) {
        while($row = mysqli_fetch_assoc($search_result)) {
            $search_results[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhà hàng ẩm thực Việt</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <!-- font awesome cdn  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- file css  -->
    <link rel="stylesheet" href="style.css">
    

</head>
<body>

    <!-- header section starts  -->
    <header>
        <a href="#" class="logo"><img src="image/logo.png" alt=""></a>

        <nav class="navbar">
            <a class="active" href="#home">Trang chủ</a>
            <a href="#dishes">Bán chạy</a>
            <a href="#about">Giới thiệu</a>
            <a href="#menu">Thực đơn</a>
            <a href="#review">Đầu bếp</a>
            <a href="#oder">Đặt món</a>
        </nav>

        <div class="icons">
            <i class="fas fa-bars" id="menu-bars"></i>
            <i class="fas fa-search" id="search-icon"></i>
            <a href="#" class="fas fa-shopping-cart" id="cart-icon"></a>
        </div>
    </header>
    <!-- header section ends -->

    <!-- thanh tim kiem -->
    <form action="" method="GET" id="search-form">
        <input type="search" placeholder="Tìm kiếm món ăn..." name="search" id="search-box">
        <label for="search-box" class="fas fa-search"></label>
        <i class="fas fa-times" id="close"></i>
    </form> 

    <!-- Popup giỏ hàng -->
    <div id="cart-popup" class="cart-popup">
        <div class="cart-popup-content">
            <div class="cart-popup-header">
                <h3>Giỏ hàng của bạn</h3>
                <i class="fas fa-times" id="close-cart"></i>
            </div>
            <div class="cart-popup-body">
                <!-- Sẽ được điền bởi JavaScript -->
            </div>
            <div class="cart-popup-footer">
                <div class="cart-total">
                    <p>Tổng cộng: <span id="cart-popup-total">0k</span></p>
                </div>
                <div class="cart-actions">
                    <a href="#oder" class="btn" onclick="hideCartPopup()">Thanh toán ngay</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Kết quả tìm kiếm nếu có -->
    <?php if(!empty($search_results)): ?>
    <section class="search-results">
        <h3 class="sub-heading">Kết quả tìm kiếm</h3>
        <h1 class="heading">Món ăn phù hợp với "<?php echo htmlspecialchars($_GET['search']); ?>"</h1>
        
        <div class="box-container">
            <?php foreach($search_results as $product): ?>
            <div class="box">
                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                <span><?php echo number_format($product['price'], 0, ',', '.'); ?>k</span>
                <a href="#" class="btn add-to-cart" 
                   data-id="<?php echo $product['product_id']; ?>" 
                   data-name="<?php echo htmlspecialchars($product['name']); ?>" 
                   data-price="<?php echo $product['price']; ?>"
                   data-image="<?php echo htmlspecialchars($product['image']); ?>">Đặt món</a>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- home section starts -->
    <section class="home" id="home">
        <div class="swiper home-slider">
            <div class="swiper-wrapper">
                <div class="swiper-slide">
                    <div class="content">
                        <span>Món tủ của Nhà Hàng</span>
                        <h3>Phở bò Hà Nội</h3>
                        <p>Phở bò là món ăn truyền thống của người Việt Nam, nổi tiếng với hương vị đậm đà và nước dùng thơm ngon.</p>
                        <a href="#" class="btn add-to-cart" 
                           data-id="28" 
                           data-name="Phở bò Hà Nội" 
                           data-price="55"
                           data-image="image/pho.jpg">Đặt món</a>
                    </div>
                    <div class="image">
                        <img src="image/pho.jpg" alt="" /> 
                    </div>
                </div>

                <div class="swiper-slide">
                    <div class="content">
                        <span>Món tủ của Nhà Hàng</span>
                        <h3>Bún bò Huế</h3>
                        <p>Hương vị đậm đà, cay nồng đặc trưng của miền Trung với nước dùng ngọt xương, sả, mắm ruốc và thịt bò chả cua.</p>
                        <a href="#" class="btn add-to-cart" 
                           data-id="5" 
                           data-name="Bún bò Huế" 
                           data-price="60"
                           data-image="image/bunbo.jpg">Đặt món</a>
                    </div>
                    <div class="image">
                        <img src="image/bunbo.jpg" alt="" />
                    </div>
                </div>

                <div class="swiper-slide">
                    <div class="content">
                        <span>Món tủ của Nhà Hàng</span>
                        <h3>Cơm tấm Sài Gòn</h3>
                        <p>Một món ăn bình dân nhưng rất đặc trưng của Sài Gòn với cơm tấm dẻo thơm, sườn nướng vàng ươm, chả trứng và bì heo dai ngon.</p>
                        <a href="#" class="btn add-to-cart" 
                           data-id="23" 
                           data-name="Cơm tấm Sườn Bì Chả" 
                           data-price="55"
                           data-image="image/comtam.jpg">Đặt món</a>
                    </div>
                    <div class="image">
                        <img src="image/comtam.jpg" alt="" />
                    </div>
                </div>
            </div>
            <div class="swiper-pagination"></div>
        </div>
    </section>
    <!-- home section ends -->

    <!-- dishes section starts -->
    <section class="dishes" id="dishes">
        <h3 class="sub-heading">Hương vị truyền thống</h3>
        <h1 class="heading">Món ăn nổi bật</h1>
        
        <div class="box-container">
            <?php 
            // Hiển thị 16 món ăn nổi bật
            $featured_dishes = array_slice($products, 0, 16);
            foreach($featured_dishes as $product): 
            ?>
            <div class="box">
                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                <span><?php echo number_format($product['price'],0, ',', '.'); ?>k</span>
                <a href="#" class="btn add-to-cart" 
                   data-id="<?php echo $product['product_id']; ?>" 
                   data-name="<?php echo htmlspecialchars($product['name']); ?>" 
                   data-price="<?php echo $product['price']; ?>"
                   data-image="<?php echo htmlspecialchars($product['image']); ?>">Đặt món</a>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <!-- dishes section ends -->

    <!-- about section starts -->
    <section class="about" id="about">
        <h3 class="sub-heading">Về chúng tôi</h3>
        <h1 class="heading">Tại sao chọn Nhà hàng Ẩm thực Việt?</h1>
    
        <div class="row">
            <div class="image">
                <img src="image/amthucviet.jpg" alt="Ẩm thực Việt">
            </div>
    
            <div class="content">
                <h3>Hương vị truyền thống đậm đà bản sắc</h3>
                <p>Nhà hàng Ẩm thực Việt tự hào mang đến những món ăn tinh hoa của đất nước hình chữ S. Từ phở, bún chả đến gỏi cuốn, mỗi món ăn đều được chế biến tỉ mỉ theo công thức truyền thống.</p>
                <p>Với không gian đậm chất Việt, đội ngũ đầu bếp tâm huyết và nguyên liệu tươi ngon được tuyển chọn mỗi ngày, chúng tôi cam kết đem lại trải nghiệm ẩm thực tuyệt vời cho thực khách trong và ngoài nước.</p>
                <div class="icons-container">
                    <div class="icons">
                        <i class="fas fa-utensils"></i>
                        <span>Thực đơn phong phú</span>
                    </div>
                    <div class="icons">
                        <i class="fas fa-leaf"></i>
                        <span>Nguyên liệu tươi sạch</span>
                    </div>
                    <div class="icons">
                        <i class="fas fa-concierge-bell"></i>
                        <span>Phục vụ tận tâm</span>
                    </div>
                </div>
                <a href="#menu" class="btn">Xem Thực Đơn</a>
            </div>
        </div>
    </section>
    <!-- about section ends -->
 
    <!-- menu section starts --> 
    <section class="menu" id="menu">
        <h3 class="sub-heading">Món ngon mỗi ngày</h3>
        <h1 class="heading">Tinh hoa ẩm thực Việt</h1>

        <div class="box-container">
            <?php foreach(array_slice($products, 16) as $product): ?>
            <div class="box">
                <div class="image">
                    <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                </div>
                <div class="content">
                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p><?php echo htmlspecialchars($product['description']); ?></p>
                    <a href="#" class="btn add-to-cart" 
                       data-id="<?php echo $product['product_id']; ?>" 
                       data-name="<?php echo htmlspecialchars($product['name']); ?>" 
                       data-price="<?php echo $product['price']; ?>"
                       data-image="<?php echo htmlspecialchars($product['image']); ?>">Đặt món</a>
                    <span class="price"><?php echo number_format($product['price'], 0, ',', '.'); ?>k</span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <!-- menu section ends  -->

    <!-- review section -->
    <section class="review" id="review">
        <h3 class="sub-heading">Đội ngũ đầu bếp</h3>
        <h1 class="heading">Người giữ hồn món Việt</h1>
    
        <div class="swiper review-slider">
            <div class="swiper-wrapper">
                <div class="swiper-slide">
                    <div class="user">
                        <img src="image/chefvoquoc.jpg" alt="Chef Võ Quốc">
                        <div class="user-info">
                            <h3>Chef Võ Quốc</h3>
                            <div class="stars">
                                <span>Chuyên ẩm thực miền Nam</span>
                            </div>
                        </div>
                    </div>
                    <p>Đầu bếp gắn liền với chương trình "Bếp Việt", nổi tiếng với khả năng bảo tồn và quảng bá món ăn dân dã miền Tây.</p>
                </div>
                
                <div class="swiper-slide">
                    <div class="user">
                        <img src="image/chefhai.jpg" alt="Chef Phan Tôn Tịnh Hải">
                        <div class="user-info">
                            <h3>Phan Tôn Tịnh Hải</h3>
                            <div class="stars">
                                <span>Ẩm thực Huế - Việt</span>
                            </div>
                        </div>
                    </div>
                    <p>Giám khảo MasterChef Việt, giảng viên ẩm thực và chuyên gia nổi tiếng trong việc gìn giữ tinh hoa món ăn cung đình Huế.</p>
                </div>
                
                <div class="swiper-slide">
                    <div class="user">
                        <img src="image/cheftin.jpg" alt="Chef Nguyễn Văn Tín">
                        <div class="user-info">
                            <h3>Nguyễn Văn Tín</h3>
                            <div class="stars">
                                <span>Ẩm thực dân gian Bắc Bộ</span>
                            </div>
                        </div>
                    </div>
                    <p>Nghệ nhân ẩm thực dân gian, nổi bật với khả năng phục dựng các món cổ truyền Bắc Bộ như nem công, chả phượng.</p>
                </div>
                
                <div class="swiper-slide">
                    <div class="user">
                        <img src="image/ChefTung.jpg" alt="Chef Hoàng Tùng">
                        <div class="user-info">
                            <h3>Chef Hoàng Tùng</h3>
                            <div class="stars">
                                <span>Ẩm thực sáng tạo Việt</span>
                            </div>
                        </div>
                    </div>
                    <p>Đầu bếp trẻ sáng lập T.U.N.G Dining, nổi tiếng với việc nâng tầm món Việt theo phong cách fine dining hiện đại.</p>
                </div>
                
                <div class="swiper-slide">
                    <div class="user">
                        <img src="image/chefvy.jpg" alt="Chef Trịnh Diễm Vy">
                        <div class="user-info">
                            <h3>Trịnh Diễm Vy</h3>
                            <div class="stars">
                                <span>Ẩm thực miền Trung</span>
                            </div>
                        </div>
                    </div>
                    <p>Đầu bếp - chủ nhà hàng nổi tiếng tại Hội An, gắn bó với di sản ẩm thực miền Trung, được du khách quốc tế đánh giá cao.</p>
                </div>
                
                <div class="swiper-slide">
                    <div class="user">
                        <img src="image/cheftuanhai.jpg" alt="Chef Phạm Tuấn Hải">
                        <div class="user-info">
                            <h3>Phạm Tuấn Hải</h3>
                            <div class="stars">
                                <span>Ẩm thực Việt hiện đại</span>
                            </div>
                        </div>
                    </div>
                    <p>Giám khảo MasterChef Việt, từng là bếp trưởng khách sạn 5 sao, chuyên kết hợp món Việt với kỹ thuật ẩm thực đương đại.</p>
                </div>
            </div>
        </div>
    </section>
    <!-- review section ends -->

    <!-- order section -->
    <section class="oder" id="oder">
        <h3 class="sub-heading">Đặt món ngay</h3>
        <h1 class="heading">Nhanh chóng & tiện lợi</h1>

        <!-- Bảng giỏ hàng -->
        <div class="order-cart">
            <table>
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Món ăn</th>
                        <th>Giá bán</th>
                        <th>Số lượng</th>
                        <th>Thành tiền</th>
                        <th>Xóa</th>
                    </tr>
                </thead>
                <tbody id="cart-body">
                    <!-- Sẽ được render bằng JS -->
                </tbody>
            </table>
            
            <!-- Hiển thị khi giỏ hàng trống -->
            <div id="empty-cart" class="empty-cart">
                <p>Giỏ hàng của bạn đang trống</p>
                <a href="#dishes" class="btn">Xem thực đơn</a>
            </div>
        </div>

        <!-- Nút hành động -->
        <div class="order-actions">
            <a href="#dishes" class="btn">◀ Tiếp tục chọn món</a>
            <button class="btn btn-danger" id="clear-cart">Xóa hết</button>
            <a href="#order-form" class="btn btn-primary" id="checkout-btn">Thanh toán</a>
        </div>

        <!-- Tổng tiền -->
        <div class="total-info">
            <p>Tạm tính: <strong id="subtotal">0k</strong></p>
            <p>Phí giao hàng: <strong>30k</strong></p>
            <p>Khuyến mãi: <strong id="discount">0k</strong></p>
            <p class="total">Tổng cộng (VAT): <strong id="total">0k</strong></p>
            <input type="hidden" name="shipping_fee" value="30">
        </div>

        <!-- Form thông tin đặt món -->
        <form action="momo_qr_payment.php" method="POST" id="order-form">
            <div class="inputBox">
                <div class="input">
                    <span>Họ tên (*)</span>
                    <input type="text" name="customer_name" placeholder="Nhập họ tên" required>
                </div>
                <div class="input">
                    <span>Số điện thoại (*)</span>
                    <input type="tel" name="customer_phone" placeholder="Nhập số điện thoại" required>
                </div>
            </div>

            <div class="inputBox">
                <div class="input">
                    <span>Địa chỉ nhận hàng (*)</span>
                    <input type="text" name="customer_address" placeholder="Số nhà, tên đường, quận..." required>
                </div>
                <div class="input">
                    <span>Email</span>
                    <input type="email" name="customer_email" placeholder="Nhập email">
                </div>
            </div>

            <div class="inputBox">
                <div class="input">
                    <span>Ngày giao hàng</span>
                    <input type="date" name="delivery_date" min="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="input">
                    <span>Khung giờ giao</span>
                    <select name="delivery_time">
                        <option value="">-- Chọn khung giờ --</option>
                        <option value="9h - 11h">9h - 11h</option>
                        <option value="11h - 13h">11h - 13h</option>
                        <option value="17h - 19h">17h - 19h</option>
                    </select>
                </div>
            </div>

            <div class="inputBox">
                <div class="input" style="width: 100%;">
                    <span>Ghi chú thêm</span>
                    <textarea name="order_note" placeholder="Ghi chú cho shipper hoặc bếp..."></textarea>
                </div>
            </div>
            
            <!-- Phương thức thanh toán MoMo -->
            <div class="inputBox">
                <div class="input" style="width: 100%;">
                    <div class="payment-method-container">
                        <div class="payment-method momo-method">
                            <img src="image/momo-logo.png" alt="MoMo" height="40">
                            <h4>Thanh toán qua ví MoMo</h4>
                            <p>Thanh toán nhanh chóng và an toàn</p>
                            <input type="hidden" name="payment_method" value="momo">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hidden input để lưu thông tin giỏ hàng -->
            <input type="hidden" name="cart_data" id="cart_data">
            <input type="hidden" name="total_amount" id="total_amount">

            <!-- Nút thanh toán MoMo -->
            <button type="submit" class="btn momo-btn" id="submit-order">
                <img src="image/momo-icon.jpg" alt="MoMo" height="20">
                Thanh toán qua MoMo
            </button>
        </form>
    </section>
    <!-- order section ends -->

    <!-- footer section starts -->
    <section class="footer">
        <div class="footer-container">
            <div class="footer-logo">
                <h2>ẨM THỰC VIỆT</h2>
                <p>Hương vị truyền thống - Tinh hoa hiện đại</p>
                <img src="image/footeri.jpg" alt="Trang trí footer" class="footer-decor">
            </div>
        
            <div class="footer-box">
                <h3>Liên kết</h3>
                <ul>
                    <li><a href="#home">Trang chủ</a></li>
                    <li><a href="#dishes">Bán chạy</a></li>
                    <li><a href="#about">Giới thiệu</a></li>
                    <li><a href="#menu">Thực đơn</a></li>
                    <li><a href="#review">Đầu bếp</a></li>
                    <li><a href="#oder">Đặt món</a></li>
                </ul>
            </div>
        
            <div class="footer-box">
                <h3>Liên hệ</h3>
                <ul>
                    <li><i class="fas fa-phone"></i>0344883755</li>
                    <li><i class="fas fa-envelope"></i> giakhanhngo1503@gmail.com</li>
                    <li><i class="fas fa-map-marker-alt"></i> 19/46 Tân Chánh Hiệp, Q.12, TP.HCM</li>
                    <li>
                        <a href="https://www.facebook.com/huynhngoyendi" target="_blank"><i class="fab fa-facebook-f"></i> Facebook</a>
                    </li>
                    <li>
                        <a href="https://github.com/YenDi05" target="_blank"><i class="fab fa-github"></i> GitHub</a>
                    </li>
                </ul>
            </div>
        </div>
    
        <div class="footer-social">
            <a href="https://www.facebook.com/huynhngoyendi" target="_blank"><i class="fab fa-facebook-f"></i></a>
            <a href="https://github.com/YenDi05" target="_blank"><i class="fab fa-github"></i></a>
        </div>
    
        <div class="footer-bottom">
            <p>Nhà Hàng Ẩm Thực Việt - <span>Ngô Gia Khánh & Huỳnh Ngô Yến Di</span> </p>
        </div>
    </section>
    <!-- footer section ends -->

    <!-- loader part -->
    <div class="loader-container">
        <img src="image/loader.gif" alt="">
    </div>

    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>