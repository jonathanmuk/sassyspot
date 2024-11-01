<?php
require_once 'db_connect.php';


// Set the number of items per page
$items_per_page = 24;

// Get the current page number
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Calculate the offset for the SQL query
$offset = ($current_page - 1) * $items_per_page;

// Fetch total number of products
$total_products_sql = "SELECT COUNT(*) as total FROM products";
$total_products_stmt = $pdo->query($total_products_sql);
$total_products = $total_products_stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Calculate total number of pages
$total_pages = ceil($total_products / $items_per_page);

// Fetch products for the current page
$products_sql = "SELECT * FROM products ORDER BY RAND() LIMIT :limit OFFSET :offset";
$products_stmt = $pdo->prepare($products_sql);
$products_stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$products_stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$products_stmt->execute();
$products = $products_stmt->fetchAll(PDO::FETCH_ASSOC);


// Fetch categories
$categories_sql = "SELECT * FROM categories";
$categories_stmt = $pdo->query($categories_sql);
$categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);


// Fetch subcategories
$subcategories_sql = "SELECT * FROM subcategories";
$subcategories_stmt = $pdo->query($subcategories_sql);
$subcategories = $subcategories_stmt->fetchAll(PDO::FETCH_ASSOC);


// Get min and max prices
$price_sql = "SELECT MIN(price) as min_price, MAX(price) as max_price FROM products";
$price_stmt = $pdo->query($price_sql);
$price_range = $price_stmt->fetch(PDO::FETCH_ASSOC);
$min_price = floor($price_range['min_price']);
$max_price = ceil($price_range['max_price']);


?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Shop - P's Sassy Spot</title>
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
.input-group {
position: relative;
}


.input-group .form-control {
padding-right: 40px;
}


.input-group .btn {
position: absolute;
right: 0;
z-index: 10;
}
.shop-page {
padding: 2rem 0;
}

.shop-container {
display: flex;
gap: 2rem;
}

.product-grid {
flex: 3;
display: grid;
grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
gap: 1rem;
}
.product-item {
border: 1px solid #ddd;
text-align: center;
border: 1px solid #ddd;
border-radius: 8px;
overflow: hidden;
display: flex;
flex-direction: column;
height: 100%;
box-shadow: 0 2px 4px rgba(0,0,0,0.1);
transition: transform 0.3s ease;
}
.product-image-container {
position: relative;
width: 100%;
padding-top: 100%; /* This creates a 1:1 aspect ratio */
overflow: hidden;
}
.product-item img {
position: absolute;
top: 0;
left: 0;
width: 100%;
height: 100%;
object-fit: cover;
transition: transform 0.3s ease;
}
.product-item:hover {
transform: translateY(-5px);
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
z-index: 10;
}
.product-item:hover .product-icons {
opacity: 1;
}
.nav-link{
font-weight: bold;
}


 

.wishlist-icon,.cart-icon{
background-color: #fff;
border: none;
padding: 0.5rem;
border-radius: 50%;
}
.product-info {
padding: 5px;
background-color: #fff;
flex-grow: 1;
display: flex;
flex-direction: column;
justify-content: space-between;
}


.product-info h4 {
margin: 0 0 10px;
font-size: 1rem;
font-weight: 500;
}


.product-info p {
margin: 0;
font-size: 1rem;
color: #914c4c;
font-weight: bold;
}


.pagination {
margin-top: 2rem;
display: flex;
justify-content: center;
width: 100%;
}


.page-item:not(:last-child) {
margin-right: 5px;
}


.page-link {
color: #914c4c;
border: 1px solid #914c4c;
transition: all 0.3s ease;
}


.page-link:hover {
background-color: #914c4c;
color: #fff;
}


.page-item.active .page-link {
background-color: #914c4c;
border-color: #914c4c;
}


.page-item.disabled .page-link {
color: #6c757d;
border-color: #dee2e6;
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
.filter-bar {
background: linear-gradient(45deg, #f6f9fc, #e9f2f9, #d8e8f3);
padding: 10px 0;
margin-bottom: 20px;
}


.filter-icon {
cursor: pointer;
font-size: 1rem;
}


.price-filter {
display: none;
}
.filter-options {
display: flex;
align-items: center;
gap: 20px;
}


.filter-item {
display: flex;
align-items: center;
gap: 10px;
padding: 0 10px;
}


.filter-label {
font-weight: bold;
}


#priceRange {
width: 200px;
}


#applyFilters {
margin-left: auto;
} 
.price-slider {
position: relative;
width: 200px;
height: 20px;
margin: 10px 0;
}


