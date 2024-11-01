<?php
session_start();
require_once 'db_connect.php';


// Fetch wishlist and cart counts
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$wishlistCount = 0;
$cartCount = 0;


if ($userId) {
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlists WHERE user_id = ?");
  $stmt->execute([$userId]);
  $wishlistCount = $stmt->fetchColumn();


  $stmt = $pdo->prepare("SELECT COUNT(*) FROM carts WHERE user_id = ?");
  $stmt->execute([$userId]);
  $cartCount = $stmt->fetchColumn();
} else {
  $wishlistCount = isset($_SESSION['guest_wishlist']) ? count($_SESSION['guest_wishlist']) : 0;
  $cartCount = isset($_SESSION['guest_cart']) ? count($_SESSION['guest_cart']) : 0;
}

// Fetch sale items
$saleItemsSql = "SELECT * FROM products WHERE is_sale = TRUE LIMIT 10";
$saleItemsStmt = $pdo->query($saleItemsSql);
$saleItems = $saleItemsStmt->fetchAll(PDO::FETCH_ASSOC);


// Fetch wishlist items
$wishlistItems = [];
if ($userId) {
  $stmt = $pdo->prepare("SELECT p.* FROM products p JOIN wishlists w ON p.id = w.product_id WHERE w.user_id = ?");
  $stmt->execute([$userId]);
  $wishlistItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif (isset($_SESSION['guest_wishlist']) && !empty($_SESSION['guest_wishlist'])) {
  $placeholders = implode(',', array_fill(0, count($_SESSION['guest_wishlist']), '?'));
  $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
  $stmt->execute(array_values($_SESSION['guest_wishlist']));
  $wishlistItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to check if a product is in the wishlist
function isInWishlist($product_id) {
    global $userId, $pdo;
    if ($userId) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $product_id]);
        return $stmt->fetchColumn() > 0;
    }
    return false;
}

// Function to check if a product is in the cart
function isInCart($product_id) {
    global $userId, $pdo;
    if ($userId) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $product_id]);
        return $stmt->fetchColumn() > 0;
    }
    return false;
}


