<?php
// Start the session
session_start();


// Include the database connection file
require_once 'db_connect.php';


// Initialize variables
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$wishlistCount = isset($_SESSION['wishlist_count']) ? $_SESSION['wishlist_count'] : 0;
$cartCount = isset($_SESSION['cart_count']) ? $_SESSION['cart_count'] : 0;
$wishlistItems = [];
$cartItems = [];


// Function to check if a product is in the wishlist
function isInWishlist($product_id) {
if (isset($_SESSION['user_id'])) {
global $pdo;
$stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ? AND product_id = ?");
$stmt->execute([$_SESSION['user_id'], $product_id]);
return $stmt->fetchColumn() > 0;
} else {
return isset($_SESSION['guest_wishlist']) && in_array($product_id, $_SESSION['guest_wishlist']);
}
}

// Function to check if a product is in the cart
function isInCart($product_id) {
    if (isset($_SESSION['user_id'])) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$_SESSION['user_id'], $product_id]);
        return $stmt->fetchColumn() > 0;
    } else {
        return isset($_SESSION['guest_cart']) && in_array($product_id, $_SESSION['guest_cart']);
    }
}


// Fetch wishlist and cart data for logged-in users
if ($userId) {
try {
// Fetch wishlist count if not in session
if (!isset($_SESSION['wishlist_count'])) {
$wishlistSql = "SELECT COUNT(*) FROM wishlist WHERE user_id = ?";
$wishlistStmt = $pdo->prepare($wishlistSql);
$wishlistStmt->execute([$userId]);
$wishlistCount = $wishlistStmt->fetchColumn();
$_SESSION['wishlist_count'] = $wishlistCount;
}


// Fetch cart count if not in session
if (!isset($_SESSION['cart_count'])) {
$cartSql = "SELECT COUNT(*) FROM cart WHERE user_id = ?";
$cartStmt = $pdo->prepare($cartSql);
$cartStmt->execute([$userId]);
$cartCount = $cartStmt->fetchColumn();
$_SESSION['cart_count'] = $cartCount;
}


// Fetch wishlist items
$wishlistItemsSql = "SELECT product_id FROM wishlist WHERE user_id = ?";
$wishlistItemsStmt = $pdo->prepare($wishlistItemsSql);
$wishlistItemsStmt->execute([$userId]);
$wishlistItems = $wishlistItemsStmt->fetchAll(PDO::FETCH_COLUMN);


// Fetch cart items
$cartItemsSql = "SELECT product_id FROM cart WHERE user_id = ?";
$cartItemsStmt = $pdo->prepare($cartItemsSql);
$cartItemsStmt->execute([$userId]);
$cartItems = $cartItemsStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
// Log the error and show a user-friendly message
error_log("Database error: " . $e->getMessage());
echo "We're experiencing technical difficulties. Please try again later.";
exit;
}
}


// Fetch new arrivals
try {
$new_arrivals_sql = "SELECT * FROM products WHERE is_new_arrival = TRUE ORDER BY created_at DESC LIMIT 10";
$new_arrivals_stmt = $pdo->query($new_arrivals_sql);
$new_arrivals = $new_arrivals_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
error_log("Error fetching new arrivals: " . $e->getMessage());
$new_arrivals = [];
}


// Fetch sale items
try {
$sale_items_sql = "SELECT * FROM products WHERE is_sale = TRUE LIMIT 10";
$sale_items_stmt = $pdo->query($sale_items_sql);
$sale_items = $sale_items_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
error_log("Error fetching sale items: " . $e->getMessage());
$sale_items = [];
}


// Fetch categories
try {
$categories_sql = "SELECT * FROM categories LIMIT 3";
$categories_stmt = $pdo->query($categories_sql);
$categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
error_log("Error fetching categories: " . $e->getMessage());
$categories = [];
}


// Close the database connection
$pdo = null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>P's Sassy Spot - Women's Fashion Accessories</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@300;400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
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
}

