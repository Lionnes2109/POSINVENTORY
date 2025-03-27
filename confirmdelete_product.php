<?php
// confirmdelete_product.php

// Include your database connection
include "./php/connection.php";

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['productID'])) {
    $productID = $_GET['productID'];

    // Fetch product details for confirmation message
    $productQuery = "SELECT * FROM products WHERE productID = ?";
    $stmt = $conn->prepare($productQuery);
    $stmt->bind_param('i', $productID);
    $stmt->execute();
    $productResult = $stmt->get_result();

    // Check if there are rows in the result
    if ($productResult->num_rows > 0) {
        $productRow = $productResult->fetch_assoc();
    } else {
        // Redirect or handle error if product not found
        header('Location: inventory.php');
        exit();
    }

    // Close the statement
    $stmt->close();
} else {
    // Redirect if productID is not set
    header('Location: inventory.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Delete</title>
    <link rel="stylesheet" href="./css/confirmdelete.css">
    <!-- Add your stylesheets or additional styling here -->
</head>
<body>

    <h1>Confirm Delete</h1>

    <p>Are you sure you want to delete the following product?</p>

    <ul>
        <li>Product ID: <?php echo $productRow['productID']; ?></li>
        <!-- Add other details as needed -->
    </ul>

    <form method="post" action="productdelete.php">
        <input type="hidden" name="productID" value="<?php echo $productRow['productID']; ?>">
        <button type="submit" name="confirmDelete">Yes, Delete Product</button>
        <a href="inventory.php">No, Cancel</a>
    </form>

</body>
</html>
