<?php
include 'connect.php';
session_start();

// --- THÔNG TIN CỬA HÀNG ---
$store_name = "Babo Bookstore";
$store_phone = "0901234567";
$store_email = "babo@gmail.com";
$store_address = "Số 12 Chùa Bộc, Phường Kim Liên, Hà Nội";
$store_hours = "8:00 - 22:00";
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Về chúng tôi - <?php echo $store_name; ?></title>
    <link rel="stylesheet" href="CSS/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* --- CẤU TRÚC CHUNG & MÀU SẮC --- */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; }
        
        :root {
            --main-green: #17584b;    
            --green-dark: #0e3c32;
            --green-light: #e2f3ef;
            --text-gray: #555;
            --white: #ffffff;
        }

        .cont {
            background: #fff; /* Nền trắng sạch sẽ */
            padding-bottom: 60px;
        }

        .wrapper {
            max-width: 1200px;
            margin: auto;
            padding: 0 20px;
        }

        /* --- SECTION 1: CÂU CHUYỆN (ẢNH + CHỮ) --- */
        .story-section {
            display: flex;
            align-items: center;
            gap: 50px;
            padding: 60px 0;
        }

        .story-image {
            flex: 1;
            position: relative;
        }

        /* Tạo khung ảnh bo tròn mềm mại */
        .story-image img {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 20px 0 20px 0; /* Bo chéo nghệ thuật */
            box-shadow: 15px 15px 0px var(--green-light); /* Đổ bóng cứng màu xanh */
        }

        .story-content {
            flex: 1;
        }

        .story-content h2 {
            color: var(--main-green);
            font-size: 36px;
            margin-bottom: 20px;
            font-weight: 800;
            line-height: 1.2;
        }

        .story-content p {
            font-size: 16px;
            line-height: 1.8;
            color: var(--text-gray);
            margin-bottom: 15px;
            text-align: justify;
        }

        .highlight-text {
            color: var(--main-green);
            font-weight: 600;
            font-style: italic;
            font-size: 18px;
            border-left: 4px solid var(--main-green);
            padding-left: 15px;
            margin: 20px 0;
        }

        /* --- SECTION 2: THÔNG TIN & MAP (LAYOUT MỚI) --- */
        .info-section {
            background-color: var(--green-light); /* Nền xanh nhạt tách biệt */
            padding: 50px;
            border-radius: 30px;
            display: flex;
            gap: 40px;
        }

        /* Bên trái: Danh sách thông tin (Không dùng ô vuông nữa) */
        .info-list-card {
            flex: 1;
            background: var(--white);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(23, 88, 75, 0.05);
        }

        .info-list-card h3 {
            color: var(--main-green);
            font-size: 24px;
            margin-top: 0;
            margin-bottom: 30px;
            border-bottom: 2px solid var(--green-light);
            padding-bottom: 15px;
        }

        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
        }

        .info-item:last-child {
            margin-bottom: 0;
        }

        .icon-circle {
            width: 50px;
            height: 50px;
            background: var(--green-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            flex-shrink: 0;
        }

        .icon-circle i {
            color: var(--main-green);
            font-size: 20px;
        }

        .info-text h4 {
            margin: 0 0 5px 0;
            color: var(--green-dark);
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-text span, .info-text a {
            font-size: 16px;
            color: var(--text-gray);
            font-weight: 600;
            text-decoration: none;
        }

        .info-text a:hover {
            color: var(--main-green);
        }

        /* Bên phải: Map */
        .map-container {
            flex: 1.2; /* Map rộng hơn chút */
            border-radius: 20px;
            overflow: hidden;
            border: 5px solid var(--white);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            min-height: 300px;
        }
        
        .map-container iframe {
            width: 100%;
            height: 100%;
            display: block;
        }

        /* --- RESPONSIVE --- */
        @media (max-width: 900px) {
            .story-section { flex-direction: column; }
            .info-section { flex-direction: column-reverse; padding: 30px; }
            .story-image img { height: 300px; }
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="banner">
    <h2 style="font-size: 30px; color: var(--main-green); text-align: center; padding: 20px; background: #e6f7f3;">VỀ CHÚNG TÔI</h2>
</div>

<div class="cont">
    <div class="wrapper">
        
        <div class="story-section">
            <div class="story-content">
                <h2>Chào mừng bạn đến với<br><?php echo $store_name; ?></h2>
                <p>
                    Babo Bookstore không chỉ là một tiệm sách, mà là một trạm dừng chân cho những tâm hồn yêu chữ. 
                    Chúng mình tin rằng mỗi cuốn sách là một cánh cửa mở ra thế giới mới, và Babo ở đây để giúp bạn tìm thấy chiếc chìa khóa của riêng mình.
                </p>
                
                <div class="highlight-text">
                    "Nơi tri thức được nâng niu và cảm xúc được chữa lành."
                </div>

                <p>
                    Hãy ghé thăm chúng mình để đắm chìm trong không gian yên tĩnh, mùi giấy mới và những câu chuyện chưa kể. 
                    Babo luôn có một góc nhỏ bình yên dành cho bạn giữa lòng Hà Nội ồn ã.
                </p>
            </div>
            
            <div class="story-image">
                <img src="images/ab.jpg" alt="Babo Bookstore Interior">
            </div>
        </div>

        <div class="info-section">
            
            <div class="map-container">
                <iframe 
                    src="https://maps.google.com/maps?q=12%20Chua%20Boc%20Ha%20Noi&t=&z=15&ie=UTF8&iwloc=&output=embed"
                    frameborder="0" style="border:0;" allowfullscreen="" loading="lazy">
                </iframe>
            </div>

            <div class="info-list-card">
                <h3>Thông Tin Liên Hệ</h3>
                
                <div class="info-item">
                    <div class="icon-circle"><i class="fa-solid fa-location-dot"></i></div>
                    <div class="info-text">
                        <h4>Địa chỉ</h4>
                        <span><?php echo $store_address; ?></span>
                    </div>
                </div>

                <div class="info-item">
                    <div class="icon-circle"><i class="fa-solid fa-clock"></i></div>
                    <div class="info-text">
                        <h4>Giờ mở cửa</h4>
                        <span><?php echo $store_hours; ?> (Tất cả các ngày)</span>
                    </div>
                </div>

                <div class="info-item">
                    <div class="icon-circle"><i class="fa-solid fa-phone"></i></div>
                    <div class="info-text">
                        <h4>Hotline</h4>
                        <a href="tel:<?php echo $store_phone; ?>"><?php echo $store_phone; ?></a>
                    </div>
                </div>

                <div class="info-item">
                    <div class="icon-circle"><i class="fa-solid fa-envelope"></i></div>
                    <div class="info-text">
                        <h4>Email hỗ trợ</h4>
                        <a href="mailto:<?php echo $store_email; ?>">Babo@gmail.com</a>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>