.price-slider input[type="range"] {
position: absolute;
width: calc(100% - 20px);
-webkit-appearance: none;
pointer-events: none;
background: none;
height: 2px;
left: 10px;
top: 50%;
border: none;
outline: none;
opacity: 0.7;
transition: opacity 0.2s;
transform: translateY(-50%);
}


.price-slider input[type="range"]::-webkit-slider-thumb {
-webkit-appearance: none;
appearance: none;
pointer-events: auto;
width: 20px;
height: 20px;
border-radius: 50%;
background-color: #000000;
cursor: pointer;
margin-top: -6px;
}


.price-slider input[type="range"]::-moz-range-thumb {
width: 20px;
height: 20px;
border-radius: 50%;
background-color: #000000;
cursor: pointer;
border: none;
}
.price-slider input[type="range"]:hover {
opacity: 1;
}
.price-slider input[type="range"]::-webkit-slider-runnable-track {
height: 7px;
background: #ddd;
border: none;
border-radius: 3px;
width: 100%;
}
.price-slider input[type="range"]::-moz-range-track {
width: 100%;
height: 2px;
background: #ddd;
border: none;
border-radius: 3px;
}
#minPriceRange {
z-index: 1;
}


#maxPriceRange {
z-index: 2;
}
.sidebar {
background-color: #f8f9fa;
padding: 20px;
border-right: 1px solid #e9ecef;
height: 900px;
overflow-y: auto;
position: sticky;
border:black;
top: 0;
box-shadow: 0 0 10px rgba(0,0,0,0.1);

}


.sidebar h3 {
font-size: 1.5rem;
margin-bottom: 1rem;
color: #333;
text-transform: uppercase;
letter-spacing: 1px;
border-bottom: 2px solid #914c4c;
padding-bottom: 10px;
}


.category-list {
list-style-type: none;
padding: 0;
}


.category-list > li {
margin-bottom: 10px;
}


.category-list > li > a {
display: block;
padding: 8px 15px;
color: #333;
text-decoration: none;
transition: all 0.3s ease;
font-weight: 500;
}


.category-list > li > a:hover,
.category-list > li > a.active {
background-color: #914c4c;
color: #fff;
border-radius: 5px;
}


.subcategory-list {
list-style-type: none;
padding-left: 20px;
display: none;
}


.subcategory-list li a {
display: block;
padding: 5px 10px;
color: #666;
text-decoration: none;
transition: all 0.3s ease;
font-size: 0.9rem;
}


.subcategory-list li a:hover,
.subcategory-list li a.active {
color: #914c4c;
background-color: #f1f3f5;
border-radius: 3px;
}


.product-grid {
display: grid;
grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
gap: 1rem;

}


