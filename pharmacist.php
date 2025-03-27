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

        // Redirect to the login page
        header('Location: index.html');
        exit();
    }

    // Include your database connection
    include "./php/connection.php";

    // Retrieve the username from the session
    $username = $_SESSION['username'];

    // Handle the prescription submission and add to basket
if (isset($_POST['submit_prescription'])) {
    $productID = $_POST['productID'];
    $medicalCenter = mysqli_real_escape_string($conn, $_POST['medical_center']);
    $prescriptor = mysqli_real_escape_string($conn, $_POST['prescriptor']);
    $prescriptionDate = $_POST['prescription_date'];
    
    // First, add the product to the basket and get the basket ID
    $insertBasketQuery = "CALL addbasket('$productID', '$username')";
    $conn->query($insertBasketQuery);
    
    // Get the latest basket ID for this user
    $basketIdQuery = "SELECT id FROM basket WHERE username = '$username' AND productID = '$productID' ORDER BY id DESC LIMIT 1";
    $basketResult = $conn->query($basketIdQuery);
    
    if ($basketResult && $basketResult->num_rows > 0) {
        $basketRow = $basketResult->fetch_assoc();
        $basketId = $basketRow['id'];
        
        // Now store the prescription details with the basket ID
        $insertPrescriptionQuery = "INSERT INTO prescription_details 
                                  (basket_id, medical_center, prescriptor, prescription_date) 
                                  VALUES ('$basketId', '$medicalCenter', '$prescriptor', '$prescriptionDate')";
        $conn->query($insertPrescriptionQuery);
        
        // Check if there was an error
        if ($conn->error) {
            echo "<script>alert('Error storing prescription: " . $conn->error . "');</script>";
        }
    } else {
        echo "<script>alert('Error retrieving basket ID');</script>";
    }
    
    // Redirect to prevent duplicate submissions
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

    // Check if the addToBasket parameter is set
    if (isset($_GET['addToBasket'])) {
        $productID = $_GET['addToBasket'];
        
        // Check if prescription is required for this product
        $prescriptionQuery = "SELECT prescription FROM products WHERE productID = '$productID'";
        $prescriptionResult = $conn->query($prescriptionQuery);
        $product = $prescriptionResult->fetch_assoc();
        
        if ($product['prescription'] == 'Optional') {
            // If prescription is optional, directly add to basket
            $insertQuery = "CALL addbasket('$productID', '$username')";
            $conn->query($insertQuery);
            
            // Redirect to prevent duplicate submissions
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        }
        // If prescription is required, the modal will be shown via JavaScript
    }

    // Check if a search query is submitted
    if (isset($_POST['search'])) {
        $searchKeyword = $_POST['searchKeyword'];

        // Fetch data from the products table based on the search keyword
        $query = "SELECT * FROM products WHERE genericName LIKE '%$searchKeyword%' OR brandName LIKE '%$searchKeyword%'";
        $result = $conn->query($query);

        // Check if there are rows in the result
        if ($result->num_rows > 0) {
            $rows = $result->fetch_all(MYSQLI_ASSOC);
        } else {
            $rows = [];
        }
    } else {
        // Fetch all data from the products table
        $query = "SELECT * FROM products";
        $result = $conn->query($query);

        // Check if there are rows in the result
        if ($result->num_rows > 0) {
            $rows = $result->fetch_all(MYSQLI_ASSOC);
        } else {
            $rows = [];
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
    <title>Pharmacist</title>
    <link rel="stylesheet" href="./css/pharmacy.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <style>
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 20px;
            border-radius: 5px;
            width: 50%;
            max-width: 500px;
        }
        
        .modal-content h2 {
            margin-top: 0;
            color: #333;
        }
        
        .modal-content form {
            display: flex;
            flex-direction: column;
        }
        
        .modal-content label {
            margin-top: 10px;
            font-weight: bold;
        }
        
        .modal-content input {
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .modal-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        
        .modal-buttons button {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .modal-buttons button[type="submit"] {
            background-color: #4CAF50;
            color: white;
        }
        
        .modal-buttons button.cancel {
            background-color: #f44336;
            color: white;
        }

        /* Added styles for success message */
        .success-message {
            background-color: #dff0d8;
            color: #3c763d;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            display: none;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">Botika</div>
            <div class="burger-menu" onclick="toggleMenu()">☰</div>
            <div class="navigation">
                <ul>
                    <li><a href="pharmacist.php"><i class="fa-solid fa-shop"></i>Products</a></li>
                    <li><a href="basket.php"><i class="fa-solid fa-basket-shopping" style="color: #0be407;"></i>Basket</a></li>
                    <li><a href="?logout=1"><i class="fa-solid fa-right-from-bracket" style="color: #FF0000"></i>Logout <?php echo $username; ?></a></li>
                </ul>
            </div>
        </nav>
    </header>
    
    <section class="product-section">
        <h1>Products</h1>

        <!-- Success Message (initially hidden) -->
        <div id="successMessage" class="success-message">
            Product added to basket successfully!
        </div>

        <!-- Search Form -->
        <form method="post">
            <label for="searchKeyword">Search:</label>
            <input type="text" id="searchKeyword" name="searchKeyword">
            <button type="submit" name="search">Search</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Product ID</th>
                    <th>Product Image</th>
                    <th>Brand Name</th>
                    <th>Generic Name</th>
                    <th>Prescription</th>
                    <th>Stocks</th>
                    <th>Price</th>
                    <th>Manufacture Date</th>
                    <th>Expiry Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?php echo $row['productID']; ?></td>
                        <td><img src="productuploads/<?php echo $row['productImg']; ?>" alt="Product Image" style="width: 50px; height: 50px;"></td>
                        <td><?php echo $row['brandName']; ?></td>
                        <td><?php echo $row['genericName']; ?></td>
                        <td><?php echo $row['prescription']; ?></td>
                        <td><?php echo $row['stocks']; ?></td>
                        <td><?php echo $row['price']; ?></td>
                        <td><?php echo $row['manufactureDate']; ?></td>
                        <td><?php echo $row['expiryDate']; ?></td>
                        <td><button onclick="addToBasket('<?php echo $row['productID']; ?>', '<?php echo $row['prescription']; ?>', '<?php echo $row['brandName']; ?>')">Add to Basket</button></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <!-- Prescription Modal -->
    <div id="prescriptionModal" class="modal">
        <div class="modal-content">
            <h2>Prescription Required</h2>
            <p>This product requires a prescription. Please enter the prescription details below:</p>
            
            <form method="post">
                <input type="hidden" id="modal_productID" name="productID">
                
                <label for="medical_center">Medical Center Name:</label>
                <input type="text" id="medical_center" name="medical_center" required>
                
                <label for="prescriptor">Prescriptor:</label>
                <input type="text" id="prescriptor" name="prescriptor" required>
                
                <label for="prescription_date">Date:</label>
                <input type="date" id="prescription_date" name="prescription_date" required>
                
                <div class="modal-buttons">
                    <button type="button" class="cancel" onclick="closeModal()">Cancel</button>
                    <button type="submit" name="submit_prescription">Submit</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleMenu() {
            const navigation = document.querySelector('.navigation');
            navigation.classList.toggle('show');
        }

        function addToBasket(productID, prescription, productName) {
            if (prescription === 'Required') {
                // Show the prescription modal
                const modal = document.getElementById('prescriptionModal');
                document.getElementById('modal_productID').value = productID;
                modal.style.display = 'block';
                
                // Set current date as default for the date field
                const today = new Date().toISOString().split('T')[0];
                document.getElementById('prescription_date').value = today;
            } else {
                // For "Optional" prescription, directly add to basket
                window.location.href = '?addToBasket=' + productID;
            }
        }

        function closeModal() {
            const modal = document.getElementById('prescriptionModal');
            modal.style.display = 'none';
        }

        // Show success message after redirect if there was a successful action
        if (window.location.search.includes('addToBasket') || 
            document.referrer.includes('submit_prescription')) {
            const successMessage = document.getElementById('successMessage');
            successMessage.style.display = 'block';
            setTimeout(() => {
                successMessage.style.display = 'none';
            }, 3000);
        }

        // Close the modal if the user clicks outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('prescriptionModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>