// Fetch related products only if wishlist is not empty
$relatedProducts = [];
if (!empty($wishlistItems)) {
  $categoryIds = array_unique(array_column($wishlistItems, 'category_id'));
  $wishlistProductIds = array_column($wishlistItems, 'id');


  if (!empty($categoryIds)) {
 $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
 
 if (!empty($wishlistProductIds)) {
$productPlaceholders = implode(',', array_fill(0, count($wishlistProductIds), '?'));
$query = "SELECT * FROM products WHERE category_id IN ($placeholders) AND id NOT IN ($productPlaceholders) LIMIT 4";
$params = array_merge($categoryIds, $wishlistProductIds);
 } else {
$query = "SELECT * FROM products WHERE category_id IN ($placeholders) LIMIT 4";
$params = $categoryIds;
 }


 $stmt = $pdo->prepare($query);
 $stmt->execute($params);
 $relatedProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}


?>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Wishlist - P's Sassy Spot</title>
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
 .wishlist-container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
 .wishlist-item {
        display: flex;
        align-items: center;
        padding: 15px 0;
        border-bottom: 1px solid #e0e0e0;
 }
 .wishlist-item:last-child {
            border-bottom: none;
        }
 .wishlist-item:hover {
    transform: translateY(-5px);
}
 .wishlist-item img {
width: 100px;
height: 100px;
object-fit: cover;
margin-right: 20px;
margin-left:20px;
}
    .wishlist-item-details {
        flex-grow: 1;
        display: flex;
        justify-content: space-between;
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr;
        align-items: center;
        gap: 10px;
        }

        .wishlist-item-name {
            font-weight: bold;
            margin-bottom: 5px;
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .wishlist-item-quantity {
            color: #666;
        }
        .wishlist-item-quantity,
        .wishlist-item-price,
        .wishlist-item-actions {
        width: 150px;
        text-align: center;
    }
    

        .wishlist-item-price {
            font-weight: bold;
            color: #333;
            text-align: right;
        }

        .wishlist-item-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        .btn-add-to-cart, .btn-remove-wishlist {
            padding: 5px 10px;
            font-size: 0.9rem;
        }
        .quantity-control {
            display: flex;
            align-items: center;
            margin-right: 10px;
            justify-content: center;
        }
        .quantity-control button {
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
        }
        .quantity-control input {
            width: 40px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .select-all-container {
            margin-bottom: 15px;
        }
        .action-buttons {
            margin-top: 15px;
        }
        .form-check-input {
        cursor: pointer;
    }

    .form-check-input:checked {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
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
 .empty-wishlist {
            text-align: center;
            padding: 50px 0;
        }
        .empty-wishlist i {
            font-size: 48px;
            color: #dc3545;
            margin-bottom: 20px;
        }
        .sale-items {
            margin-top: 50px;
        }
        #related-products .card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        #related-products .card:hover {
            transform: translateY(-5px);
        }

        #related-products .card-img-top {
            height: 200px;
            object-fit: cover;
        }

        #related-products .card-body {
            padding: 1.25rem;
        }

        #related-products .card-title {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        #related-products .btn {
            width: 100%;
            padding: 0.5rem;
        }
        .product-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            padding:0;
            height:290px;
            width:225px;
            margin-bottom: 10px;
            position: relative;
            transition: transform 0.3s ease;
        }
        .product-card:hover {
            transform: translateY(-5px);
        }
        .product-image-container {
            position: relative;
            overflow: hidden;
            padding-top: 100%; /* 1:1 Aspect Ratio */
        }
        .product-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .product-card:hover .product-image {
            transform: scale(1.1);
        }

        .product-details {
        padding: 10px;
        }
        .product-name {
            font-size: 1.1rem;
            margin-bottom: 5px;
        }
        .original-price {
            text-decoration: line-through;
            color: #dc3545;
            font-size: 0.8rem;
        }
        .sale-price {
            font-weight: bold;
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
        .product-card:hover .product-icons {
            opacity: 1;
        }
        .product-icons i {
        background-color: rgba(255, 255, 255, 0.8);
        padding: 8px;
        border-radius: 50%;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    .product-icons i:hover {
        background-color: #fff;
    }
    .wishlist-summary {
            background-color: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .wishlist-summary h2 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #333;
        }

        .wishlist-summary p {
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
        }

        .wishlist-summary h3 {
            font-size: 1.3rem;
            margin-bottom: 1rem;
            color: #333;
        }

        .share-buttons .btn {
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
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


  <main class="container mt-4">
 
 <?php if (empty($wishlistItems)): ?>
            <div class="empty-wishlist">
                <i class="fas fa-heart"></i>
                <h2>Your wishlist is empty</h2>
                <p>Add items to your wishlist to keep track of what you love.</p>
                <a href="shop.php" class="btn btn-dark mt-3">Shop Now</a>
            </div>

            <div class="sale-items">
    <h2 class="mb-4 text-center">On Sale Now</h2>
    <div class="row">
        <?php foreach ($saleItems as $product): ?>
            <div class="col-6 col-md-3 mb-4">
                <div class="product-card">
                    <div class="product-image-container">
                        <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                    </div>
                    <div class="product-details">
                        <h5 class="product-name text-center"><?php echo htmlspecialchars($product['name']); ?></h5>
                        <p>
                            <span class="original-price">Shs <?php echo number_format($product['price']); ?></span>
                            <span class="sale-price">Shs <?php echo number_format($product['sale_price']); ?></span>
                        </p>
                    </div>
                    <div class="product-icons">
                        <i class="<?php echo isInWishlist($product['id']) ? 'fas' : 'far'; ?> fa-heart wishlist-icon" data-product-id="<?php echo $product['id']; ?>"></i>
                        <i class="<?php echo isInCart($product['id']) ? 'fas fa-shopping-cart' : 'fas fa-cart-plus'; ?> cart-icon" data-product-id="<?php echo $product['id']; ?>"></i>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php else: ?>
            <div class="select-all-container">
                <input type="checkbox" id="select-all" class="form-check-input">
                <label for="select-all" class="form-check-label">Select All</label>
            </div>
            <div class="wishlist-container">
                <?php foreach ($wishlistItems as $item): ?>
                    <div class="wishlist-item" data-product-id="<?php echo $item['id']; ?>">
                        <input type="checkbox" class="form-check-input item-checkbox" data-product-id="<?php echo $item['id']; ?>">
                        <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="wishlist-item-image">
                        <div class="wishlist-item-details">
                            <div class="wishlist-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div class="wishlist-item-quantity">
                                <div class="quantity-control">
                                    <button class="decrease-quantity">-</button>
                                    <input type="number" min="1" value="1" class="quantity-input">
                                    <button class="increase-quantity">+</button>
                                </div>
                            </div>
                            <div class="wishlist-item-price">Shs.<?php echo number_format($item['price']); ?></div>
                            <div class="wishlist-item-actions">
                                <button class="btn btn-dark btn-sm btn-add-to-cart" data-product-id="<?php echo $item['id']; ?>">
                                    Add to Cart
                                </button>
                                <button class="btn btn-outline-danger btn-sm btn-remove-wishlist" data-product-id="<?php echo $item['id']; ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="action-buttons d-flex justify-content-between align-items-center">
                <div>
                <button id="add-selected-to-cart" class="btn btn-primary me-2">Add Selected to Cart</button>
                <button id="remove-selected-from-wishlist" class="btn btn-danger">Remove Selected from Wishlist</button>
                </div>
                <button id="clear-wishlist" class="btn btn-outline-danger">Clear Wishlist</button>
            </div>
        <?php endif; ?>

        <?php if (!empty($relatedProducts)): ?>
            <h2 class="mt-5 mb-4 text-center">You May Also Like</h2>
            <div class="row" id="related-products">
                <?php foreach ($relatedProducts as $product): ?>
                    <div class="col-md-3 mb-4">
                        <div class="card" data-product-id="<?php echo $product['id']; ?>">
                            <img src="<?php echo htmlspecialchars($product['image_path']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                <p class="card-text text-muted">Shs.<?php echo number_format($product['price']); ?></p>
                                <button class="btn btn-outline-primary add-to-wishlist" data-product-id="<?php echo $product['id']; ?>">Add to Wishlist</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
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
    // Add to Cart
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            addToCart(productId,this);
        });
    });

    // Remove from Wishlist
    document.querySelectorAll('.remove-from-wishlist').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            removeFromWishlist(productId,this);
        });
    });

    // Clear Wishlist
    document.getElementById('clear-wishlist').addEventListener('click', function() {
        if (confirm('Are you sure you want to clear your wishlist?')) {
            clearWishlist();
        }
    });

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
});

    // Add to Wishlist (for related products)
    document.querySelectorAll('.add-to-wishlist').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            addToWishlist(productId);
        });
    });

     // Cart icon functionality
     document.querySelectorAll('.cart-icon').forEach(icon => {
        icon.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            addToCart(productId, this);
        });
    });

    // Wishlist icon functionality
    document.querySelectorAll('.wishlist-icon').forEach(icon => {
        icon.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            const isInWishlist = this.classList.contains('fas');

            if (isInWishlist) {
                removeFromWishlist(productId, this);
            } else {
                addToWishlist(productId, this);
            }
        });
    });

    function addToCart(productId, button) {
    fetch('add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}`
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            updateCartCount(data.cartCount);
            if (data.inCart) {
                showToast('Item already in cart', 'warning');
            } else {
                showToast('Item added to cart successfully', 'success');
                if (button) {
                    updateButtonState(button, true);
                }
            }
        } else {
            showToast(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while adding to cart', 'danger');
    });
}



