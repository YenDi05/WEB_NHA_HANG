let menu = document.querySelector('#menu-bars');
let navbar = document.querySelector('.navbar');

menu.onclick = () => {
    menu.classList.toggle('fa-times');
    navbar.classList.toggle('active');
}

let section = document.querySelectorAll('section'); 
let navLinks = document.querySelectorAll('header .navbar a'); 

window.onscroll = () => {
    menu.classList.remove('fa-times');
    navbar.classList.remove('active');

    section.forEach(sec => {
        let top = window.scrollY;
        let height = sec.offsetHeight;
        let offset = sec.offsetTop - 150;
        let id = sec.getAttribute('id');

        if(top >= offset && top < offset + height){
            navLinks.forEach(links => {
                links.classList.remove('active');
                document.querySelector('header .navbar a[href*=' + id + ']')?.classList.add('active');
            });
        };
    });
}

document.querySelector('#search-icon').onclick = () => {
    document.querySelector('#search-form').classList.toggle('active');
}

document.querySelector('#close').onclick = () => {
    document.querySelector('#search-form').classList.remove('active');
}

var swiper = new Swiper(".home-slider", {
   spaceBetween: 20,
   centeredSlides: true,
   autoplay: {
      delay: 7500,
      disableOnInteraction: false,
   },
   pagination:{
    el:".swiper-pagination",
    clickable:true,
   },
   loop:true,
});

var swiper = new Swiper(".review-slider", {
    spaceBetween: 30,
    centeredSlides: true,
    autoplay: {
       delay: 7500,
       disableOnInteraction: false,
    },
    loop:true,
    breakpoints: {
        0: {
            slidesPerView: 1,
        },
        640: {
            slidesPerView: 2,
        },
        768: {
            slidesPerView: 3,
        },
        1024: {
            slidesPerView: 4,
        },
    },
});

function loader(){
    document.querySelector('.loader-container').classList.add('fade-out');
}

function fadeOut(){
    setInterval(loader, 3000);
}

window.onload = fadeOut;

// Hàm định dạng tiền theo kiểu nghìn đồng (k)
function formatCurrency(amount) {
    // Hiển thị số tiền dưới dạng nghìn đồng không có chữ số thập phân
    return parseFloat(amount).toLocaleString('vi-VN', {maximumFractionDigits: 0}) + 'k';
}

// Khởi tạo giỏ hàng từ localStorage
let cart = JSON.parse(localStorage.getItem('cart')) || [];

// Hàm hiển thị số lượng món trong giỏ hàng
function updateCartCount() {
    const cartIcon = document.querySelector('.fa-shopping-cart');
    if (!cartIcon) return;
    
    // Tính tổng số lượng món trong giỏ hàng
    const count = cart.reduce((total, item) => total + parseInt(item.quantity), 0);
    
    // Hiển thị số lượng trên icon
    if (count > 0) {
        cartIcon.setAttribute('data-count', count);
        cartIcon.classList.add('has-items');
    } else {
        cartIcon.setAttribute('data-count', '0');
        cartIcon.classList.remove('has-items');
    }
}

// Hàm cập nhật dữ liệu giỏ hàng vào form
function updateCartDataInput() {
    const cartDataInput = document.getElementById('cart_data');
    const totalAmountInput = document.getElementById('total_amount');
    
    if (cartDataInput && totalAmountInput) {
        const totalProductAmount = cart.reduce((total, item) => total + (parseFloat(item.price) * parseInt(item.quantity)), 0);
        
        console.log("Cập nhật dữ liệu form:", {
            cartData: JSON.stringify(cart),
            totalAmount: totalProductAmount
        });
        
        cartDataInput.value = JSON.stringify(cart);
        // Lưu giá trị chỉ của sản phẩm, không bao gồm phí ship
        totalAmountInput.value = totalProductAmount;
        return true;
    }
    return false;
}

// Hàm cập nhật tất cả hiển thị giỏ hàng
function updateCart() {
    updateCartCount();
    renderCart();
    renderCartPopup();
    updateCartDataInput();
}

// Hàm thêm món vào giỏ hàng
function addToCart(id, name, price, image, quantity = 1) {
    // Kiểm tra xem món đã có trong giỏ hàng chưa
    const existingItem = cart.find(item => item.id === id);
    
    if (existingItem) {
        // Nếu đã có, tăng số lượng
        existingItem.quantity += quantity;
    } else {
        // Nếu chưa có, thêm mới
        cart.push({
            id: id,
            name: name,
            price: price,
            image: image,
            quantity: quantity
        });
    }
    
    // Lưu giỏ hàng vào localStorage
    localStorage.setItem('cart', JSON.stringify(cart));
    
    // Cập nhật hiển thị giỏ hàng - Sử dụng hàm updateCart thay vì gọi riêng lẻ
    updateCart();
    
    // Hiển thị thông báo
    showToast(`Đã thêm "${name}" vào giỏ hàng!`);
}

