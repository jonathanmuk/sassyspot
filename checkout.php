<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - P's Sassy Spot</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <!-- Same as index.html -->
    </header>

    <main class="checkout-page">
        <h1>Checkout</h1>
        <form id="checkout-form">
            <section class="billing-info">
                <h2>Billing Information</h2>
                <input type="text" name="name" placeholder="Full Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="text" name="address" placeholder="Address" required>
                <input type="text" name="city" placeholder="City" required>
                <input type="text" name="zip" placeholder="ZIP Code" required>
            </section>
            <section class="payment-info">
                <h2>Payment Information</h2>
                <input type="text" name="card-number" placeholder="Card Number" required>
                <input type="text" name="card-name" placeholder="Name on Card" required>
                <input type="text" name="expiry" placeholder="MM/YY" required>
                <input type="text" name="cvv" placeholder="CVV" required>
            </section>
            <section class="order-summary">
                <h2>Order Summary</h2>
                <!-- Order details will be dynamically added here -->
            </section>
            <button type="submit">Place Order</button>
        </form>
    </main>

    <footer>
        <!-- Same as index.html -->
    </footer>

    <script src="checkout.js"></script>
</body>
</html>
