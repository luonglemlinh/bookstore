<?php
include 'connect.php'; 
session_start(); 

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0; // Set user_id an toàn


?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ | Bookiee</title>
    <link rel="stylesheet" href="CSS/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
<?php if (!empty($_SESSION['cart_msg'])): ?>
    <div class="cart-alert">
        <?= $_SESSION['cart_msg']; ?>
    </div>
    <?php unset($_SESSION['cart_msg']); ?>
<?php endif; ?>
<?php 
include 'header.php'; 
?>

<section class="home_cont">
    <div class="main_descrip">
        <h1>Tìm cuốn sách mới <br> với giá tốt nhất</h1>
         <section class="search_cont">
    <form method="post" class="search_form" autocomplete="off">

        <input type="text" name="search" id="search_input" placeholder="🔍 Nhập tên sách..." required>

        <div id="suggest_box" class="suggest_box" aria-hidden="true"></div>
    </form>
    
</section>
    </div>
</section>

<section class="features_section">
    <div class="feature_item">
        <i class="fas fa-truck-fast"></i>
        <div class="feature_info">
            <h3>Giao hàng nhanh</h3>
            <p>Vận chuyển trong 24h</p>
        </div>
    </div>
    <div class="feature_item">
        <i class="fas fa-shield-alt"></i>
        <div class="feature_info">
            <h3>Thanh toán an toàn</h3>
            <p>Bảo mật 100%</p>
        </div>
    </div>
    <div class="feature_item">
        <i class="fas fa-thumbs-up"></i>
        <div class="feature_info">
            <h3>Chất lượng cao</h3>
            <p>Sách chính hãng</p>
        </div>
    </div>
    <div class="feature_item">
        <i class="fas fa-headset"></i>
        <div class="feature_info">
            <h3>Hỗ trợ 24/7</h3>
            <p>Luôn sẵn sàng</p>
        </div>
    </div>
</section>

<section class="products_cont">
    <h2>
        Sách nổi bật 
    </h2>
    
    <div class="pro_box_cont">
        <?php
        $books = mysqli_query($ocon,
            "SELECT b.*, MIN(i.url) AS image
             FROM books b
             LEFT JOIN images i ON b.book_id = i.book_id
             WHERE b.status='active'
             GROUP BY b.book_id
             ORDER BY b.book_id DESC 
             LIMIT 8" // Sắp xếp theo ID mới nhất và chỉ lấy 8 cuốn
        );

        // 2. HIỂN THỊ SÁCH NẾU CÓ DỮ LIỆU
        if (mysqli_num_rows($books) > 0) {
            while ($b = mysqli_fetch_assoc($books)) {
        ?>
<form action="add_to_cart.php" method="POST" class="pro_box">
    <input type="hidden" name="book_id" value="<?php echo $b['book_id']; ?>">

    <span class="badge hot">Hot</span> 

    <img src="<?php echo $b['image']; ?>">

    <a href="checkout.php?id=<?php echo $b['book_id']; ?>&qty=1" class="buy_now_btn">
    MUA NGAY
    </a>

    <h3><?php echo $b['title']; ?></h3>

    <div class="price_row">
        <span class="price"><?php echo number_format($b['discounted_price'],0,',','.'); ?>₫</span>
        <input type="number" name="quantity" min="1" value="1" class="qty_input">
    </div>

    <div class="product_actions">
        <a href="book_detail.php?id=<?php echo $b['book_id']; ?>" class="product_btn detail_btn">
            <i class="fas fa-eye"></i> Chi tiết
        </a>

        <button type="submit" name="add_to_cart" class="product_btn add_to_cart_btn">
            <i class="fas fa-shopping-cart"></i> Thêm vào giỏ
        </button>
    </div>

</form>

        <?php
            }
        } else {
            // Hiển thị thông báo nếu không có sách
            echo "<p class='empty'>Chưa có sách nào đang hoạt động!</p>";
        }
        ?>
    </div>
</section>
<script>
    setTimeout(() => {
        let alertBox = document.querySelector('.cart-alert');
        if(alertBox){
            alertBox.classList.add('hide');
        }
    }, 2500);
</script>
<?php include 'footer.php'; ?>
<script>
// ---- Debounce helper để giảm request khi gõ ----
function debounce(fn, delay){
    let t;
    return function(...args){
        clearTimeout(t);
        t = setTimeout(()=> fn.apply(this,args), delay);
    }
}

const input = document.getElementById('search_input');
const suggestBox = document.getElementById('suggest_box');

async function fetchSuggestions(keyword){
    if (!keyword || keyword.trim().length === 0) {
        suggestBox.innerHTML = '';
        suggestBox.style.display = 'none';
        return;
    }

    // POST form data
    const form = new FormData();
    form.append('keyword', keyword.trim());

    try {
        const res = await fetch('search_suggest.php', {
            method: 'POST',
            body: form
        });
        const html = await res.text();

        if (html && html.trim().length > 0) {
            suggestBox.innerHTML = html;
            suggestBox.style.display = 'block';
            suggestBox.setAttribute('aria-hidden','false');
        } else {
            suggestBox.innerHTML = '';
            suggestBox.style.display = 'none';
        }
    } catch (err) {
        console.error('Fetch suggest error:', err);
        suggestBox.style.display = 'none';
    }
}

// Debounced handler (wait 220ms after user stops typing)
const onKey = debounce(function(e){
    fetchSuggestions(e.target.value);
}, 220);

input.addEventListener('input', onKey);

// Hide suggestions when clicking outside
document.addEventListener('click', function(e){
    if (!document.querySelector('.search_form').contains(e.target)) {
        suggestBox.style.display = 'none';
    }
});

// Called when user clicks a suggestion
function selectSuggestion(book_id){
    window.location.href = "book_detail.php?id=" + book_id;
}

// When user hits Enter / clicks search button, keep existing POST behavior.
// This function prevents double submission when autocomplete auto-submits.
function submitSearch(){
    // allow form to submit normally (POST) — no JS redirect
    return true;
}
</script>
</body>
</html>