function removeFromWishlist(productId, button) {
    fetch('remove_from_wishlist.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateWishlistCount(data.wishlistCount);
            button.closest('.wishlist-item').remove();
            showToast('Item removed from wishlist', 'info');
            
            if (data.wishlistCount === 0) {
                document.querySelector('.col-md-8').innerHTML = '<p>Your wishlist is empty. Add items to your wishlist!</p>';
            }
        } else {
            showToast('Failed to remove item from wishlist', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while removing from wishlist', 'danger');
    });
}


    function clearWishlist() {
        fetch('clear_wishlist.php', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.querySelector('.col-md-8').innerHTML = '<p>Your wishlist is empty. Add items to your wishlist!</p>';
                updateWishlistCount(0);
                updateRelatedProducts(data.newRelatedProducts);
            } else {
                alert('Failed to clear wishlist');
            }
        });
    }

    function addToWishlist(productId, icon) {
        fetch('add_to_wishlist.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=${productId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateWishlistCount(data.wishlistCount);
                icon.classList.remove('far');
                icon.classList.add('fas');
                alert('Item added to wishlist!');
            } else {
                alert('Failed to add item to wishlist');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while adding to wishlist');
        });
    }

    function updateCartCount(count) {
    const cartBadge = document.querySelector('a[href="cart.php"] .badge');
    if (cartBadge) {
        cartBadge.textContent = count;
        cartBadge.style.display = count > 0 ? 'inline' : 'none';
    }
} 

function showToast(message, type = 'info') {
    const toast = document.getElementById('liveToast');
    const toastBody = toast.querySelector('.toast-body');
    
    toast.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'bg-info');
    toast.classList.add(`bg-${type}`);
    
    toastBody.textContent = message;
    
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
}

