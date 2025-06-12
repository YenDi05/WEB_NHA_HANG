//edit_product và add_product
// Thêm ảnh preview khi chọn file ảnh
document.getElementById('image').onchange = function (e) {
    const [file] = e.target.files;
    if (file) {
        document.getElementById('preview').src = URL.createObjectURL(file);
    }
}
// orders
       // Hàm xem chi tiết đơn hàng
function viewOrderDetails(orderId) {
    const modal = document.getElementById('orderDetailModal');
    const modalContent = document.getElementById('modalContent');
    
    // Hiển thị modal
    modal.style.display = 'flex';
    
    // Hiển thị loading
    modalContent.innerHTML = '<div class="loading">Đang tải...</div>';
    
    // Gọi AJAX để lấy chi tiết đơn hàng
    fetch(`get_order_details.php?id=${orderId}`)
        .then(response => response.text())
        .then(data => {
            modalContent.innerHTML = data;
        })
        .catch(error => {
            modalContent.innerHTML = '<div class="error">Lỗi khi tải dữ liệu. Vui lòng thử lại sau.</div>';
        });
}

// Đóng modal
function closeModal() {
    document.getElementById('orderDetailModal').style.display = 'none';
}

// Hủy đơn hàng
function cancelOrder(orderId) {
    if (confirm('Bạn có chắc muốn hủy đơn hàng này?')) {
        window.location.href = `cancel_order.php?id=${orderId}`;
    }
}

// In hóa đơn
function printOrder(orderId) {
    window.open(`print_order.php?id=${orderId}`, '_blank');
}

