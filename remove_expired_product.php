<?php
// Include your database connection
include "./php/connection.php";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the product ID from the form
    $productID = $_POST['productID'];

    // Fetch product details for confirmation message
    $query = "SELECT * FROM expiredproducts WHERE productID = '$productID'";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $productDetails = $result->fetch_assoc();
    } else {
        // Redirect to the original page if the product is not found
        header('Location: notif.php');
        exit();
    }
}

// Check if the confirmation form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm'])) {
    // Remove the product from the expiredproducts table
    $deleteQuery = "DELETE FROM expiredproducts WHERE productID = '$productID'";
    $deleteResult = $conn->query($deleteQuery);

    // Check if the deletion was successful
    if ($deleteResult) {
        // Redirect to the original page after successful deletion
        header('Location: notif.php');
        exit();
    } else {
        // Handle the case where deletion fails
        echo "Error deleting product: " . $conn->error;
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation</title>
    <link rel="stylesheet" href="./css/expired.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body>
    <div class="confirmation-container">
        <h2>Confirm Deletion</h2>
        <p>Are you sure you want to remove the following product?</p>
        <ul>
            <li><strong>Product ID:</strong> <?php echo $productDetails['productID']; ?></li>
            <li><strong>Brand Name:</strong> <?php echo $productDetails['brandName']; ?></li>
            <li><strong>Generic Name:</strong> <?php echo $productDetails['genericName']; ?></li>
            <li><strong>Expiry Date:</strong> <?php echo $productDetails['expiryDate']; ?></li>
        </ul>
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <input type="hidden" name="productID" value="<?php echo $productID; ?>">
            <button type="submit" name="confirm">Yes, Remove</button>
            <a href="notif.php">Cancel</a>
        </form>
    </div>
</body>
</html>
