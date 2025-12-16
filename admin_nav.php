<?php
include 'connect.php';

// Lấy tên file hiện tại để active menu
$current_page = basename($_SERVER['PHP_SELF']);

// Nếu chưa có $admin_name → set mặc định
$admin_name = $admin_name ?? "Admin";

// 1. Kiểm tra có ở trang con của "Quản lý sách" không
$openBookMenu = in_array($current_page, [
    'admin_bookmanagement.php',
    'admin_categorymanagement.php'
]);


$openStatsMenu = in_array($current_page, [
    'admin_stats_orders.php',     // Ví dụ: file thống kê đơn hàng
    'admin_customer_stats.php'   // Ví dụ: file thống kê khách hàng
]);
?>

<style>
/* ========== ADMIN SIDEBAR ========== */
.admin_sidebar {
    width: 260px;
    background: #16515fff;
    color: #fff;
    padding: 2rem 1.4rem;
    display: flex;
    flex-direction: column;
    
    /* --- PHẦN CHỈNH SỬA --- */
    height: 100vh; /* Giữ chiều cao bằng màn hình */
    position: sticky; /* Cố định sidebar khi cuộn trang chính */
    top: 0; 
    overflow-y: auto; /* Tự động hiện thanh cuộn nếu nội dung dài */
    /* ---------------------- */
}

/* Tùy chỉnh thanh cuộn cho đẹp (ẩn thanh cuộn mặc định to xấu đi) */
.admin_sidebar::-webkit-scrollbar {
    width: 5px;
}
.admin_sidebar::-webkit-scrollbar-track {
    background: transparent; 
}
.admin_sidebar::-webkit-scrollbar-thumb {
    background: #458298ff; 
    border-radius: 10px;
}
.admin_sidebar::-webkit-scrollbar-thumb:hover {
    background: #fff; 
}

.admin_brand {
    font-size: 25px;
    font-weight: bold;
    margin-bottom: 1.5rem;
    text-align: center;
    letter-spacing: 0.5px;
    flex-shrink: 0; 
}

.admin_profile {
    text-align: center;
    margin-bottom: 2rem;
    flex-shrink: 0; 
}

.admin_profile img {
    width: 70px;
    border-radius: 50%;
    margin-bottom: 10px;
}

.admin_profile p {
    opacity: 0.9;
    font-size: 16px;
    font-weight: bold;
}

.admin_menu {
    display: flex;
    flex-direction: column;
    gap: 6px;
    flex-grow: 1; 
}

.admin_menu a {
    color: #ffffff;
    text-decoration: none;
    padding: .6rem .8rem;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: .25s ease;
    font-size: 16px;
}

.admin_menu a:hover,
.admin_menu a.active {
    background: #458298ff;
}

.logout-btn {
    margin-top: auto; 
}

/* ===== SUBMENU ===== */
.submenu {
    list-style: none;
    margin: 5px 0 10px 35px;
    padding-left: 0;
    display: none;
}

.submenu li {
    margin: 10px 0;
}

.submenu li a {
    font-size: 15px;
    padding: .5rem .7rem;
    border-radius: 6px;
    display: block;
    transition: 0.25s ease;
}

.submenu li a:hover,
.submenu li a.active {
    background: #3a6d7aff;
}

/* RESPONSIVE */
@media (max-width:768px){
    .admin_sidebar {
        display: none;
    }
}
</style>


<aside class="admin_sidebar">

    <div class="admin_brand">Quản lý Trang</div>

    <div class="admin_profile">
        <img src="images/user.png" alt="Admin">
        <p><?= $admin_name ?></p>
    </div>

    <nav class="admin_menu">

        <a href="admin_dashboard.php" class="<?= ($current_page=='admin_dashboard.php')?'active':'' ?>">
            <i class="fa fa-chart-line"></i> Dashboard
        </a>

        <a href="javascript:void(0);" id="bookMenuToggle">
            <i class="fa fa-book"></i>
            Quản lý sách <?= $openBookMenu ? '▴' : '▾' ?>
        </a>
        <ul class="submenu" id="bookSubmenu" style="display: <?= $openBookMenu ? 'block' : 'none' ?>;">
            <li>
                <a href="admin_bookmanagement.php" class="<?= ($current_page=='admin_bookmanagement.php')?'active':'' ?>">
                    Quản lý sách
                </a>
            </li>
            <li>
                <a href="admin_categorymanagement.php" class="<?= ($current_page=='admin_categorymanagement.php')?'active':'' ?>">
                    Quản lý danh mục
                </a>
            </li>
        </ul>

        <a href="admin_usermanagement.php" class="<?= ($current_page=='admin_usermanagement.php')?'active':'' ?>">
            <i class="fa fa-users"></i> Quản lý Người dùng
        </a>

        <a href="admin_orders.php">
            <i class="fa fa-shopping-cart"></i> Quản lý Đơn hàng
        </a>

        <a href="admin_comments.php">
            <i class="fa fa-comments"></i> Quản lý Bình luận
        </a>

        <a href="javascript:void(0);" id="statsMenuToggle">
            <i class="fa fa-chart-pie"></i> 
            Thống kê <?= $openStatsMenu ? '▴' : '▾' ?>
        </a>
        <ul class="submenu" id="statsSubmenu" style="display: <?= $openStatsMenu ? 'block' : 'none' ?>;">
            <li>
                <a href="admin_stats_orders .php" class="<?= ($current_page=='admin_stats_orders.php')?'active':'' ?>">
                    Thống kê đơn hàng
                </a>
            </li>
            <li>
                <a href="admin_customer_stats.php" class="<?= ($current_page=='admin_customer_stats.php')?'active':'' ?>">
                    Thống kê khách hàng
                </a>
            </li>
        </ul>
       <a href="index.php" target="_blank">
            <i class="fa fa-globe"></i> Website
        </a>
        <a href="logout.php">
            <i class="fa fa-sign-out-alt"></i> Đăng xuất
        </a>

    </nav>

</aside>


<script>
document.addEventListener("DOMContentLoaded", () => {
    
    // --- Xử lý menu Quản lý sách ---
    const bookToggle = document.getElementById("bookMenuToggle");
    const bookSubmenu = document.getElementById("bookSubmenu");

    if(bookToggle && bookSubmenu){
        bookToggle.addEventListener("click", () => {
            const isOpen = bookSubmenu.style.display === "block";
            bookSubmenu.style.display = isOpen ? "none" : "block";
            bookToggle.innerHTML = isOpen
                ? bookToggle.innerHTML.replace("▴", "▾")
                : bookToggle.innerHTML.replace("▾", "▴");
        });
    }

    // --- [MỚI] Xử lý menu Thống kê ---
    const statsToggle = document.getElementById("statsMenuToggle");
    const statsSubmenu = document.getElementById("statsSubmenu");

    if(statsToggle && statsSubmenu){
        statsToggle.addEventListener("click", () => {
            const isOpen = statsSubmenu.style.display === "block";
            statsSubmenu.style.display = isOpen ? "none" : "block";
            
            // Logic đổi mũi tên: nếu đang là ▾ (mũi tên xuống) thì đổi thành ▴ và ngược lại
            // Lưu ý: Nếu trong code editor bạn không thấy ký tự mũi tên, hãy copy chính xác từ code mẫu
            statsToggle.innerHTML = isOpen
                ? statsToggle.innerHTML.replace("▴", "▾")
                : statsToggle.innerHTML.replace("▾", "▴");
        });
    }
});
</script>