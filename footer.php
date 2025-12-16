<section class="footer">
    <div class="footer_box_container">
        
<div class="footer_box">
            <div class="footer_logo_cont">
                <a href="index.php" class="book_logo">
                    <i class="fas fa-book-open"></i> Babo Bookstore 
                </a>
            </div>

            <p><i class="fas fa-phone"></i> 0901234567</p>
            <p><i class="fas fa-envelope"></i> babo@gmail.com</p>
            <p><i class="fas fa-map-marker-alt"></i> Số 12 Chùa Bộc, Phường Kim Liên, Hà Nội</p>
            <p><i class="fa-solid fa-shop"></i> Giờ mở cửa: 8h - 22h</p>
        </div>

        <div class="footer_box">
            <h3>Liên kết nhanh</h3>
            <a href="index.php">Trang chủ</a>
            <a href="store.php">Giới thiệu</a>
            <a href="contact.php">Liên hệ</a>
            <a href="policy_return.php">Chính sách</a>
        </div>

        <div class="footer_box">
            <h3>Tài khoản</h3>
            <a href="login.php">Đăng nhập</a>
            <a href="register.php">Đăng ký</a>
            <a href="cart.php">Giỏ hàng</a>
            <a href="order.php">Đơn hàng của tôi</a>
        </div>
    </div>

    <p class="credit">
        © 2025 Babo BookStore — All Rights Reserved.
    </p>
</section>
<style>
    /* --- 1. NÚT LIÊN HỆ (Góc phải) --- */
    .fc-toggle-btn {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 55px;
        height: 55px;
        background: #17584b; /* Xanh Babo */
        color: #fff;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 24px;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(23, 88, 75, 0.4);
        z-index: 10000;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    /* Hiệu ứng rung */
    @keyframes pulse-green {
        0% { box-shadow: 0 0 0 0 rgba(23, 88, 75, 0.7); }
        70% { box-shadow: 0 0 0 15px rgba(23, 88, 75, 0); }
        100% { box-shadow: 0 0 0 0 rgba(23, 88, 75, 0); }
    }
    .fc-toggle-btn.pulse { animation: pulse-green 2s infinite; }
    .fc-toggle-btn:hover { transform: scale(1.1); background: #0e3c32; }
    
    /* Khi mở menu: xoay thành dấu X đỏ */
    .fc-toggle-btn.active {
        transform: rotate(45deg);
        background: #d33;
        box-shadow: none;
        animation: none;
    }

    /* Danh sách icon liên hệ con */
    .contact-floating {
        position: fixed;
        bottom: 100px;
        right: 38px;
        display: flex;
        flex-direction: column;
        gap: 15px;
        z-index: 9999;
        opacity: 0;
        visibility: hidden;
        transform: translateY(20px);
        transition: all 0.3s ease;
    }
    .contact-floating.show { opacity: 1; visibility: visible; transform: translateY(0); }

    .contact-floating .cf-item {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        color: #fff;
        font-size: 18px;
        text-decoration: none;
        position: relative;
        transition: 0.2s;
    }
    .cf-call { background: #34c759; }
    .cf-mail { background: #ffb400; }
    .cf-mess { background: #0084FF; }
    .cf-zalo { background: #0068FF; }
    
    /* Tooltip */
    .contact-floating .cf-item::after {
        content: attr(title);
        position: absolute;
        right: 50px;
        background: rgba(0,0,0,0.7);
        color: #fff;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        white-space: nowrap;
        opacity: 0;
        pointer-events: none;
        transition: 0.2s;
    }
    .contact-floating .cf-item:hover::after { opacity: 1; right: 55px; }


    /* --- 2. NÚT LÊN ĐẦU TRANG (Scroll Top) --- */
    .scroll-top-btn {
        position: fixed;
        bottom: 35px; /* Ngang hàng với nút liên hệ */
        right: 100px; /* Cách nút liên hệ một khoảng về bên trái */
        width: 45px;
        height: 45px;
        background: #fff; /* Nền trắng */
        color: #17584b;   /* Icon xanh */
        border: 2px solid #17584b;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 20px;
        cursor: pointer;
        z-index: 9990;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        
        /* Mặc định ẩn */
        opacity: 0;
        visibility: hidden;
        transform: translateY(20px);
        transition: all 0.3s ease;
    }

    .scroll-top-btn:hover {
        background: #17584b;
        color: #fff;
        transform: translateY(-5px);
    }

    /* Class để hiện nút khi cuộn xuống */
    .scroll-top-btn.show {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    /* Mobile Responsive */
    @media (max-width: 480px) {
        .fc-toggle-btn { right: 20px; bottom: 20px; width: 50px; height: 50px; }
        .contact-floating { right: 25px; bottom: 80px; }
        .scroll-top-btn { right: 80px; bottom: 22px; width: 45px; height: 45px; } /* Đẩy sát vào hơn trên mobile */
    }
</style>

<div id="scrollTopBtn" class="scroll-top-btn" title="Lên đầu trang">
    <i class="fas fa-arrow-up"></i>
</div>

<div class="contact-floating" id="contactList">
    <a href="tel:0901234567" class="cf-item cf-call" title="Gọi ngay">
        <i class="fas fa-phone"></i>
    </a>
    <a href="mailto:babo@gmail.com" class="cf-item cf-mail" title="Gửi mail">
        <i class="fas fa-envelope"></i>
    </a>
    <a href="https://www.facebook.com/messages/t/99839463868" target="_blank" class="cf-item cf-mess" title="Messenger">
        <i class="fab fa-facebook-messenger"></i>
    </a>
    <a href="https://zalo.me/0901234567" target="_blank" class="cf-item cf-zalo" title="Zalo">
        <span style="font-weight:bold; font-family:sans-serif;">Z</span>
    </a>
</div>

<div class="fc-toggle-btn pulse" id="contactToggle" title="Liên hệ">
    <i class="fas fa-headset" id="toggleIcon"></i>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // --- XỬ LÝ NÚT LIÊN HỆ ---
        const toggleBtn = document.getElementById("contactToggle");
        const contactList = document.getElementById("contactList");
        const icon = document.getElementById("toggleIcon");

        toggleBtn.addEventListener("click", function() {
            contactList.classList.toggle("show");
            toggleBtn.classList.toggle("active");

            if (contactList.classList.contains("show")) {
                icon.className = "fas fa-plus"; 
                toggleBtn.classList.remove("pulse");
            } else {
                icon.className = "fas fa-headset";
                toggleBtn.classList.add("pulse");
            }
        });

        // --- XỬ LÝ NÚT SCROLL TO TOP ---
        const scrollBtn = document.getElementById("scrollTopBtn");

        // 1. Lắng nghe sự kiện cuộn chuột
        window.addEventListener("scroll", function() {
            // Nếu cuộn xuống quá 300px thì hiện nút
            if (window.pageYOffset > 300) {
                scrollBtn.classList.add("show");
            } else {
                scrollBtn.classList.remove("show");
            }
        });

        // 2. Sự kiện click để lướt lên
        scrollBtn.addEventListener("click", function() {
            window.scrollTo({
                top: 0,
                behavior: "smooth" // Hiệu ứng lướt mượt mà
            });
        });
    });
</script>
