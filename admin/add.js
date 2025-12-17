// --- BẢO MẬT: KIỂM TRA TRẠNG THÁI ĐĂNG NHẬP ---
function checkAuth() {
    // Kiểm tra xem biến 'adminLoggedIn' có tồn tại và là 'true' trong Local Storage không
    if (localStorage.getItem('adminLoggedIn') !== 'true') {
        // Nếu chưa đăng nhập, chuyển hướng đến trang login.html
        window.location.href = 'loginadmin.html';
    }
}

// Hàm Đăng Xuất
function logoutAdmin() {
    localStorage.removeItem('adminLoggedIn'); // Xóa trạng thái đăng nhập
    alert('Bạn đã đăng xuất thành công.');
    window.location.href = 'loginadmin.html'; // Chuyển hướng về trang đăng nhập
}

// Gọi hàm kiểm tra mỗi khi trang admin.html được tải
checkAuth();
// --- HẾT PHẦN BẢO MẬT ---


document.addEventListener('DOMContentLoaded', function () {
    const topNavButtons = document.querySelectorAll('.main-nav .nav-item');
    const sidebarMenus = document.querySelectorAll('.menu-list');
    const sidebarMenuItems = document.querySelectorAll('.sidebar .menu-item');
    const contentViews = document.querySelectorAll('.content-view');
    const logoutButton = document.querySelector('.user-control .nav-item');
    const searchInput = document.getElementById('searchInput');


    // Gắn chức năng đăng xuất vào nút
    logoutButton.addEventListener('click', logoutAdmin);
    // Thêm biến cho container và input mới
    const otherTableContainer = document.getElementById('otherDataTableContainer');
    const searchInputOther = document.getElementById('searchInputOther');


    // Hàm vẽ bảng HTML chỉ với các cột cần thiết cho tab Dữ liệu Khác
    function renderOtherDataTable(dataToDisplay) {
        if (dataToDisplay.length === 0) {
            otherTableContainer.innerHTML = '<p style="text-align:center; color:gray;">Không có người dùng nào được tìm thấy.</p>';
            return;
        }

        let tableHTML = '<table>';
        tableHTML += '<thead><tr><th>ID</th><th>Ngày nhận</th><th>Họ Tên</th><th>SĐT</th><th style="width: 40%;">Ghi Chú</th><th>Hành Động</th></tr></thead>';
        tableHTML += '<tbody>';

        dataToDisplay.forEach(user => {
            const formattedDate = user.ngay_nhan ? new Date(user.ngay_nhan).toLocaleString('vi-VN') : '-';
            tableHTML += `
            <tr data-id="${user.id}">
                <td>${user.id}</td>
                <td>${formattedDate}</td>
                <td>${user.ho_ten}</td>
                <td>${user.sdt}</td>
                <td style="width: 40%; text-align: left; word-wrap: break-word;">${user.ghi_chu || '-'}</td>
                <td>
                    <button class="action-btn btn-update" onclick="openEditModal(${user.id}, '${user.ho_ten}', '${user.sdt}', '${user.nam_sinh}', '${user.dia_chi}', '${user.chuong_trinh}', '${user.quoc_gia}', '${user.ghi_chu}')">Sửa</button>
                    <button class="action-btn btn-delete" onclick="deleteUser(${user.id})">Xóa</button>
                </td>
            </tr>
        `;
        });

        tableHTML += '</tbody></table>';
        otherTableContainer.innerHTML = tableHTML;
    }

    // Hàm tải dữ liệu cho tab Dữ liệu Khác (sử dụng cùng allUsers)
    async function fetchOtherData() {
        // Chỉ tải lại dữ liệu nếu chưa có, nếu không thì dùng allUsers đã tải từ fetchUsers
        if (!window.allUsers) {
            await fetchUsers();
        }
        // Render dữ liệu đã có
        renderOtherDataTable(window.allUsers || []);
    }


    // Hàm lọc dữ liệu cho tab Dữ liệu Khác
    function filterOtherData() {
        const searchTerm = searchInputOther.value.toLowerCase();

        // Tái sử dụng logic lọc từ hàm filterUsers/allUsers
        const filteredUsers = window.allUsers.filter(user => {
            const searchString = `${user.ho_ten} ${user.sdt} ${user.ghi_chu} ${user.ngay_nhan}`.toLowerCase();
            return searchString.includes(searchTerm);
        });

        renderOtherDataTable(filteredUsers);
    }

    // Gắn Listener cho input tìm kiếm mới
    searchInputOther.addEventListener('keyup', filterOtherData);

    // Gắn Listener khi chuyển tab (bạn cần thêm logic này vào hàm chuyển tab chính)
    document.querySelector('[data-view="other-data"]').addEventListener('click', fetchOtherData);

    // Bổ sung: Hàm fetchUsers phải được sửa để lưu dữ liệu vào window.allUsers
    let allUsers = []; // Khai báo biến global

    // Sửa hàm fetchUsers (Đã sửa trong mã gốc của bạn, đảm bảo biến allUsers là global)

    // Hàm chuyển đổi giữa các menu chính (Dữ liệu/Web)
    function switchMainMenu(targetContent) {
        // 1. Cập nhật nút trên Top Bar
        topNavButtons.forEach(btn => btn.classList.remove('active'));
        const targetBtn = document.querySelector(`.main-nav button[data-content="${targetContent}"]`);
        if (targetBtn) {
            targetBtn.classList.add('active');
        }

        // 2. Cập nhật Menu Sidebar
        sidebarMenus.forEach(menu => menu.classList.remove('active-menu'));
        const activeMenu = document.getElementById(`${targetContent}-menu`);
        if (activeMenu) {
            activeMenu.classList.add('active-menu');

            // 3. Tự động chọn mục đầu tiên của menu mới
            const firstItem = activeMenu.querySelector('.menu-item');
            if (firstItem) {
                // Kích hoạt mục menu sidebar đầu tiên
                sidebarMenuItems.forEach(item => item.classList.remove('active'));
                firstItem.classList.add('active');

                // Hiển thị nội dung tương ứng
                const firstContentView = document.getElementById(firstItem.dataset.view);
                contentViews.forEach(view => view.classList.remove('active-view'));
                if (firstContentView) {
                    firstContentView.classList.add('active-view');
                }
            }
        }
    }

    // Lắng nghe sự kiện click trên Top Bar
    topNavButtons.forEach(button => {
        button.addEventListener('click', function () {
            const target = this.getAttribute('data-content');
            switchMainMenu(target);
        });
    });

    // Lắng nghe sự kiện click trên Menu Sidebar
    sidebarMenuItems.forEach(item => {
        item.addEventListener('click', function () {
            // 1. Cập nhật trạng thái active của Sidebar Menu Item
            sidebarMenuItems.forEach(i => i.classList.remove('active'));
            this.classList.add('active');

            // 2. Hiển thị Content View tương ứng
            const targetView = this.getAttribute('data-view');
            contentViews.forEach(view => view.classList.remove('active-view'));
            const activeView = document.getElementById(targetView);
            if (activeView) {
                activeView.classList.add('active-view');
            }
        });
    });

    // Khởi tạo trạng thái ban đầu (ví dụ: "Quản lý Dữ liệu" được chọn)
    switchMainMenu('data-management');



});