function updateButtonState(button, inCart) {
    if (inCart) {
        button.textContent = 'In Cart';
        button.classList.remove('btn-primary');
        button.classList.add('btn-success');
        button.disabled = true;
    } else {
        button.textContent = 'Add to Cart';
        button.classList.remove('btn-success');
        button.classList.add('btn-primary');
        button.disabled = false;
    }
}

    function updateWishlistCount(count) {
        const wishlistBadge = document.querySelector('a[href="wishlist.php"] .badge');
        if (wishlistBadge) {
            wishlistBadge.textContent = count;
            wishlistBadge.style.display = count > 0 ? 'inline' : 'none';
        }
    }

    function addWishlistItem(item) {
        const wishlistContainer = document.querySelector('.col-md-8');
        const newItem = document.createElement('div');
        newItem.className = 'wishlist-item';
        newItem.dataset.productId = item.id;
        newItem.innerHTML = `
            <img src="${item.image_path}" alt="${item.name}">
            <div class="wishlist-item-details">
                <h3>${item.name}</h3>
                <p>Shs.${item.price}</p>
            </div>
            <div class="wishlist-item-actions">
                <button class="btn btn-sm btn-primary me-2 add-to-cart" data-product-id="${item.id}">
                    <i class="fas fa-cart-plus"></i> Add to Cart
                </button>
                <button class="btn btn-sm btn-danger remove-from-wishlist" data-product-id="${item.id}">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        wishlistContainer.appendChild(newItem);
    }

    function addRelatedProduct(product) {
        const relatedProductsContainer = document.getElementById('related-products');
        const newProduct = document.createElement('div');
        newProduct.className = 'col-md-3 mb-4';
        newProduct.innerHTML = `
            <div class="card" data-product-id="${product.id}">
                <img src="${product.image_path}" class="card-img-top" alt="${product.name}">
                <div class="card-body">
                    <h5 class="card-title">${product.name}</h5>
                    <p class="card-text">Shs.${product.price}</p>
                    <button class="btn btn-sm btn-outline-primary add-to-wishlist" data-product-id="${product.id}">Add to Wishlist</button>
                </div>
            </div>
        `;
        relatedProductsContainer.appendChild(newProduct);
    }

    function updateRelatedProducts(newProducts) {
        const relatedProductsContainer = document.getElementById('related-products');
        relatedProductsContainer.innerHTML = '';
        newProducts.forEach(product => addRelatedProduct(product));
    }

    function updateWishlistIcon(productId, isInWishlist) {
        const icon = document.querySelector(`.wishlist-icon[data-product-id="${productId}"]`);
        if (icon) {
            icon.classList.toggle('far', !isInWishlist);
            icon.classList.toggle('fas', isInWishlist);
        }
    }

    function updateCartIcon(productId, isInCart) {
        const icon = document.querySelector(`.cart-icon[data-product-id="${productId}"]`);
        if (icon) {
            icon.classList.toggle('fas fa-cart-plus', !isInCart);
            icon.classList.toggle('fas fa-shopping-cart', isInCart);
        }
    }
});

// Update all "Add to Cart" buttons on page load
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.add-to-cart').forEach(button => {
        const productId = button.dataset.productId;
        fetch(`check_cart.php?product_id=${productId}`)
            .then(response => response.json())
            .then(data => {
                updateButtonState(button, data.inCart);
            });
    });
});

  </script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {

            // Select All functionality
            const selectAllCheckbox = document.getElementById('select-all');
            const itemCheckboxes = document.querySelectorAll('.item-checkbox');

            selectAllCheckbox.addEventListener('change', function() {
                itemCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });

            // Quantity control
            document.querySelectorAll('.decrease-quantity').forEach(button => {
                button.addEventListener('click', function() {
                    const input = this.nextElementSibling;
                    if (input.value > 1) {
                        input.value = parseInt(input.value) - 1;
                        updatePrice(this);
                    }
                });
            });

            document.querySelectorAll('.increase-quantity').forEach(button => {
                button.addEventListener('click', function() {
                    const input = this.previousElementSibling;
                    input.value = parseInt(input.value) + 1;
                    updatePrice(this);
                });
            });

            document.querySelectorAll('.quantity-input').forEach(input => {
                input.addEventListener('change', function() {
                    if (this.value < 1) this.value = 1;
                    updatePrice(this);
                });
            });

            function updatePrice(element) {
                const wishlistItem = element.closest('.wishlist-item');
                const quantityInput = wishlistItem.querySelector('.quantity-input');
                const priceElement = wishlistItem.querySelector('.wishlist-item-price');
                const basePrice = parseFloat(priceElement.dataset.basePrice);
                const newPrice = basePrice * parseInt(quantityInput.value);
                priceElement.textContent = `Shs.${newPrice.toFixed(2)}`;
            }

            // Add selected to cart
            document.getElementById('add-selected-to-cart').addEventListener('click', function() {
                const selectedItems = document.querySelectorAll('.item-checkbox:checked');
                selectedItems.forEach(item => {
                    const productId = item.dataset.productId;
                    const quantity = item.closest('.wishlist-item').querySelector('.quantity-input').value;
                    addToCart(productId, quantity);
                });
            });

            // Remove selected from wishlist
            document.getElementById('remove-selected-from-wishlist').addEventListener('click', function() {
                const selectedItems = document.querySelectorAll('.item-checkbox:checked');
                selectedItems.forEach(item => {
                    const productId = item.dataset.productId;
                    removeFromWishlist(productId, item);
                });
            });

        });
  </script>

<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <strong class="me-auto">Cart Notification</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body"></div>
    </div>
</div>
</body>
</html>