@media (max-width: 768px) {
.sidebar {
height: auto;
position: static;
}
}
.fashion-tips-container {
background: linear-gradient(135deg, #f8f9fa, #e9ecef);
border-radius: 10px;
padding: 20px;
box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}
.fashion-tips-title {
font-size: 1.5rem;
color: #914c4c;
text-align: center;
margin-bottom: 20px;
text-transform: uppercase;
letter-spacing: 2px;
}
.fashion-tips {
display: flex;
flex-direction: column;
gap: 20px;
}
.fashion-tip {
background-color: #ffffff;
border-radius: 8px;
padding: 15px;
transition: transform 0.3s ease, box-shadow 0.3s ease;
box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}
.fashion-tip:hover {
transform: translateY(-5px);
box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
}
.tip-icon {
font-size: 2rem;
color: #d4af37;
text-align: center;
margin-bottom: 10px;
}


.fashion-tip h5 {
font-size: 1.1rem;
color: #333;
margin-bottom: 10px;
text-align: center;
}


.fashion-tip p {
font-size: 0.9rem;
color: #666;
line-height: 1.4;
text-align: center;
}


@media (max-width: 768px) {
.fashion-tips-container {
margin-bottom: 20px;
}
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
<li class="nav-item"><a class="nav-link text-dark" href="#new-arrivals">New Arrivals</a></li>
<li class="nav-item"><a class="nav-link text-dark" href="#sale-items">Sale</a></li>
</ul>
<div class="d-flex align-items-center">
<a href="wishlist.php" class="nav-link me-3"><img src="icons/wishlist.png" alt="Wishlist" width="20" height="20"></a>
<a href="cart.php" class="nav-link me-3"><img src="icons/cart.png" alt="Cart" width="20" height="20"></a>
<a href="login.html" class="nav-link me-3"><img src="icons/user.png" alt="User" width="20" height="20"></a>
<form class="d-flex" id="searchForm">
<div class="input-group">
<input class="form-control rounded-pill" type="search" placeholder="Search Here...." aria-label="Search" id="searchInput" autocomplete="off">
<button class="btn btn-outline-light rounded-pill text-dark" type="submit">
<img src="icons/search.png" alt="Search" width="20" height="20">
</button>
</div>
</form>
<div id="searchResults" class="position-absolute bg-white border rounded shadow-sm" style="display: none; z-index: 1000;"></div>
</div>
</div>
</div>
</nav>
</header>
<div class="filter-bar">
<div class="container">
<div class="d-flex justify-content-between align-items-center">
<div class="filter-icon" id="filterIcon">
<i class="fas fa-filter"></i> Filter
</div>
<div class="filter-options" id="filterOptions" style="display: none;">
<div class="filter-item">
<span class="filter-label">Categories</span>
<div class="dropdown">
<button class="btn btn-outline-dark dropdown-toggle" type="button" id="categoryDropdown" data-bs-toggle="dropdown" aria-expanded="false">
Select Category
</button>
<ul class="dropdown-menu" aria-labelledby="categoryDropdown">
<?php foreach ($categories as $category): ?>
<li><a class="dropdown-item" href="#" data-category-id="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></a></li>
<?php endforeach; ?>
</ul>
</div>
</div>
<div class="filter-item">
<span class="filter-label">Price Range</span>
<div class="price-slider">
<input type="range" id="minPriceRange" min="<?php echo $min_price; ?>" max="<?php echo $max_price; ?>" step="1" value="<?php echo $min_price; ?>">
<input type="range" id="maxPriceRange" min="<?php echo $min_price; ?>" max="<?php echo $max_price; ?>" step="1" value="<?php echo $max_price; ?>">
</div>
<span id="priceValue">Shs<?php echo $min_price; ?> - Shs<?php echo $max_price; ?></span>
</div>
<button id="applyFilters" class="btn btn-dark">Apply Filters</button>
</div>
</div>
</div>
</div>



<main class="container-fluid shop-page">
<div class="row">
<div class="col-md-3">
<div class="sidebar" id="sidebar">
<h3 class="text-center">Categories</h3>
<ul class="category-list" id="categoryList">
<?php foreach ($categories as $category): ?>
<li>
<a href="#" data-category-id="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></a>
<ul class="subcategory-list" id="subcategoryList-<?php echo $category['id']; ?>" style="display: none;">
<?php 
$categorySubcategories = array_filter($subcategories, function($subcat) use ($category) {
return $subcat['category_id'] == $category['id'];
});
foreach ($categorySubcategories as $subcategory): 
?>
<li>
<a href="#" data-subcategory-id="<?php echo $subcategory['id']; ?>"><?php echo htmlspecialchars($subcategory['name']); ?></a>
</li>
<?php endforeach; ?>
</ul>
</li>
<?php endforeach; ?>
</ul>
<div class="fashion-tips-container mt-4">
<h4 class="fashion-tips-title">Fashion Tips</h4>
<div class="fashion-tips">
<div class="fashion-tip">
<div class="tip-icon"><i class="fas fa-tshirt"></i></div>
<h5>Mix and Match</h5>
<p>Don't be afraid to combine unexpected colors and patterns for a unique look.</p>
</div>
<div class="fashion-tip">
<div class="tip-icon"><i class="fas fa-shoe-prints"></i></div>
<h5>Accessorize Wisely</h5>
<p>Choose one statement piece to elevate your entire outfit.</p>
</div>
<div class="fashion-tip">
<div class="tip-icon"><i class="fas fa-palette"></i></div>
<h5>Know Your Colors</h5>
<p>Wear colors that complement your skin tone to enhance your natural glow.</p>
</div>
</div>
</div>
</div>

</div>


<div class="col-md-9">
<div class="product-grid" id="productGrid">
<?php foreach ($products as $product): ?>
<div class="product-item" data-category-id="<?php echo $product['category_id']; ?>" data-subcategory-id="<?php echo $product['subcategory_id']; ?>" data-price="<?php echo $product['price']; ?>">
<div class="product-image-container">
<img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
<div class="product-icons">
<i class="far fa-heart wishlist-icon" data-product-id="<?php echo $product['id']; ?>"></i>
<i class="fas fa-cart-plus cart-icon" data-product-id="<?php echo $product['id']; ?>"></i>
</div>
</div>
<div class="product-info">
<h4><?php echo htmlspecialchars($product['name']); ?></h4>
<p>Shs <?php echo number_format($product['price']); ?></p>
</div>
</div>
<?php endforeach; ?>
</div>
</div>
</div>


<div class="container">
    <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination justify-content-center">
            <?php if ($current_page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $current_page - 1; ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php
            $start_page = max(1, $current_page - 2);
            $end_page = min($total_pages, $current_page + 2);

            for ($i = $start_page; $i <= $end_page; $i++):
            ?>
                <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($current_page < $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $current_page + 1; ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
const filterIcon = document.getElementById('filterIcon');
const filterOptions = document.getElementById('filterOptions');
const minPriceRange = document.getElementById('minPriceRange');
const maxPriceRange = document.getElementById('maxPriceRange');
const priceValue = document.getElementById('priceValue');
const applyFilters = document.getElementById('applyFilters');
const productGrid = document.getElementById('productGrid');
const categoryDropdown = document.getElementById('categoryDropdown');


filterIcon.addEventListener('click', function() {
filterOptions.style.display = filterOptions.style.display === 'none' ? 'flex' : 'none';
});


function updatePriceRange() {
let minVal = parseInt(minPriceRange.value);
let maxVal = parseInt(maxPriceRange.value);


if (minVal > maxVal) {
[minVal, maxVal] = [maxVal, minVal];
minPriceRange.value = minVal;
maxPriceRange.value = maxVal;
}


priceValue.textContent = `Shs${minVal.toLocaleString()} - Shs${maxVal.toLocaleString()}`;
}


minPriceRange.addEventListener('input', updatePriceRange);
maxPriceRange.addEventListener('input', updatePriceRange);


applyFilters.addEventListener('click', function() {
const minPrice = parseInt(minPriceRange.value);
const maxPrice = parseInt(maxPriceRange.value);
const selectedCategory = document.querySelector('.dropdown-item.active');


const products = productGrid.querySelectorAll('.product-item');
products.forEach(product => {
const price = parseFloat(product.dataset.price);
const categoryMatch = !selectedCategory || product.dataset.categoryId === selectedCategory.dataset.categoryId;


if (price >= minPrice && price <= maxPrice && categoryMatch) {
product.style.display = '';
} else {
product.style.display = 'none';
}
});


// We don't hide the filter options anymore
// filterOptions.style.display = 'none';
});


document.querySelectorAll('.dropdown-item').forEach(item => {
item.addEventListener('click', function(e) {
e.preventDefault();
document.querySelectorAll('.dropdown-item').forEach(i => i.classList.remove('active'));
this.classList.add('active');
categoryDropdown.textContent = this.textContent;
});
});
});
</script>



<script>
document.addEventListener('DOMContentLoaded', function() {
const categoryList = document.getElementById('categoryList');
const productGrid = document.getElementById('productGrid');


categoryList.addEventListener('click', function(e) {
e.preventDefault();

if (e.target.tagName === 'A') {
const clickedElement = e.target;
const isCategory = clickedElement.parentElement.parentElement === categoryList;

if (isCategory) {
// Clicked on a category
const categoryId = clickedElement.dataset.categoryId;

// Toggle active class for categories
categoryList.querySelectorAll('li > a').forEach(a => a.classList.remove('active'));
clickedElement.classList.add('active');


// Toggle subcategory visibility
const subcategoryList = clickedElement.nextElementSibling;
categoryList.querySelectorAll('.subcategory-list').forEach(ul => {
if (ul !== subcategoryList) {
ul.style.display = 'none';
}
});
if (subcategoryList) {
subcategoryList.style.display = subcategoryList.style.display === 'none' ? 'block' : 'none';
}


showProductsByCategory(categoryId);
} else {
// Clicked on a subcategory
const subcategoryId = clickedElement.dataset.subcategoryId;


// Toggle active class for subcategories
clickedElement.closest('.subcategory-list').querySelectorAll('a').forEach(a => a.classList.remove('active'));
clickedElement.classList.add('active');


showProductsBySubcategory(subcategoryId);
}
}
});


function showProductsByCategory(categoryId) {
const products = productGrid.querySelectorAll('.product-item');
let foundProducts = false;


products.forEach(product => {
if (product.dataset.categoryId === categoryId) {
product.style.display = '';
foundProducts = true;
} else {
product.style.display = 'none';
}
});


if (!foundProducts) {
productGrid.innerHTML = '<p class="text-center">No products found in this category.</p>';
}
}


function showProductsBySubcategory(subcategoryId) {
const products = productGrid.querySelectorAll('.product-item');
let foundProducts = false;


products.forEach(product => {
if (product.dataset.subcategoryId === subcategoryId) {
product.style.display = '';
foundProducts = true;
} else {
product.style.display = 'none';
}
});


if (!foundProducts) {
productGrid.innerHTML = '<p class="text-center">No products found in this subcategory.</p>';
}
}


// Add this function to show all products
function showAllProducts() {
const products = productGrid.querySelectorAll('.product-item');
products.forEach(product => {
product.style.display = '';
});
}


// Call this function when the page loads to show all products initially
showAllProducts();
});



</script>


<script>
$(document).ready(function() {
const searchInput = $('#searchInput');
const searchResults = $('#searchResults');
const productGrid = $('#productGrid');


searchInput.autocomplete({
source: function(request, response) {
$.ajax({
url: 'search.php',
dataType: 'json',
data: {
query: request.term
},
success: function(data) {
response(data.map(function(item) {
return {
label: item.name,
value: item.id
};
}));
}
});
},
minLength: 2,
select: function(event, ui) {
searchInput.val(ui.item.label);
searchProducts(ui.item.label);
return false;
}
});


$('#searchForm').on('submit', function(e) {
e.preventDefault();
searchProducts(searchInput.val());
});


function searchProducts(query) {
$.ajax({
url: 'search_products.php',
method: 'GET',
data: { query: query },
dataType: 'json',
success: function(data) {
if (data.length > 0) {
let html = '';
data.forEach(function(product) {
html += `
<div class="product-item" data-category-id="${product.category_id}" data-subcategory-id="${product.subcategory_id}" data-price="${product.price}">
<img src="${product.image_path}" alt="${product.name}">
<div class="product-icons">
<img src="icons/addwishlist.png" alt="Add to Wishlist" class="wishlist-icon">
<img src="icons/addcart.png" alt="Add to Cart" class="cart-icon">
</div>
<div class="product-info">
<h4>${product.name}</h4>
<p>Shs ${Number(product.price).toLocaleString()}</p>
</div>
</div>
`;
});
productGrid.html(html);
} else {
productGrid.html('<p class="text-center">Sorry, item not found.</p>');
}
},
error: function() {
productGrid.html('<p class="text-center">An error occurred while searching. Please try again.</p>');
}
});
}
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
const productGrid = document.getElementById('productGrid');


productGrid.addEventListener('click', function(e) {
if (e.target.classList.contains('wishlist-icon') || e.target.classList.contains('cart-icon')) {
const icon = e.target;
const productId = icon.dataset.productId;


if (icon.classList.contains('wishlist-icon')) {
toggleWishlist(icon, productId);
} else if (icon.classList.contains('cart-icon')) {
toggleCart(icon, productId);
}
}
});


function toggleWishlist(icon, productId) {
icon.classList.toggle('fas');
icon.classList.toggle('far');
// Add logic to add/remove the product to/from the wishlist
console.log(`Toggled wishlist for product ${productId}`);
}


function toggleCart(icon, productId) {
icon.classList.toggle('fa-cart-plus');
icon.classList.toggle('fa-shopping-cart');
// Add logic to add/remove the product to/from the cart
console.log(`Toggled cart for product ${productId}`);
}
});


</script>
</body>
</html>