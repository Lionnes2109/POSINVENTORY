<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    // Get the productID, quantity, and cash from the form
    $productIDs = $_POST['productID'];
    $quantities = $_POST['quantity'];
    $cash = $_POST['cash'];

    // Fetch additional details about the products (you might need to adjust this query based on your database schema)
    $productDetailsQuery = "SELECT * FROM products WHERE productID IN (" . implode(',', $productIDs) . ")";
    $productDetailsResult = $conn->query($productDetailsQuery);

    if ($productDetailsResult->num_rows > 0) {
        $productDetails = $productDetailsResult->fetch_all(MYSQLI_ASSOC);
    } else {
        $productDetails = [];
    }

    // Calculate total and subtotal
    $subtotal = 0;
    foreach ($productDetails as $index => $product) {
        $subtotal += $product['price'] * $quantities[$index];
    }
    $total = $subtotal;

    // Display the details in a receipt-like format
    // You can customize this part based on your preferences
    echo "<h1>Order Confirmation</h1>";
    echo "<h2>Product Details:</h2>";
    echo "<ul>";
    foreach ($productDetails as $index => $product) {
        echo "<li>{$product['productName']} - Quantity: {$quantities[$index]} - Price: {$product['price']}</li>";
    }
    echo "</ul>";
    echo "<p>Subtotal: $subtotal</p>";
    echo "<p>Total: $total</p>";
    echo "<p>Cash: $cash</p>";
    echo "<p>Change: " . ($cash - $total) . "</p>";

    // Provide buttons for confirmation, cancellation, and printing receipt
    echo '<button type="submit" name="confirm">Confirm</button>';
    echo '<button type="submit" name="cancel">Cancel</button>';
    echo '<button type="submit" name="printReceipt">Print Receipt</button>';
}
?>