// Hàm xóa món khỏi giỏ hàng
function removeFromCart(id) {
    cart = cart.filter(item => item.id !== id);
    localStorage.setItem('cart', JSON.stringify(cart));
    // Sử dụng hàm updateCart thay vì gọi riêng lẻ
    updateCart();
}

// Hàm cập nhật số lượng món
function updateQuantity(id, quantity) {
    const item = cart.find(item => item.id === id);
    if (item) {
        item.quantity = parseInt(quantity);
        if (item.quantity <= 0) {
            removeFromCart(id);
        } else {
            localStorage.setItem('cart', JSON.stringify(cart));
            // Sử dụng hàm updateCart thay vì gọi riêng lẻ
            updateCart();
        }
    }
}

// Hàm xóa tất cả món trong giỏ hàng
function clearCart() {
    if (confirm('Bạn có chắc muốn xóa tất cả món trong giỏ hàng?')) {
        cart = [];
        localStorage.setItem('cart', JSON.stringify(cart));
        // Sử dụng hàm updateCart thay vì gọi riêng lẻ
        updateCart();
    }
}

// Hàm hiển thị popup giỏ hàng
function showCartPopup() {
    const cartPopup = document.getElementById('cart-popup');
    if (!cartPopup) return;
    
    cartPopup.classList.add('active');
    renderCartPopup();
    
    // Thêm overlay để đóng popup khi click bên ngoài
    const overlay = document.createElement('div');
    overlay.classList.add('overlay');
    overlay.addEventListener('click', hideCartPopup);
    document.body.appendChild(overlay);
}

// Hàm ẩn popup giỏ hàng
function hideCartPopup() {
    const cartPopup = document.getElementById('cart-popup');
    if (!cartPopup) return;
    
    cartPopup.classList.remove('active');
    
    // Xóa overlay
    const overlay = document.querySelector('.overlay');
    if (overlay) {
        overlay.remove();
    }
}

// Hàm render nội dung popup giỏ hàng
function renderCartPopup() {
    const cartBody = document.querySelector('.cart-popup-body');
    const cartTotal = document.getElementById('cart-popup-total');
    if (!cartBody || !cartTotal) return;
    
    // Xóa nội dung cũ
    cartBody.innerHTML = '';
    
    // Kiểm tra giỏ hàng trống
    if (cart.length === 0) {
        cartBody.innerHTML = `
            <div class="cart-empty">
                <p>Giỏ hàng của bạn đang trống</p>
                <a href="#dishes" class="btn" onclick="hideCartPopup()">Xem thực đơn</a>
            </div>
        `;
        cartTotal.textContent = '0k';
        return;
    }
    
    // Tính tổng tiền
    let totalAmount = 0;
    
    // Thêm các món vào giỏ hàng
    cart.forEach(item => {
        const subtotal = item.price * item.quantity;
        totalAmount += subtotal;
        
        const cartItem = document.createElement('div');
        cartItem.classList.add('cart-item');
        cartItem.innerHTML = `
            <div class="cart-item-image">
                <img src="${item.image}" alt="${item.name}">
            </div>
            <div class="cart-item-info">
                <div class="cart-item-name">${item.name}</div>
                <div class="cart-item-price">${formatCurrency(item.price)}</div>
            </div>
            <div class="cart-item-quantity">
                <button onclick="updateQuantity(${item.id}, ${item.quantity - 1})">-</button>
                <input type="text" value="${item.quantity}" readonly>
                <button onclick="updateQuantity(${item.id}, ${item.quantity + 1})">+</button>
            </div>
            <div class="cart-item-remove" onclick="removeFromCart(${item.id})">
                <i class="fas fa-trash"></i>
            </div>
        `;
        
        cartBody.appendChild(cartItem);
    });
    
    // Cập nhật tổng tiền
    cartTotal.textContent = formatCurrency(totalAmount);
}

