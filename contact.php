<?php
include 'connect.php';
session_start();

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// 2. Lấy thông tin user từ Database
$q = mysqli_query($ocon, "SELECT * FROM users WHERE user_id = $user_id LIMIT 1");
$user = mysqli_fetch_assoc($q);

// 3. Gán giá trị mặc định để đưa vào Form (Dựa trên kết quả bạn vừa gửi)
$prefill_name = isset($user['full_name']) ? $user['full_name'] : '';
$prefill_email = isset($user['email']) ? $user['email'] : '';

// --- CẤU HÌNH THÔNG TIN CỬA HÀNG ---
$store_name = "Babo Bookstore";
$store_phone = "0901234567"; 
$store_email = "moodnme@gmail.com";
$store_address = "Số 12 Chùa Bộc, Phường Kim Liên, Hà Nội";
$facebook_link = "https://www.facebook.com/profile.php?id=61584584241326";
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liên hệ</title>
    <link rel="stylesheet" href="CSS/style.css">
    <link rel="stylesheet" href="CSS/contact.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<?php include 'header.php'; ?>

<div class="banner">
    <h2>Liên hệ</h2>
</div>

<div class="cont">
    <div class="contact-header">
        <p>Liên hệ với <?php echo $store_name; ?> nếu bạn cần hỗ trợ!</p>
    </div>

    <div class="contact-wrapper">
        <div class="contact-grid">

            <div class="left-wrapper">
                <div class="info-boxes">
                    <div class="info-box">
                        <i class="fa-solid fa-phone"></i>
                        <a href="tel:<?php echo str_replace(' ', '', $store_phone); ?>"><?php echo $store_phone; ?></a>
                    </div>
                    <div class="info-box">
                        <i class="fa-solid fa-envelope"></i>
                        <a href="mailto:<?php echo $store_email; ?>">Babo@gmail.com</a>
                    </div>
                    <div class="info-box">
                        <a href="<?php echo $facebook_link; ?>" target="_blank">
                            <i class="fa-brands fa-facebook"></i>
                            <p style="color:#555;"><?php echo $store_name; ?></p>
                        </a>
                    </div>
                    <div class="info-box">
                        <i class="fa-solid fa-store"></i>
                        <p><?php echo $store_address; ?></p>
                    </div>
                </div>
                <div class="map-box">
                     <iframe src="https://maps.google.com/maps?q=12%20Chua%20Boc%20Ha%20Noi&t=&z=15&ie=UTF8&iwloc=&output=embed" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe>
                </div>
            </div>

            <div class="right-wrapper">
                <h3 class="contact-title">Liên hệ với chúng mình tại đây!</h3>
                
                <div class="contact-form">
                    <form id="contact_form"> 
                        <input type="text" id="contact_name" name="contact_name" placeholder="Họ và tên" required 
                               value="<?php echo $prefill_name; ?>">
                        
                        <input type="email" id="contact_email" name="contact_email" placeholder="Email" required 
                               value="<?php echo $prefill_email; ?>">
                        
                        <input type="text" id="contact_title" name="contact_title" placeholder="Tiêu đề" required>
                        
                        <textarea id="contact_message" name="contact_message" placeholder="Nội dung lời nhắn..." rows="5" required></textarea>
                        
                        <button type="submit" id="btn_submit">Gửi ngay</button>
                    </form>
                    
                    <div id="form_status" style="margin-top: 15px; font-weight: bold; text-align: center; min-height: 20px;"></div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/emailjs-com@3/dist/email.min.js"></script>
<script>
  (function(){
      // Khởi tạo EmailJS
      emailjs.init('TYzIvV_1PwIGOu8oy'); 
  })();

  document.getElementById('contact_form').addEventListener('submit', function(event) {
    event.preventDefault(); // Chặn load lại trang

    const btn = document.getElementById('btn_submit');
    const originalText = btn.innerText;
    const formStatus = document.getElementById('form_status');

    // 1. Hiệu ứng đang gửi
    btn.innerText = "Đang gửi...";
    btn.disabled = true;
    formStatus.innerHTML = ""; 

    // 2. Lấy dữ liệu
    const params = {
      name: document.getElementById('contact_name').value,
      email: document.getElementById('contact_email').value,
      title: document.getElementById('contact_title').value,
      message: document.getElementById('contact_message').value,
      to_email: "moodnme@gmail.com"
    };

    // 3. Gửi đi
    emailjs.send("service_rhgotao", "template_tn3hg1p", params)
      .then(function(response) {
          // THÀNH CÔNG
          formStatus.innerHTML = "<p style='color: green; background: #d4edda; padding: 10px; border-radius: 5px;'>Gửi tin nhắn thành công!</p>";
          
          // Reset form (trừ tên và email để khách đỡ phải nhập lại nếu muốn gửi tiếp)
          document.getElementById('contact_title').value = "";
          document.getElementById('contact_message').value = "";
          
          btn.innerText = originalText;
          btn.disabled = false;

          // Tự biến mất sau 2 giây
          setTimeout(function() {
              formStatus.innerHTML = "";
          }, 2000);

      }, function(error) {
          // THẤT BẠI
          console.log('Lỗi:', error);
          formStatus.innerHTML = "<p style='color: red; background: #f8d7da; padding: 10px; border-radius: 5px;'>Gửi thất bại. Kiểm tra kết nối mạng!</p>";
          
          btn.innerText = originalText;
          btn.disabled = false;
          
          setTimeout(function() {
              formStatus.innerHTML = "";
          }, 3000);
      });
  });
</script>

<?php include 'footer.php'; ?>
</body>
</html>