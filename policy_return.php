<?php
$page_title = "Chính sách đổi trả | Bookiee";
include 'header.php';
?>

<style>
/* ================== POLICY RETURN STYLE ================== */

.policy-container {
    max-width: 900px;
    margin: 50px auto;
    background: #fdfdfd;
    padding: 40px 35px;
    border-radius: 12px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #2c3e50;
    line-height: 1.8;
}

/* ===== TITLE ===== */
.policy-container h1 {
    text-align: center;
    color: #1a1a1a;
    margin-bottom: 35px;
    font-size: 32px;
    letter-spacing: 1px;
}

/* ===== SECTION TITLE ===== */
.policy-container h2 {
    color: #34495e;
    margin-top: 30px;
    font-size: 22px;
    border-left: 4px solid #3498db;
    padding-left: 12px;
    padding-bottom: 4px;
}

/* ===== TEXT ===== */
.policy-container p,
.policy-container li {
    font-size: 16px;
}

.policy-container ul,
.policy-container ol {
    margin-left: 25px;
}

/* ===== HIGHLIGHT ===== */
.highlight {
    background: #fff9e6;
    border-left: 5px solid #f39c12;
    padding: 15px 20px;
    margin: 20px 0;
    font-weight: 500;
    border-radius: 6px;
}

/* ===== CONTACT BOX ===== */
.contact-box {
    background: #e8f6f3;
    padding: 20px;
    border-radius: 8px;
    margin-top: 30px;
}

.contact-box p {
    margin: 8px 0;
}
</style>

<div class="policy-container">
    <h1>CHÍNH SÁCH ĐỔI TRẢ SÁCH ONLINE</h1>
    <link rel="stylesheet" href="CSS/style.css">
    <link rel="stylesheet" href="CSS/order.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <h2>1. Mục đích</h2>
    <p>
        Chính sách đổi trả được xây dựng nhằm đảm bảo quyền lợi của khách hàng khi mua sách online,
        đồng thời quy định rõ trách nhiệm của shop trong quá trình bán hàng.
    </p>

    <h2>2. Điều kiện áp dụng đổi/trả</h2>
    <ul>
        <li>Sách bị lỗi in ấn: thiếu trang, trùng trang, mờ chữ.</li>
        <li>Sách bị hư hỏng do quá trình vận chuyển.</li>
        <li>Giao sai sách so với đơn hàng đã đặt.</li>
    </ul>

    <p class="highlight">
        ⏰ Thời gian tiếp nhận yêu cầu đổi/trả: <b>03 ngày</b> kể từ khi khách hàng nhận được sách.
    </p>

    <h2>3. Trách nhiệm của shop</h2>
    <p>
        Trong trường hợp sách có vấn đề do lỗi in ấn, lỗi đóng gói hoặc giao sai sản phẩm,
        <b>shop chịu hoàn toàn trách nhiệm</b>, bao gồm việc đổi/trả sách và chi phí phát sinh liên quan.
    </p>

    <h2>4. Trường hợp không hỗ trợ đổi/trả</h2>
    <ul>
        <li>Sách đã qua sử dụng, có dấu hiệu viết, vẽ, gấp trang.</li>
        <li>Sách bị hư hỏng do lỗi từ phía khách hàng.</li>
        <li>Khách hàng thay đổi nhu cầu cá nhân.</li>
        <li>Yêu cầu đổi/trả quá thời hạn quy định.</li>
    </ul>

    <h2>5. Quy trình đổi/trả</h2>
    <ol>
        <li>Khách hàng liên hệ shop qua trang <b>Liên hệ</b>, <b>SĐT</b> hoặc <b>Zalo</b>.</li>
        <li>Cung cấp mã đơn hàng, lý do đổi/trả và hình ảnh minh chứng (nếu có).</li>
        <li>Shop xác nhận và hướng dẫn các bước xử lý tiếp theo.</li>
    </ol>

    <h2>6. Phí vận chuyển</h2>
    <ul>
        <li>Khách hàng <b>tự chịu phí vận chuyển</b> khi gửi sách đổi/trả về shop.</li>
        <li>Trường hợp sách có lỗi từ shop, <b>shop sẽ chịu toàn bộ chi phí</b>.</li>
    </ul>

    <h2>7. Trách nhiệm của khách hàng</h2>
    <ul>
        <li>Đóng gói sách cẩn thận trước khi gửi lại.</li>
        <li>Chịu trách nhiệm với rủi ro vận chuyển nếu không phải lỗi từ shop.</li>
    </ul>

    <h2>8. Thông tin liên hệ</h2>
    <div class="contact-box">
        <p>📞 <b>SĐT:</b>  0901234567</p>
        <p>💬 <b>Zalo:</b> 0901234567</p>
        <p>🌐 <b>Trang liên hệ:</b>
        <a href="contact.php">Liên hệ với Bookiee</a>
        </p>

    </div>
</div>

<?php include 'footer.php'; ?>