// Hàm hiển thị giỏ hàng trong trang đặt món
function renderCart() {
    const cartBody = document.getElementById('cart-body');
    const emptyCart = document.getElementById('empty-cart');
    const orderActions = document.querySelector('.order-actions');
    const totalInfo = document.querySelector('.total-info');
    const submitBtn = document.getElementById('submit-order');
    
    if (!cartBody) return;
    
    // Xóa nội dung cũ
    cartBody.innerHTML = '';
    
    // Kiểm tra giỏ hàng trống
    if (cart.length === 0) {
        if (emptyCart) emptyCart.style.display = 'block';
        if (orderActions) orderActions.style.display = 'none';
        if (totalInfo) totalInfo.style.display = 'none';
        if (submitBtn) submitBtn.disabled = true;
        
        // Cập nhật input hidden
        if (document.getElementById('cart_data')) {
            document.getElementById('cart_data').value = '';
        }
        if (document.getElementById('total_amount')) {
            document.getElementById('total_amount').value = '0';
        }
        
        return;
    }
    
    // Hiển thị các phần tử nếu giỏ hàng có món
    if (emptyCart) emptyCart.style.display = 'none';
    if (orderActions) orderActions.style.display = 'flex';
    if (totalInfo) totalInfo.style.display = 'block';
    if (submitBtn) submitBtn.disabled = false;
    
    // Tính tổng tiền
    let totalAmount = 0;
    
    // Thêm các món vào bảng giỏ hàng
    cart.forEach((item, index) => {
        const subtotal = item.price * item.quantity;
        totalAmount += subtotal;
        
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${index + 1}</td>
            <td>
                <div class="item-info">
                    <img src="${item.image}" alt="${item.name}">
                    <span>${item.name}</span>
                </div>
            </td>
            <td>${formatCurrency(item.price)}</td>
            <td>
                <input type="number" min="1" value="${item.quantity}" onchange="updateQuantity(${item.id}, this.value)">
            </td>
            <td>${formatCurrency(subtotal)}</td>
            <td>
                <button onclick="removeFromCart(${item.id})" class="btn-remove">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        
        cartBody.appendChild(tr);
    });
    
    const shippingFee = 30; // Phí giao hàng đã là đơn vị k
    
    // Cập nhật tổng tiền
    document.getElementById('subtotal').textContent = formatCurrency(totalAmount);
    document.getElementById('total').textContent = formatCurrency(totalAmount + shippingFee);
    
    // Cập nhật input hidden
    if (document.getElementById('cart_data')) {
        document.getElementById('cart_data').value = JSON.stringify(cart);
    }
    if (document.getElementById('total_amount')) {
        document.getElementById('total_amount').value = totalAmount;
    }
}

// Hàm hiển thị thông báo
function showToast(message) {
    // Tạo thông báo
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.textContent = message;
    
    // Thêm vào body
    document.body.appendChild(toast);
    
    // Hiển thị toast
    setTimeout(() => {
        toast.classList.add('show');
    }, 100);
    
    // Tự động ẩn sau 3 giây
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}

// Xử lý gửi đơn hàng
function submitOrder(event) {
    event.preventDefault();
    console.log("Đang xử lý đặt hàng...");
    
    // Kiểm tra giỏ hàng trống
    if (cart.length === 0) {
        showToast('Giỏ hàng của bạn đang trống. Vui lòng thêm món vào giỏ hàng trước khi đặt hàng.');
        return;
    }
    
    // Đảm bảo dữ liệu giỏ hàng được cập nhật vào form trước khi gửi
    updateCartDataInput();
    
    // Kiểm tra dữ liệu đã được cập nhật vào form chưa
    const cartData = document.getElementById('cart_data').value;
    console.log("Dữ liệu giỏ hàng trước khi gửi:", cartData);
    
    if (!cartData || cartData === '') {
        console.error("Không thể cập nhật dữ liệu giỏ hàng vào form");
        showToast("Có lỗi khi xử lý giỏ hàng. Vui lòng thử lại.");
        return;
    }
    
    // Lấy form
    const form = event.target;
    const formData = new FormData(form);
    
    // Kiểm tra phương thức thanh toán
    const paymentMethod = formData.get('payment_method');
    let processUrl = 'process_order.php';
    
    if (paymentMethod === 'momo') {
        processUrl = 'momo_qr_payment.php';
    }
    
    // Gửi ajax request
    fetch(processUrl, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log("Status response:", response.status);
        if (!response.ok) {
            throw new Error("Lỗi server: " + response.status);
        }
        return response.json();
    })
    .then(data => {
        console.log("Phản hồi từ server:", data);
        if (data.success) {
            if (paymentMethod === 'momo' && data.redirect) {
                // Chuyển hướng đến trang thanh toán QR MoMo
                window.location.href = data.redirect;
            } else {
                // Hiển thị thông báo thành công với COD
                document.getElementById('order-code').textContent = data.order_code;
                document.getElementById('order-success').classList.add('show');
                
                // Xóa giỏ hàng
                cart = [];
                localStorage.setItem('cart', JSON.stringify(cart));
                updateCart();
                
                // Reset form
                form.reset();
            }
        } else {
            showToast('Đã xảy ra lỗi: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Lỗi:', error);
        showToast('Đã xảy ra lỗi khi xử lý đơn hàng: ' + error.message);
    });
}

