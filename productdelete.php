<?php
// productdelete.php

// Include your database connection
include "./php/connection.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmDelete'])) {
    $productID = $_POST['productID'];

    // Prepare and execute the SQL statement to remove the product
    $removeProductQuery = "DELETE FROM products WHERE productID = ?";
    $stmt = $conn->prepare($removeProductQuery);
    $stmt->bind_param('i', $productID);

    if ($stmt->execute()) {
        // Product removed successfully, you may want to add a success message or redirect
        // For example: header('Location: inventory.php');
        header('Location: inventory.php');
        exit();
    } else {
        // Handle error, you may want to add an error message
        // For example: echo "Error: " . $conn->error;
        header('Location: inventory.php');
        exit();
    }

    // Close the statement
    $stmt->close();

    // Close the database connection
    $conn->close();

    // You can add additional content or redirection here
    exit();
}
?>