.container {
max-width: 1160px;
margin-left: auto;
margin-right: auto;
padding-left: 20px;
padding-right: 20px;
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
.input-group {
position: relative;
width:500px;
}


.input-group .form-control {
padding-right: 40px;
}


.input-group .btn {
position: absolute;
right: 0;
z-index: 10;
}


.hero {
display: flex;
justify-content: space-between;
align-items: center;
margin-top:70px;
padding: 1rem 0;
background-color: #f8f8f8;
}

.hero-content {
flex: 1;
padding-right: 2rem;
}
.hero-content h1 {
color: var(--primary-color);
font-size: 2.5rem;
font-weight:bold;
} 
.cta-button {
display: inline-block;
background-color: var(--primary-color);
color: var(--background-color);
padding: 0.5rem 1rem;
text-decoration: none;
border-radius: 4px;
}
.hero-performance span {
margin: 0.5rem 0;
font-size: 0.9rem;
}
.hero-performance {
display: flex;
justify-content: space-between;
align-items: center;
background-color: rgba(255, 255, 255, 0.9);
margin-top: 2rem;
border-radius: 15px;
padding: 20px;
box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}
.performance-item {
display: flex;
align-items: center;
text-align: center;
}
.performance-item i {
margin-right: 10px;
}
.performance-value {
font-size: 1.4rem;
font-weight: bold;
color: var(--primary-color);
display: block;
}
.performance-label {
font-size: 0.9rem;
color: var(--text-color);
display: block;
}
.performance-separator {
width: 1px;
height: 40px;
background-color: #ccc;
margin: 0 20px;
}
.hero-image {
width: 100%;
max-width: 320px;
height: auto;
object-fit: cover;
margin-left: 120px;
}


.section-title {
text-align: center;
margin-bottom: 2rem;
}
h1, h2, h3, h4 {
color: var(--primary-color);
}
.categories-section {
margin-top:0;
padding: 4rem 0;
background: linear-gradient(45deg, #f6f9fc, #e9f2f9, #d8e8f3);
}

.category-grid, .trending-grid {
display: grid;
grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
gap: 2rem;
margin-top: 2rem;
}

.category-item, .trending-item {
text-align: center;
}
.category-item img, .trending-item img {
max-width: 100%;
height: auto;
height: 200px; 
object-fit: cover;
}
.category-card {
position: relative;
overflow: hidden;
border-radius: 15px;
box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
transition: transform 0.3s ease;
}


.category-card:hover {
transform: translateY(-5px);
box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
}
.category-image {
height: 300px;
overflow: hidden;
}
.category-image img {
width: 100%;
height: 100%;
object-fit: cover;
transition: transform 0.3s ease;
}
.category-card:hover .category-image img {
transform: scale(1.1);
}


.category-content {
position: absolute;
bottom: 0;
left: 0;
right: 0;
padding: 2rem;
background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0) 100%);
color: #fff;
text-align: center;
}
.category-content h3 {
font-size: 2rem;
margin-bottom: 1rem;
color:white;
text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
}


.category-content .btn {
background-color: rgba(255, 255, 255, 0.2);
border-color: #fff;
color: #fff;
transition: all 0.3s ease;
}


.category-content .btn:hover {
background-color: #fff;
color: #000;
}




.product-grid {
display: grid;
grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
gap: 2rem;
}


.product-item {
position: relative;
background-color: #fff;
border-radius: 10px;
box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
overflow: hidden;
transition: transform 0.3s ease;
}


.product-item:hover {
transform: translateY(-5px);
}


.product-item img {
width: 100%;
height: 200px;
object-fit: cover;
}


.product-info {
padding: 1rem;
}


.product-info h4 {
font-size: 1.1rem;
margin: 0 0 0.5rem;
}