// Xử lý khi trang được tải
document.addEventListener('DOMContentLoaded', function() {
    console.log("Trang đã tải xong, khởi tạo các chức năng...");
    
    // Cập nhật hiển thị giỏ hàng
    updateCart();
    
    // Xử lý form tìm kiếm
    const searchForm = document.getElementById('search-form');
    const closeSearch = document.getElementById('close');
    const searchIcon = document.getElementById('search-icon');
    
    if (searchIcon) {
        searchIcon.addEventListener('click', function() {
            searchForm.classList.add('active');
        });
    }
    
    if (closeSearch) {
        closeSearch.addEventListener('click', function() {
            searchForm.classList.remove('active');
        });
    }
    
    // THÊM CODE MỚI Ở ĐÂY: Xử lý khi người dùng nhấn vào menu
    const navLinks = document.querySelectorAll('header .navbar a');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Kiểm tra nếu đang có tham số tìm kiếm trong URL
            if (window.location.search.includes('search=')) {
                e.preventDefault(); // Ngăn hành vi mặc định
                
                // Lấy anchor từ href (ví dụ: '#home', '#dishes')
                const href = this.getAttribute('href');
                
                // Xóa tham số tìm kiếm và chuyển đến phần được chọn
                history.pushState({}, '', window.location.pathname + href);
                
                // Cuộn đến phần được chọn
                if (href.startsWith('#')) {
                    const targetSection = document.querySelector(href);
                    if (targetSection) {
                        targetSection.scrollIntoView({behavior: 'smooth'});
                    }
                }
                
                // Xóa phần kết quả tìm kiếm nếu có
                const searchResults = document.querySelector('.search-results');
                if (searchResults) {
                    searchResults.style.display = 'none';
                }
            }
        });
    });
    
    // THÊM CODE MỚI Ở ĐÂY: Xử lý khi tải lại trang với tham số tìm kiếm
    window.addEventListener('load', function() {
        // Kiểm tra nếu trang được tải lại (không phải từ cache)
        if (performance.navigation.type === 1 && window.location.search.includes('search=')) {
            // Chuyển hướng về trang chủ không có tham số tìm kiếm
            window.location.href = window.location.pathname;
        }
    });
    
    // Xử lý form đặt hàng
    const orderForm = document.getElementById('order-form');
    if (orderForm) {
        console.log("Đã tìm thấy form đặt hàng, đăng ký sự kiện submit");
        // Xóa tất cả event listener cũ nếu có
        const clonedForm = orderForm.cloneNode(true);
        orderForm.parentNode.replaceChild(clonedForm, orderForm);
        
        // Đăng ký lại event listener
        clonedForm.addEventListener('submit', submitOrder);
    } else {
        console.error("Không tìm thấy form đặt hàng với id 'order-form'");
    }
    
    // Thêm sự kiện cho nút xóa hết
    const clearCartBtn = document.getElementById('clear-cart');
    if (clearCartBtn) {
        clearCartBtn.addEventListener('click', clearCart);
    }
    
    // Thêm sự kiện cho các nút "Đặt món"
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    console.log("Số nút đặt món tìm thấy:", addToCartButtons.length);
    
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const id = parseInt(this.getAttribute('data-id'));
            const name = this.getAttribute('data-name');
            const price = parseFloat(this.getAttribute('data-price'));
            const image = this.getAttribute('data-image');
            
            console.log("Thêm món vào giỏ hàng:", {id, name, price, image});
            addToCart(id, name, price, image);
        });
    });
    
    // Hiển thị popup giỏ hàng khi click vào icon giỏ hàng
    const cartIcon = document.getElementById('cart-icon');
    if (cartIcon) {
        cartIcon.addEventListener('click', function(e) {
            e.preventDefault();
            showCartPopup();
        });
    }
    
    // Đóng popup giỏ hàng
    const closeCartBtn = document.getElementById('close-cart');
    if (closeCartBtn) {
        closeCartBtn.addEventListener('click', hideCartPopup);
    }
    
    // Đóng thông báo đặt hàng thành công
    const closeSuccessBtn = document.getElementById('close-success');
    if (closeSuccessBtn) {
        closeSuccessBtn.addEventListener('click', function() {
            document.getElementById('order-success').classList.remove('show');
        });
    }
    
    // Thêm sự kiện cho nút thanh toán
    const checkoutBtn = document.getElementById('checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', function() {
            // Scroll đến form đặt hàng
            document.getElementById('submit-order')?.scrollIntoView({behavior: 'smooth'});
            // Nhấn mạnh vào form
            const orderForm = document.getElementById('order-form');
            if (orderForm) {
                orderForm.classList.add('highlight');
                setTimeout(() => {
                    orderForm.classList.remove('highlight');
                }, 1500);
            }
        });
    }
    
    console.log("Khởi tạo các chức năng hoàn tất");
});