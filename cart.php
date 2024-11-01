<?php
session_start();
require_once 'db_connect.php';


// Initialize variables
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$wishlistCount = isset($_SESSION['wishlist_count']) ? $_SESSION['wishlist_count'] : 0;
$cartCount = isset($_SESSION['cart_count']) ? $_SESSION['cart_count'] : 0;
$cartItems = [];
$subtotal = 0;


// Debug information
error_log("User ID: " . print_r($userId, true));
error_log("Guest Cart: " . print_r($_SESSION['guest_cart'] ?? [], true));


// Fetch cart items
if ($userId !== null) {
// For logged-in users
$stmt = $pdo->prepare("SELECT c.*, p.name, p.price, p.image_path FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif (isset($_SESSION['guest_cart']) && !empty($_SESSION['guest_cart'])) {
// For guest users
$guestCartIds = array_values($_SESSION['guest_cart']); // Use array_values to reset array keys
$placeholders = implode(',', array_fill(0, count($guestCartIds), '?'));
$stmt = $pdo->prepare("SELECT id, name, price, image_path FROM products WHERE id IN ($placeholders)");
$stmt->execute($guestCartIds);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Reconstruct cart items with correct quantities
foreach ($products as $product) {
$cartItems[] = [
'id' => $product['id'],
'name' => $product['name'],
'price' => $product['price'],
'image_path' => $product['image_path'],
'quantity' => 1 // Set default quantity to 1 for guest cart
];
}
}


error_log("Cart Items: " . print_r($cartItems, true));


// Calculate subtotal
foreach ($cartItems as $item) {
$subtotal += $item['price'] * $item['quantity'];
}


$shipping = $subtotal > 100 ? 0 : 10;
$total = $subtotal + $shipping;


// Display debug information
//echo "<pre>";
//echo "User ID: " . print_r($userId, true) . "\n";
//echo "Guest Cart: " . print_r($_SESSION['guest_cart'] ?? [], true) . "\n";
//echo "Cart Items: " . print_r($cartItems, true) . "\n";
//echo "</pre>";


?>


<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Shopping Cart - P's Sassy Spot</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@300;400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
:root {
--primary-color: #914c4c;
--gold-color: #d4af37;
--text-color: #333;
--background-color: #fff;
}
body {
font-family: 'Urbanist', sans-serif;
color: var(--text-color);
background-color: var(--background-color);
margin: 0;
padding: 0;
line-height: 1.6;
background: linear-gradient(45deg, #f6f9fc, #e9f2f9, #d8e8f3);
}
header {
background-color: #d3d3d3;
color: var(--background-color);
padding: 0.5rem 0;
}

nav {
display: flex;
justify-content: space-between;
align-items: center;
}
.navbar {
background-color: #d3d3d3;
}

.logo {
font-size: 1.5rem;
font-weight: bold;
}

nav ul {
display: flex;
list-style-type: none;
}

nav ul li {
margin-left: 1rem;
}

nav ul li a {
color: var(--background-color);
text-decoration: none;
}
.nav-link:not(.social-icons a), .footer-section ul li a {
position: relative;
text-decoration: none;
}


.nav-link:not(.social-icons a)::before, .nav-link:not(.social-icons a)::after,
.footer-section ul li a::before, .footer-section ul li a::after {
content: '';
position: absolute;
width: 0;
height: 2px;
bottom: -2px;
background: #000000;
transition: width 0.3s ease;
}


.nav-link:not(.social-icons a)::before, .footer-section ul li a::before {
left: 50%;
}


.nav-link:not(.social-icons a)::after, .footer-section ul li a::after {
right: 50%;
}


.nav-link:not(.social-icons a):hover::before, .nav-link:not(.social-icons a):hover::after,
.footer-section ul li a:hover::before, .footer-section ul li a:hover::after {
width: 50%;
} 
.nav-link{
font-weight: bold;
}
footer {
background-color: var(--primary-color);
color: var(--background-color);
padding: 3rem 0 1rem;
margin-top:0;
}
.footer-section h4 {
color: var(--gold-color);
margin-bottom: 1rem;
}
.footer-content {
display: flex;
justify-content: space-between;
}

.footer-section {
flex: 1;
margin-right: 2rem;
}
.footer-section ul {
list-style-type: none;
padding: 0;
}
.footer-section ul li {
margin-bottom: 0.5rem;
}

.footer-section ul li a {
color: var(--background-color);
text-decoration: none;
}

.footer-bottom {
text-align: center;
margin-top: 2rem;
padding-top: 1rem;
border-top: 1px solid rgba(255, 255, 255, 0.1);
}
.social-icons {
margin-top: 1rem;
}
.social-icons a {
color: var(--background-color);
font-size: 1.5rem;
margin-right: 1rem;
}
.footer-section ul li a::before,
.footer-section ul li a::after {
background: #ffffff;
}
.cart-page {
padding: 2rem 0;
}

.cart-container {
display: flex;
gap: 2rem;
}

.cart-items {
flex: 2;
}

.cart-summary {
flex: 1;
}
.badge {
position: absolute;
top: -8px;
right: -8px;
background-color: red;
color: white;
border-radius: 50%;
padding: 2px 6px;
font-size: 0.7rem;
}
.cart-item {
display: flex;
align-items: center;
margin-bottom: 1rem;
padding: 1rem;
background-color: #fff;
border-radius: 8px;
box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.cart-item img {
width: 80px;
height: 80px;
object-fit: cover;
margin-right: 1rem;
}
.cart-item-details {
flex-grow: 1;
}
.cart-item-actions {
display: flex;
align-items: center;
}
.quantity-input {
width: 50px;
text-align: center;
margin: 0 0.5rem;
}
</style>
</head>
<body>
<header>
<nav class="navbar navbar-expand-lg navbar-dark">
<div class="container">
<a class="navbar-brand" href="index.php"><img src="logo.png" alt="P's Sassy Spot Logo" height="60"></a>
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
<span class="navbar-toggler-icon"></span>
</button>
<div class="collapse navbar-collapse" id="navbarNav">
<ul class="navbar-nav me-auto">
<li class="nav-item"><a class="nav-link text-dark" href="index.php">Home</a></li>
<li class="nav-item"><a class="nav-link text-dark" href="shop.php">Shop</a></li>
</ul>
<div class="d-flex align-items-center">
<a href="wishlist.php" class="nav-link me-3 position-relative">
<img src="icons/wishlist.png" alt="Wishlist" width="20" height="20">
<?php if ($wishlistCount > 0): ?>
<span class="badge"><?php echo $wishlistCount; ?></span>
<?php endif; ?>
</a>
<a href="cart.php" class="nav-link me-3 position-relative">
<img src="icons/cart.png" alt="Cart" width="20" height="20">
<?php if ($cartCount > 0): ?>
<span class="badge"><?php echo $cartCount; ?></span>
<?php endif; ?>
</a>
<a href="login.html" class="nav-link me-3"><img src="icons/user.png" alt="User" width="20" height="20"></a>
</div>
</div>
</div>
</nav>
</header>


<main class="cart-page">
<div class="container">
<h1 class="my-4">Your Shopping Cart</h1>
<div class="row">
<div class="col-md-8">
<?php if (empty($cartItems)): ?>
<p>Your cart is empty.</p>
<?php else: ?>
<?php foreach ($cartItems as $item): ?>
<div class="cart-item">
<img src="<?php echo htmlspecialchars($item['image_path'] ?? ''); ?>" alt="<?php echo htmlspecialchars($item['name'] ?? 'Product Image'); ?>">
<div class="cart-item-details">
<h3><?php echo htmlspecialchars($item['name'] ?? 'Unknown Product'); ?></h3>
<p>Price: Shs <?php echo number_format($item['price'] ?? 0); ?></p>
</div>
<div class="cart-item-actions">
<button class="btn btn-sm btn-secondary decrease-quantity">-</button>
<input type="number" class="quantity-input" 
 value="<?php echo isset($item['quantity']) ? intval($item['quantity']) : 1; ?>" 
 min="1" 
 data-product-id="<?php echo isset($item['id']) ? htmlspecialchars($item['id']) : ''; ?>">
<button class="btn btn-sm btn-secondary increase-quantity">+</button>
<button class="btn btn-sm btn-danger ms-2 remove-item" 
data-product-id="<?php echo isset($item['id']) ? htmlspecialchars($item['id']) : ''; ?>">
Remove
</button>
</div>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>
<div class="col-md-4">
<div class="cart-summary">
<h2>Order Summary</h2>
<p>Subtotal: <span id="subtotal">Shs <?php echo number_format($subtotal); ?></span></p>
<p>Shipping: <span id="shipping">Shs <?php echo number_format($shipping); ?></span></p>
<p>Total: <span id="total">Shs <?php echo number_format($total); ?></span></p>
<input type="text" id="discount-code" class="form-control mb-2" placeholder="Enter discount code">
<button id="apply-discount" class="btn btn-secondary mb-2">Apply</button>
<button id="checkout" class="btn btn-primary">Proceed to Checkout</button>
<button id="clear-cart" class="btn btn-danger mt-2">Clear Cart</button>
</div>
</div>
</div>
</div>
</main>


<footer class="mt-5">
<div class="container">
<div class="row">
<div class="col-md-3 footer-section">
<h4>Quick Links</h4>
<ul class="list-unstyled">
<li><a href="index.php">Home</a></li>
<li><a href="contact.html">Contact</a></li>
<li><a href="blog.html">Blog</a></li>
</ul>
</div>
<div class="col-md-3 footer-section">
<h4>Help</h4>
<ul class="list-unstyled">
<li><a href="shipping.html">Shipping</a></li>
<li><a href="returns.html">Returns</a></li>
<li><a href="sizing.html">Sizing Guide</a></li>
<li><a href="track-order.html">Track Order</a></li>
</ul>
</div>
<div class="col-md-3 footer-section">
<h4>FAQ</h4>
<ul class="list-unstyled">
<li><a href="faq.html#payment">Payment Options</a></li>
<li><a href="faq.html#delivery">Delivery Time</a></li>
<li><a href="faq.html#returns">Return Policy</a></li>
<li><a href="faq.html#warranty">Warranty</a></li>
</ul>
</div>
<div class="col-md-3 footer-section">
<h4>Connect With Us</h4>
<div class="social-icons">
<a href="#"><i class="fab fa-facebook"></i></a>
<a href="#"><i class="fab fa-instagram"></i></a>
<a href="#"><i class="fab fa-twitter"></i></a>
<a href="#"><i class="fab fa-pinterest"></i></a>
</div>
</div>
</div>
</div>
<div class="text-center mt-4">
<p>&copy; 2024 P's Sassy Spot. All rights reserved.</p>
</div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>


<script>
 document.addEventListener('DOMContentLoaded', function() {
const quantityInputs = document.querySelectorAll('.quantity-input');
const removeButtons = document.querySelectorAll('.remove-item');
const clearCartButton = document.getElementById('clear-cart');


quantityInputs.forEach(input => {
input.addEventListener('change', updateQuantity);
});


removeButtons.forEach(button => {
button.addEventListener('click', removeItem);
});


document.querySelectorAll('.increase-quantity').forEach(btn => {
btn.addEventListener('click', function() {
const input = this.previousElementSibling;
input.value = parseInt(input.value) + 1;
updateQuantity.call(input);
});
});


document.querySelectorAll('.decrease-quantity').forEach(btn => {
btn.addEventListener('click', function() {
const input = this.nextElementSibling;
if (parseInt(input.value) > 1) {
input.value = parseInt(input.value) - 1;
updateQuantity.call(input);
}
});
});


function updateQuantity() {
const productId = this.dataset.productId;
const quantity = this.value;
fetch('update_quantity.php', {
method: 'POST',
headers: {
'Content-Type': 'application/x-www-form-urlencoded',
},
body: `product_id=${productId}&quantity=${quantity}`
})
.then(response => response.json())
.then(data => {
if (data.success) {
updateCartTotals(data);
updateCartCount(data.cartCount);
} else {
alert('Failed to update quantity');
}
});
}


function removeItem() {
const productId = this.dataset.productId;
fetch('remove_item.php', {
method: 'POST',
headers: {
'Content-Type': 'application/x-www-form-urlencoded',
},
body: `product_id=${productId}`
})
.then(response => response.json())
.then(data => {
if (data.success) {
this.closest('.cart-item').remove();
updateCartTotals(data);
updateCartCount(data.cartCount);
} else {
alert('Failed to remove item');
}
});
}


clearCartButton.addEventListener('click', function() {
    if (confirm('Are you sure you want to clear your cart?')) {
        fetch('clear_cart.php', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.querySelector('.col-md-8').innerHTML = '<p>Your cart is empty.</p>';
                updateCartTotals(data);
                updateCartCount(data.cartCount);
            } else {
                alert('Failed to clear cart');
            }
        });
    }
});

function updateCartCount(count) {
    const cartBadge = document.querySelector('.badge');
    if (cartBadge) {
        if (count > 0) {
            cartBadge.textContent = count;
            cartBadge.style.display = 'inline';
        } else {
            cartBadge.style.display = 'none';
        }
    }
}



function updateCartTotals(data) {
document.getElementById('subtotal').textContent = `Shs.${data.subtotal}`;
document.getElementById('shipping').textContent = `Shs.${data.shipping}`;
document.getElementById('total').textContent = `Shs.${data.total}`;
}


document.getElementById('checkout').addEventListener('click', () => {
window.location.href = 'checkout.php';
});
});
</script>
</body>
</html>