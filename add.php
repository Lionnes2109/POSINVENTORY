<?php
// Start the session
session_start();

// Check if the user is logged in, otherwise redirect to index.html
if (!isset($_SESSION['username'])) {
    header('Location: index.html');
    exit();
}

// Logout logic
if (isset($_GET['logout'])) {
    // Unset all of the session variables
    $_SESSION = array();

    // Destroy the session
    session_destroy();

    // Redirect to login page
    header('Location: index.html');
    exit();
}

// Retrieve the username from the session
$username = $_SESSION['username'];
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add/Edit/Delete Product</title>
    <link rel="stylesheet" href="./css//edit.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body>
<header>
        <nav>
        <div class="logo">Botika</div>
        <div class="burger-menu" onclick="toggleMenu()">☰</div>
        <div class="navigation">
            <ul>
                <li><a href="inventory.php"><i class="fa-solid fa-box"></i>Inventory</a></li>
                <li>
                    <a href="add.php" class="dropbtn"><i class="fa-solid fa-pen-to-square"></i>Add Product</a>
                   
                </li>

                <li><a href="sales.php"><i class="fa-solid fa-dollar-sign"></i>Sales</a></li>
                <li><a href="notif.php"> <i class="fa-solid fa-envelope"></i>Notification</a></li>
                <li><a href="?logout=1"><i class="fa-solid fa-right-from-bracket" style="color: #FF0000"></i>Logout <?php echo $username; ?></a></li>
            </ul>
        </div>
    </nav>
</header>

<section class="product-form-section">
    <h1>Add Product</h1>
    <?php
include "./php/connection.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $brandName = $_POST['brandName'];
    $productcode= $_POST['productcode'];
    $genericName = $_POST['genericName'];
    $stocks = $_POST['stocks'];
    $price = $_POST['price'];
    $manufactureDate = $_POST['manufactureDate'];
    $expiryDate = $_POST['expiryDate'];
    $prescription = $_POST['prescription'];

    // Upload image
    $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/posinventory/productuploads/";
    $imageFileType = strtolower(pathinfo($_FILES["productImg"]["name"], PATHINFO_EXTENSION));
    $newFileName = uniqid() . "." . $imageFileType;
    $target_file = $target_dir . $newFileName;
    $extensions_arr = array("jpg", "jpeg", "png", "gif");

    if (in_array($imageFileType, $extensions_arr)) {
        if (move_uploaded_file($_FILES["productImg"]["tmp_name"], $target_file)) {
            // Image uploaded successfully
            $query = "CALL addproduct('$productcode','$newFileName', '$brandName', '$genericName', $stocks, $price, '$manufactureDate', '$expiryDate', '$prescription')";
            if ($conn->query($query) == true) {
                $feedback = "Product added successfully.";
                $alertType = "success";
            } else {
                $feedback = "Error adding product: " . $conn->error;
                $alertType = "error";
            }
        } else {
            $feedback = "Error uploading image.";
            $alertType = "error";
        }
    } else {
        $feedback = "Invalid image type.";
        $alertType = "error";
    }
}

// JavaScript to display alert
if (isset($feedback)) {
    echo "<script>alert('$feedback');</script>";
}
?>
     <form action="add.php" method="post" enctype="multipart/form-data" onsubmit="return validateDates();">
        <div class="form-group">
            <label for="productImg">Product Image:</label>
            <input type="file" id="productImg" name="productImg" accept="image/*" required>
        </div>

        <div class="form-group">
            <label for="productcode">Product Code:</label>
            <input type="number" id="productcode" name="productcode" required>
        </div>

        <div class="form-group">
            <label for="brandName">Brand Name:</label>
            <input type="text" id="brandName" name="brandName" required>
        </div>

        <div class="form-group">
            <label for="genericName">Generic Name:</label>
            <input type="text" id="genericName" name="genericName" required>
        </div>

        <div class="form-group">
            <label for="prescription">Prescription:</label>
            <select id="prescription" name="prescription" required>
                <option value="Required">Required</option>
                <option value="Optional">Optional</option>
            </select>
        </div>

        <div class="form-group">
            <label for="stocks">Stocks:</label>
            <input type="number" id="stocks" name="stocks" required>
        </div>

        <div class="form-group">
            <label for="price">Price:</label>
            <input type="number" id="price" name="price" step="0.01" required>
        </div>

        <div class="form-group">
            <label for="manufactureDate">Manufacture Date:</label>
            <input type="date" id="manufactureDate" name="manufactureDate" required>
        </div>

        <div class="form-group">
            <label for="expiryDate">Expiry Date:</label>
            <input type="date" id="expiryDate" name="expiryDate" required>
        </div>

        <button type="submit">Add Product</button>
    </form>
</section>




<script>
    function toggleMenu() {
        const navigation = document.querySelector('.navigation');
        navigation.classList.toggle('show');
    }

    function validateDates() {
        var manufactureDate = new Date(document.getElementById('manufactureDate').value);
        var expiryDate = new Date(document.getElementById('expiryDate').value);
        var currentDate = new Date();

        if (manufactureDate > currentDate) {
            alert('Manufacture date cannot be in the future.');
            return false;
        }

        if (expiryDate < currentDate) {
            alert('Expiry date cannot be in the past.');
            return false;
        }

        return true;
    }
</script>
</body>
</html>