.product-info p {
font-size: 1rem;
color: #914c4c;
font-weight: bold;
margin: 0 0 0.5rem;
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

 
.trending-item {
text-align: center;
margin-bottom: 2rem;
}

.trending-item h3 {
margin-bottom: 1rem;
}

.product-grid {
display: grid;
grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
gap: 1.2rem;
}

.product-item {
position:relative;
background-color: #fff;
border-radius: 8px;
box-shadow: 0 2px 4px rgba(0,0,0,0.1);
overflow: hidden;
transition: transform 0.3s ease;
display: flex;
flex-direction: column;
}

.product-item:hover {
transform: none;
}

.product-item img {
width: 100%;
height: 250px;
object-fit: cover;
transition: transform 0.3s ease;
}
.product-item:hover img {
transform: scale(1.1);
}
.product-icons {
position: absolute;
top: 10px;
left: 10px;
right: 10px;
display: flex;
justify-content: space-between;
opacity: 0;
transition: opacity 0.3s ease;
}
.wishlist-icon,.cart-icon{
background-color: #fff;
border: none;
padding: 0.5rem;
border-radius: 50%;
cursor:pointer;
}
.product-item:hover .product-icons {
opacity: 1;
}

.product-info {
padding: 0.5rem;
flex-grow: 1;
display: flex;
flex-direction: column;
justify-content: space-between;
}

.product-info h4 {
font-size: 1rem;
margin: 0.5rem 0;
}

.product-info p {
font-size: 0.9rem;
color: #914c4c;
font-weight: bold;
margin: 0;
}


.category-card img {
width: 100%;
height: 350px;
object-fit: cover;
}
.sale-price {
display: flex;
align-items: center;
justify-content: center;
}


.original-price {
color: red;
text-decoration: line-through;
margin-right: 0.5rem;
}


.discounted-price {
color: black;
font-weight: bold;
}
[data-aos] {
opacity: 0;
transition-property: opacity, transform;
}


[data-aos].aos-animate {
opacity: 1;
}


[data-aos="fade-up"] {
transform: translateY(100px);
}


[data-aos="fade-up"].aos-animate {
transform: translateY(0);
}
.newsletter-section {
background-color: #000000;
padding: 3rem 0;
color: #fff;
margin-top: 3rem;
border-radius: 40px;
margin-left: 50px;
margin-right: 50px
}
.newsletter-title {
font-size: 2.5rem;
color:#ffffff;
font-weight: bold;
margin-bottom: 1rem;
border-radius: 15px;
margin-left: 20px;
margin-right: 20px;
}
.newsletter-text {
font-size: 1.1rem;
margin-bottom: 0;
}


.newsletter-content {
text-align: center;
}


.newsletter-form {
max-width: 600px;
margin: 0 auto;
}
.input-group {
max-width: 400px;
margin: 0 auto;
}
.newsletter-form .input-group-text {
background-color: #fff;
}


.newsletter-form .form-control {
border-left: none;
border-radius: 25px;
padding-right: 100px;
}
.newsletter-form .input-group {
max-width: 400px;
margin: 0 auto;
position: relative;
}


.newsletter-form input {
width: 50%;
padding: 10px;
border: 1px solid #ddd;
border-radius: 4px 0 0 4px;
}


.newsletter-form .btn-dark {
width: 30%;
padding: 11.3px;
color: white;
border: none;
cursor: pointer;
border-radius:0;
}
.reviews-section {
background-color: #f8f8f8;
padding: 3rem 0;
}


.review-card {
background-color: #000;
color: #fff;
border-radius: 10px;
padding: 2rem;
box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
height: 100%;
transition: all 0.3s ease;
}


.review-card:hover {
transform: translateY(-5px);
box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
}


.stars {
color: #ffc107;
margin-bottom: 1rem;
}


.review-text {
font-style: italic;
margin-bottom: 1rem;
}


.reviewer {
font-weight: bold;
color: #d4af37;
}
.footer-section ul li a::before,
.footer-section ul li a::after {
background: #ffffff;
}
.brands-section {
position: relative;
padding: 4rem 0;
background: linear-gradient(135deg, #43cea2 0%, #185a9d 100%);
overflow: hidden;
margin-bottom:0;


}

.brands-section h2 {
color: #fff;
position: relative;
z-index: 2;
}


.brand-card {
background-color: rgba(255, 255, 255, 0.9);
border-radius: 15px;
padding: 2rem;
text-align: center;
transition: all 0.3s ease;
box-shadow: 0 5px 15px rgba(0,0,0,0.08);
position: relative;
overflow: hidden;
z-index: 2;
}


.brand-card:hover {
transform: translateY(-5px);
box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}


.brand-logo {
max-width: 120px;
height: auto;
margin-bottom: 1.5rem;
transition: all 0.3s ease;
}
.brand-name {
font-weight: 600;
margin-bottom: 0;
font-size: 1.2rem;
color: #333;
}
.circles {
position: absolute;
top: 0;
left: 0;
width: 100%;
height: 100%;
overflow: hidden;
margin: 0;
padding: 0;
}


.circles li {
position: absolute;
display: block;
list-style: none;
width: 20px;
height: 20px;
background: rgba(255, 255, 255, 0.2);
animation: animate 25s linear infinite;
bottom: -150px;
}


.circles li:nth-child(1){
left: 25%;
width: 80px;
height: 80px;
animation-delay: 0s;
}


.circles li:nth-child(2){
left: 10%;
width: 20px;
height: 20px;
animation-delay: 2s;
animation-duration: 12s;
}


.circles li:nth-child(3){
left: 70%;
width: 20px;
height: 20px;
animation-delay: 4s;
}


.circles li:nth-child(4){
left: 40%;
width: 60px;
height: 60px;
animation-delay: 0s;
animation-duration: 18s;
}


.circles li:nth-child(5){
left: 65%;
width: 20px;
height: 20px;
animation-delay: 0s;
}


.circles li:nth-child(6){
left: 75%;
width: 110px;
height: 110px;
animation-delay: 3s;
}


.circles li:nth-child(7){
left: 35%;
width: 150px;
height: 150px;
animation-delay: 7s;
}


.circles li:nth-child(8){
left: 50%;
width: 25px;
height: 25px;
animation-delay: 15s;
animation-duration: 45s;
}


.circles li:nth-child(9){
left: 20%;
width: 15px;
height: 15px;
animation-delay: 2s;
animation-duration: 35s;
}


.circles li:nth-child(10){
left: 85%;
width: 150px;
height: 150px;
animation-delay: 0s;
animation-duration: 11s;
}


@keyframes animate {
0% {
transform: translateY(0) rotate(0deg);
opacity: 1;
border-radius: 0;
}
100% {
transform: translateY(-1000px) rotate(720deg);
opacity: 0;
border-radius: 50%;
}
}
/* Specific hover effects for each brand */
.brand-card:nth-child(1):hover { box-shadow: 0 8px 25px rgba(0,0,0,0.1), 0 0 0 5px rgba(240,234,214,0.5); }
.brand-card:nth-child(2):hover { box-shadow: 0 8px 25px rgba(0,0,0,0.1), 0 0 0 5px rgba(230,230,250,0.5); }
.brand-card:nth-child(3):hover { box-shadow: 0 8px 25px rgba(0,0,0,0.1), 0 0 0 5px rgba(253,245,230,0.5); }
.brand-card:nth-child(4):hover { box-shadow: 0 8px 25px rgba(0,0,0,0.1), 0 0 0 5px rgba(224,255,255,0.5); }
.brand-card:nth-child(5):hover { box-shadow: 0 8px 25px rgba(0,0,0,0.1), 0 0 0 5px rgba(255,240,245,0.5); }
.brand-card:nth-child(6):hover { box-shadow: 0 8px 25px rgba(0,0,0,0.1), 0 0 0 5px rgba(240,255,240,0.5); }


.brand-card:hover .brand-logo {
animation: rotate-clockwise 2s linear infinite;
}


@keyframes rotate-clockwise {
from {
transform: rotate(0deg);
}
to {
transform: rotate(360deg);
}
}
.nav-link{
font-weight: bold;
}

</style>
</head>
<body>
<header>
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
<div class="container">
<a class="navbar-brand" href="index.php"><img src="logo.png" alt="P's Sassy Spot Logo" height="60"></a>
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
<span class="navbar-toggler-icon"></span>
</button>
<div class="collapse navbar-collapse" id="navbarNav">
<ul class="navbar-nav me-auto">
<li class="nav-item"><a class="nav-link text-dark" href="shop.php">Shop</a></li>
<li class="nav-item"><a class="nav-link text-dark" href="#new-arrivals">New Arrivals</a></li>
<li class="nav-item"><a class="nav-link text-dark" href="#sale-items">Sale</a></li>
</ul>
<form class="d-flex">
<div class="input-group">
<input class="form-control rounded-pill" type="search" placeholder="Search Here...." aria-label="Search">
<button class="btn btn-outline-light rounded-pill text-dark" type="submit">
<img src="icons/search.png" alt="Search" width="20" height="20">
</button>
</div>
</form>
<div class="d-flex align-items-center">
<a href="wishlist.php" class="nav-link me-3 position-relative">
<img src="icons/wishlist.png" alt="Wishlist" width="20" height="20">
<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger wishlist-badge" style="display: none;">
 0
</span>
</a>
<a href="cart.php" class="nav-link me-3 position-relative">
<img src="icons/cart.png" alt="Cart" width="20" height="20">
<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-badge" style="display: none;">
0
</span>
</a>
<a href="login.html" class="nav-link me-3"><img src="icons/user.png" alt="User" width="20" height="20"></a>
</div>
</div>
</div>
</nav>
</header>


<main>
<section class="hero">
<div class="container">
<div class="row align-items-center">
<div class="col-md-6 hero-content">
<h1>Elevate Your Style</h1>
<p>Discover our exquisite collection of bags, jewelry, and accessories.</p>
<a href="shop.php" class="btn btn-dark mb-4">Shop Now</a>
<div class="hero-performance">
<div class="performance-item">
<i class="fas fa-users fa-2x text-primary"></i>
<div class="performance-text">
<span class="performance-value">10,000+</span>
<span class="performance-label">Happy Customers</span>
</div>
</div>
<div class="performance-separator"></div>
<div class="performance-item">
<i class="fas fa-star fa-2x text-warning"></i>
<div class="performance-text">
<span class="performance-value">4.8/5</span>
<span class="performance-label">Rating</span>
</div>
</div>
<div class="performance-separator"></div>
<div class="performance-item">
<i class="fas fa-gem fa-2x text-success"></i>
<div class="performance-text">
<span class="performance-value">100%</span>
<span class="performance-label">Authenticity Guaranteed</span>
</div>
</div>
</div>
</div>
<div class="col-md-6">
<img src="images\photo_2024-09-28_17-11-06 (2).png" alt="Hero Image" class="hero-image">
</div>
</div>
</div>
</section>


<section class="brands-section">
<div class="container">
<h2 class="text-center mb-5" data-aos="fade-up">Featured Brands</h2>
<div class="row">
<?php
$brands = [
['name' => 'Gucci', 'logo' => 'icons/gucci.png'],
['name' => 'Versace', 'logo' => 'icons/versace.png'],
['name' => 'YSL', 'logo' => 'icons/ysl.png'],
['name' => 'D&G', 'logo' => 'icons/dolce.png'],
['name' => 'Louis Vuitton', 'logo' => 'icons/louis.png'],
['name' => 'Chanel', 'logo' => 'icons/chanel.png'],
];
foreach ($brands as $index => $brand):
?>
<div class="col-md-4 col-sm-6 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
<div class="brand-card">
<img src="<?php echo $brand['logo']; ?>" alt="<?php echo $brand['name']; ?>" class="brand-logo">
<p class="brand-name"><?php echo $brand['name']; ?></p>
</div>
</div>
<?php endforeach; ?>
</div>
</div>
<ul class="circles">
<li></li>
<li></li>
<li></li>
<li></li>
<li></li>
<li></li>
<li></li>
<li></li>
<li></li>
<li></li>
</ul>
</section>






<section class="categories-section my-5">
<div class="container">
<h2 class="section-title text-center mb-5" data-aos="fade-up"><b>Shop by Category</b></h2>
<div class="row">
<?php
$categories = [
['name' => 'Bags', 'image' => 'images\photo_2024-09-28_16-46-49 (2).png', 'color' => '#f8d7da'],
['name' => 'Jewelry', 'image' => 'images\photo_2024-09-28_16-47-05.png', 'color' => '#e2e3e5'],
['name' => 'Accessories', 'image' => 'images\photo_2024-09-28_16-46-59.png', 'color' => '#d4edda'],
];
foreach ($categories as $index => $category): 
?>
<div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
<div class="category-card" style="background-color: <?php echo $category['color']; ?>">
<div class="category-image">
<img src="<?php echo $category['image']; ?>" alt="<?php echo $category['name']; ?>">
</div>
<div class="category-content">
<h3><?php echo $category['name']; ?></h3>
<a href="shop.php?category=<?php echo strtolower($category['name']); ?>" class="btn btn-outline-dark">Shop Now</a>
</div>
</div>
</div>
<?php endforeach; ?>
</div>
</div>
</section>




<section class="new-arrivals my-5">
<div class="container">
<h2 class="section-title" data-aos="fade-up"><b>New Arrivals</b></h2>
<div class="product-grid">
<?php foreach ($new_arrivals as $product): ?>
<div class="product-item" data-aos="fade-up" data-product-id="<?php echo htmlspecialchars($product['id']); ?>">
<img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
<div class="product-icons">
<i class="<?php echo isInWishlist($product['id']) ? 'fas' : 'far'; ?> fa-heart wishlist-icon" data-active-icon="fas fa-heart wishlist-icon"></i>
<i class="fas fa-cart-plus cart-icon" data-active-icon="fas fa-shopping-cart cart-icon"></i>
</div>
<div class="product-info">
<h4><?php echo htmlspecialchars($product['name']); ?></h4>
<p>Shs <?php echo number_format($product['price']); ?></p>
</div>
</div>
<?php endforeach; ?>
</div>
</div>
</section>




<section class="sale-items my-5">
<div class="container">
<h2 class="section-title" data-aos="fade-up"><b>Sale</b></h2>
<div class="product-grid">
<?php foreach ($sale_items as $product): ?>
<div class="product-item" data-aos="fade-up" data-product-id="<?php echo htmlspecialchars($product['id']); ?>">
<img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
<div class="product-icons">
<i class="<?php echo isInWishlist($product['id']) ? 'fas' : 'far'; ?> fa-heart wishlist-icon" data-active-icon="fas fa-heart wishlist-icon"></i>
<i class="fas fa-cart-plus cart-icon" data-active-icon="fas fa-shopping-cart cart-icon"></i>
</div>
<div class="product-info">
<h4><?php echo htmlspecialchars($product['name']); ?></h4>
<div class="sale-price">
<span class="original-price">Shs <?php echo number_format($product['price']); ?></span>
<span class="discounted-price">Shs <?php echo number_format($product['sale_price']); ?></span>
</div>
</div>
</div>
<?php endforeach; ?>
</div>
</div>
</section>


<section class="reviews-section my-5">
<div class="container">
<h2 class="section-title" data-aos="fade-up"><b>What Our Customers Say</b></h2>
<div class="row">
<div class="col-md-4" data-aos="fade-up">
<div class="review-card">
<div class="stars">
<i class="fas fa-star"></i>
<i class="fas fa-star"></i>
<i class="fas fa-star"></i>
<i class="fas fa-star"></i>
<i class="fas fa-star"></i>
</div>
<p class="review-text">"Absolutely love the quality of their products! Will definitely shop here again."</p>
<p class="reviewer">- Sarah M.</p>
</div>
</div>
<div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
<div class="review-card">
<div class="stars">
<i class="fas fa-star"></i>
<i class="fas fa-star"></i>
<i class="fas fa-star"></i>
<i class="fas fa-star"></i>
<i class="fas fa-star-half-alt"></i>
</div>
<p class="review-text">"Great selection and fast shipping. Highly recommend!"</p>
<p class="reviewer">- Emily R.</p>
</div>
</div>
<div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
<div class="review-card">
<div class="stars">
<i class="fas fa-star"></i>
<i class="fas fa-star"></i>
<i class="fas fa-star"></i>
<i class="fas fa-star"></i>
<i class="fas fa-star"></i>
</div>
<p class="review-text">"The customer service is outstanding. They went above and beyond!"</p>
<p class="reviewer">- Jessica T.</p>
</div>
</div>
</div>
</div>
</section>


<section class="newsletter-section">
<div class="container">
<div class="row align-items-center">
<div class="col-md-6">
<h2 class="newsletter-title">Stay Updated</h2>
<p class="newsletter-text">Subscribe to our newsletter for exclusive offers and the latest fashion trends.</p>
</div>
<div class="col-md-6">
<form class="newsletter-form">
<div class="input-group">
<input type="email" class="form-control" placeholder="Enter your email address" required>
<button type="submit" class="btn btn-dark">Subscribe</button>
</div>
</form>
</div>
</div>
</div>
</section>



</main>


<footer class="mt-5">
<div class="container">
<div class="row">
<div class="col-md-3 footer-section">
<h4>Quick Links</h4>
<ul class="list-unstyled">
<li><a href="about.html">About Us</a></li>
<li><a href="contact.html">Contact</a></li>
<li><a href="shop.html">Shop</a></li>
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
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
AOS.init({
duration: 1000,
once: true,
disable: 'mobile',
});
document.addEventListener('DOMContentLoaded', function() {
const productIcons = document.querySelectorAll('.product-icons i');

productIcons.forEach(icon => {
icon.addEventListener('click', function() {
const activeIcon = this.getAttribute('data-active-icon');
const currentClass = this.className;

this.className = activeIcon;
this.setAttribute('data-active-icon', currentClass);
});
});
});


// Fetch and display trending products
async function fetchTrendingProducts() {
// In a real application, this would be an API call
const products = [
{ name: 'Elegant Tote', price: 79.99, image: 'tote.jpg' },
{ name: 'Pearl Necklace', price: 129.99, image: 'necklace.jpg' },
{ name: 'Silk Scarf', price: 39.99, image: 'scarf.jpg' }
];


const trendingGrid = document.querySelector('.trending-grid');
products.forEach(product => {
const productElement = document.createElement('div');
productElement.classList.add('product-item');
productElement.innerHTML = `
<img src="${product.image}" alt="${product.name}">
<h4>${product.name}</h4>
<p>$${product.price.toFixed(2)}</p>
<button>Add to Cart</button>
`;
trendingGrid.appendChild(productElement);
});
}


fetchTrendingProducts();
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const wishlistIcons = document.querySelectorAll('.wishlist-icon');
    const cartIcons = document.querySelectorAll('.cart-icon');
    const wishlistBadge = document.querySelector('.wishlist-badge');
    const cartBadge = document.querySelector('.cart-badge');

    let wishlistCount = <?php echo isset($_SESSION['wishlist_count']) ? $_SESSION['wishlist_count'] : 0; ?>;
    let cartCount = <?php echo isset($_SESSION['cart_count']) ? $_SESSION['cart_count'] : 0; ?>;

    function updateBadges() {
        if (wishlistCount > 0) {
            wishlistBadge.style.display = 'inline-block';
            wishlistBadge.textContent = wishlistCount;
        } else {
            wishlistBadge.style.display = 'none';
        }

        if (cartCount > 0) {
            cartBadge.style.display = 'inline-block';
            cartBadge.textContent = cartCount;
        } else {
            cartBadge.style.display = 'none';
        }
    }

    function toggleWishlistItem(productId, iconElement) {
        let formData = new FormData();
        formData.append('product_id', productId);

        fetch('toggle_wishlist_item.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                wishlistCount = data.wishlistCount;
                updateBadges();
                if (data.action === 'added') {
                    iconElement.classList.remove('far');
                    iconElement.classList.add('fas');
                } else if (data.action === 'removed') {
                    iconElement.classList.remove('fas');
                    iconElement.classList.add('far');
                }
                console.log(data.message);
            } else {
                console.error('Failed to update wishlist:', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    function toggleCartItem(productId, iconElement) {
        let formData = new FormData();
        formData.append('product_id', productId);

        fetch('toggle_cart_item.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                cartCount = data.cartCount;
                updateBadges();
                if (data.action === 'added') {
                    iconElement.classList.remove('fa-cart-plus');
                    iconElement.classList.add('fa-shopping-cart');
                } else if (data.action === 'removed') {
                    iconElement.classList.remove('fa-shopping-cart');
                    iconElement.classList.add('fa-cart-plus');
                }
                console.log(data.message);
            } else {
                console.error('Failed to update cart');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    wishlistIcons.forEach(icon => {
        icon.addEventListener('click', function(e) {
            e.preventDefault();
            const productItem = this.closest('.product-item');
            const productId = productItem.dataset.productId;
            toggleWishlistItem(productId, this);
        });
    });

    cartIcons.forEach(icon => {
        icon.addEventListener('click', function(e) {
            e.preventDefault();
            const productItem = this.closest('.product-item');
            const productId = productItem.dataset.productId;
            toggleCartItem(productId, this);
        });
    });

    // Set initial states
    <?php foreach ($wishlistItems as $productId): ?>
    document.querySelector(`.product-item[data-product-id="${<?php echo $productId; ?>}"] .wishlist-icon`)?.classList.add('fas');
    <?php endforeach; ?>

    <?php foreach ($cartItems as $productId): ?>
    document.querySelector(`.product-item[data-product-id="${<?php echo $productId; ?>}"] .cart-icon`)?.classList.add('fa-shopping-cart');
    <?php endforeach; ?>

    updateBadges();
});

</script>
</body>
</html>