// Đóng modal khi click bên ngoài
window.onclick = function(event) {
    const modal = document.getElementById('orderDetailModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}


        
      //ADD_orders





//Employee
      document.addEventListener('DOMContentLoaded', function() {
        // Elements
        const addEmployeeBtn = document.getElementById('addEmployeeBtn');
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const positionFilter = document.getElementById('positionFilter');
        const confirmDeleteBtn = document.getElementById('confirmDelete');
        const employeeForm = document.getElementById('employeeForm');
        
        // Modal elements
        const employeeModal = document.getElementById('employeeModal');
        const viewEmployeeModal = document.getElementById('viewEmployeeModal');
        const deleteModal = document.getElementById('deleteModal');
        
        let currentEmployeeId = null;
        
        // Kiểm tra URL để hiển thị thông báo phù hợp
        const urlParams = new URLSearchParams(window.location.search);
        
        if (urlParams.has('success')) {
            const action = urlParams.get('success');
            if (action === 'add') {
                showNotification('Thêm nhân viên thành công!', 'success');
            } else if (action === 'edit') {
                showNotification('Cập nhật thông tin thành công!', 'success');
            } else if (action === 'delete') {
                showNotification('Xóa nhân viên thành công!', 'success');
            }
        } else if (urlParams.has('error')) {
            const action = urlParams.get('error');
            if (action === 'add') {
                showNotification('Đã xảy ra lỗi khi thêm nhân viên!', 'error');
            } else if (action === 'edit') {
                showNotification('Đã xảy ra lỗi khi cập nhật thông tin!', 'error');
            } else if (action === 'delete') {
                showNotification('Đã xảy ra lỗi khi xóa nhân viên!', 'error');
            }
        }
        
        // Mở modal tùy chỉnh
        function openModal(modalElement) {
            if (modalElement) {
                modalElement.style.display = 'block';
            }
        }
        // Đóng modal tùy chỉnh
        function closeModal(modalElement) {
            if (modalElement) {
                modalElement.style.display = 'none'; 
            }
        }
        
        // Thêm sự kiện đóng cho tất cả các nút đóng modal
        document.querySelectorAll('.emp-modal-close, .emp-btn-secondary').forEach(button => {
            button.addEventListener('click', function() {
                const modal = this.closest('.emp-modal');
                closeModal(modal);
            });
        });
        
        // Đóng modal khi click bên ngoài
        window.addEventListener('click', function(event) {
            if (event.target.classList.contains('emp-modal')) {
                closeModal(event.target);
            }
        });
        
        // Thêm sự kiện click cho nút thêm nhân viên
        addEmployeeBtn.addEventListener('click', function() {
            document.getElementById('modalTitle').textContent = 'Thêm nhân viên mới';
            document.getElementById('employeeForm').reset();
            document.getElementById('formAction').value = 'add';
            document.getElementById('employeeId').value = '';
            document.getElementById('hireDate').value = getCurrentDate();
            openModal(employeeModal);
        });
        
        // Thêm sự kiện click cho các nút xem chi tiết
        document.querySelectorAll('.emp-btn-view').forEach(btn => {
            btn.addEventListener('click', viewEmployee);
        });
        
        // Thêm sự kiện click cho các nút sửa
        document.querySelectorAll('.emp-btn-edit').forEach(btn => {
            btn.addEventListener('click', editEmployee);
        });
        
        // Thêm sự kiện click cho các nút xóa
        document.querySelectorAll('.emp-btn-delete').forEach(btn => {
            btn.addEventListener('click', showDeleteConfirmation);
        });
        
        // Thêm sự kiện cho các bộ lọc
        searchInput.addEventListener('input', filterEmployees);
        statusFilter.addEventListener('change', filterEmployees);
        positionFilter.addEventListener('change', filterEmployees);
        
        // Nút xác nhận xóa
        confirmDeleteBtn.addEventListener('click', function() {
            deleteEmployee(currentEmployeeId);
        });
        
        // Xử lý phân trang
        document.querySelectorAll('.emp-pagination .emp-page-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Bỏ qua nếu đang ở trang disabled hoặc active
                if (this.parentElement.classList.contains('disabled') || 
                    this.parentElement.classList.contains('active')) {
                    return;
                }
                
                const page = this.textContent;
                
                // Cập nhật UI phân trang
                document.querySelector('.emp-page-item.active').classList.remove('active');
                this.parentElement.classList.add('active');
                
                // Lọc nhân viên theo trang
                filterEmployees();
            });
        });
        
        // Hàm lọc nhân viên
        function filterEmployees() {
            const searchTerm = searchInput.value.toLowerCase();
            const statusValue = statusFilter.value;
            const positionValue = positionFilter.value;
            
            const rows = document.querySelectorAll('#employeesTableBody tr');
            
            rows.forEach(row => {
                const name = row.cells[1].textContent.toLowerCase();
                const phone = row.cells[2].textContent.toLowerCase();
                const position = row.cells[3].textContent;
                const statusText = row.querySelector('td:nth-child(7) span').textContent;
                const status = statusText === 'Đang làm việc' ? 'active' : 'inactive';
                
                // Kiểm tra điều kiện lọc
                const matchSearch = name.includes(searchTerm) || phone.includes(searchTerm);
                const matchStatus = statusValue === 'all' || status === statusValue;
                const matchPosition = positionValue === 'all' || position === positionValue;
                
                // Hiển thị/ẩn hàng theo kết quả lọc
                if (matchSearch && matchStatus && matchPosition) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Cập nhật phân trang khi lọc
            updatePagination();
        }
        
        // Cập nhật phân trang
        function updatePagination() {
            // Trong phiên bản chuyển hướng, phân trang đơn giản chỉ cần tính toán số lượng mục hiển thị
            const visibleRows = document.querySelectorAll('#employeesTableBody tr[style=""]').length;
            const itemsPerPage = 10; // Số lượng mục trên mỗi trang
            const totalPages = Math.ceil(visibleRows / itemsPerPage);
            
            // Cập nhật UI phân trang nếu cần
            // Đây là phiên bản đơn giản, bạn có thể cải thiện thêm
        }
        
        // Xem chi tiết nhân viên
        function viewEmployee() {
            const employeeId = this.getAttribute('data-id');
            
            // Gửi AJAX request để lấy thông tin chi tiết nhân viên
            fetch(`get_employee.php?id=${employeeId}`)
                .then(response => response.json())
                .then(employee => {
                    const detailsContainer = document.querySelector('.employee-details');
                    
                    const hireDate = new Date(employee.hire_date).toLocaleDateString('vi-VN');
                    const salary = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(employee.basic_salary);
                    const status = employee.status === 'active' ? 'Đang làm việc' : 'Đã nghỉ việc';
                    
                    detailsContainer.innerHTML = `
                        <h4>Thông tin cá nhân</h4>
                        <p><strong>Họ và tên:</strong> ${employee.full_name}</p>
                        <p><strong>Số điện thoại:</strong> ${employee.phone}</p>
                        <p><strong>Email:</strong> ${employee.email || 'Không có'}</p>
                        <p><strong>Địa chỉ:</strong> ${employee.address || 'Không có'}</p>
                        
                        <h4>Thông tin công việc</h4>
                        <p><strong>Vị trí/Chức vụ:</strong> ${employee.position}</p>
                        <p><strong>Ngày bắt đầu làm việc:</strong> ${hireDate}</p>
                        <p><strong>Lương cơ bản:</strong> ${salary}</p>
                        <p><strong>Trạng thái:</strong> ${status}</p>
                    `;
                    
                    // Set up edit button
                    const editBtn = document.querySelector('.edit-from-view');
                    editBtn.setAttribute('data-id', employee.id);
                    editBtn.onclick = function() {
                        closeModal(viewEmployeeModal);
                        
                        document.getElementById('modalTitle').textContent = 'Chỉnh sửa thông tin nhân viên';
                        document.getElementById('employeeId').value = employee.id;
                        document.getElementById('fullName').value = employee.full_name;
                        document.getElementById('phone').value = employee.phone;
                        document.getElementById('email').value = employee.email || '';
                        document.getElementById('address').value = employee.address || '';
                        document.getElementById('position').value = employee.position;
                        document.getElementById('hireDate').value = employee.hire_date;
                        document.getElementById('basicSalary').value = employee.basic_salary;
                        
                        if (employee.status === 'active') {
                            document.getElementById('statusActive').checked = true;
                        } else {
                            document.getElementById('statusInactive').checked = true;
                        }
                        
                        document.getElementById('formAction').value = 'edit';
                        openModal(employeeModal);
                    };
                    
                    openModal(viewEmployeeModal);
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Đã xảy ra lỗi khi lấy thông tin nhân viên!', 'error');
                });
        }
        
        // Chỉnh sửa nhân viên
        function editEmployee() {
            const employeeId = this.getAttribute('data-id');
            
            // Gửi AJAX request để lấy thông tin nhân viên
            fetch(`get_employee.php?id=${employeeId}`)
                .then(response => response.json())
                .then(employee => {
                    document.getElementById('modalTitle').textContent = 'Chỉnh sửa thông tin nhân viên';
                    document.getElementById('employeeId').value = employee.id;
                    document.getElementById('fullName').value = employee.full_name;
                    document.getElementById('phone').value = employee.phone;
                    document.getElementById('email').value = employee.email || '';
                    document.getElementById('address').value = employee.address || '';
                    document.getElementById('position').value = employee.position;
                    document.getElementById('hireDate').value = employee.hire_date;
                    document.getElementById('basicSalary').value = employee.basic_salary;
                    
                    if (employee.status === 'active') {
                        document.getElementById('statusActive').checked = true;
                    } else {
                        document.getElementById('statusInactive').checked = true;
                    }
                    
                    document.getElementById('formAction').value = 'edit';
                    openModal(employeeModal);
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Đã xảy ra lỗi khi lấy thông tin nhân viên!', 'error');
                });
        }
        
        // Hiển thị modal xác nhận xóa
        function showDeleteConfirmation() {
            currentEmployeeId = this.getAttribute('data-id');
            openModal(deleteModal);
        }
        
        // Xóa nhân viên
        function deleteEmployee(employeeId) {
            // Tạo và gửi form ẩn
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'employee_action.php';
            form.style.display = 'none';
            
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            idInput.value = employeeId;
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'delete';
            
            form.appendChild(idInput);
            form.appendChild(actionInput);
            document.body.appendChild(form);
            form.submit();
        }
        
        // Hiển thị thông báo
        function showNotification(message, type) {
            // Kiểm tra xem đã có thông báo chưa
            let notification = document.querySelector('.emp-notification');
            
            if (!notification) {
                // Tạo thông báo mới
                notification = document.createElement('div');
                notification.className = 'emp-notification';
                document.body.appendChild(notification);
            }
            
            // Thêm class type và nội dung
            notification.className = `emp-notification ${type}`;
            notification.textContent = message;
            
            // Hiển thị thông báo
            notification.classList.add('show');
            
            // Tự động ẩn sau 3 giây
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }
        
        // Lấy ngày hiện tại định dạng YYYY-MM-DD
        function getCurrentDate() {
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }
    });