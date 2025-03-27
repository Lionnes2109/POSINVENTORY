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

    // Retrieve the username from the session
    $username = $_SESSION['username'];

    // Include your database connection
    include "./php/connection.php";

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['removeProductID'])) {
        $productID = $_POST['removeProductID']; // Fix: use the correct variable name
        $removeQuery = "DELETE FROM checkout WHERE username = '$username' AND productID = '$productID'";
        
        if ($conn->query($removeQuery)) {
            // Removal successful
            $removeResponse = ['success' => true];
        } else {
            // Removal failed
            $removeResponse = ['success' => false];
        }
    }
    // Fetch products from the checkoutpage view for the logged-in user
    $checkoutQuery = "SELECT * FROM checkoutpage WHERE username = '$username'";
    $checkoutResult = $conn->query($checkoutQuery);

    // Check if there are rows in the result
    if ($checkoutResult->num_rows > 0) {
        $checkoutRows = $checkoutResult->fetch_all(MYSQLI_ASSOC);
    } else {
        $checkoutRows = [];
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
        // Get the productID and quantity from the form
        $productIDs = $_POST['productID'];
        $quantities = $_POST['quantity'];
        $paymentMethod = $_POST['paymentMethod']; // Get the payment method
        $referenceNo = isset($_POST['referenceNo']) ? $_POST['referenceNo'] : ''; // Get the reference number if provided

        // Loop through each product and perform the checkout
        foreach (array_combine($productIDs, $quantities) as $productID => $quantity) {
            // Call the stored procedure with the three parameters
            $procedureCall = "CALL checkout('$username', '$productID', '$quantity')";

            if ($conn->query($procedureCall)) {
                // Checkout successful
                $checkoutResponse = ['success' => true];
            } else {
                // Checkout failed
                $checkoutResponse = ['success' => false];
            }
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
    <title>Checkout</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link rel="stylesheet" href="./css/checkout.css">
    <style>
        /* Add some styling for payment method section */
        .payment-section {
            margin-bottom: 15px;
        }
        .payment-option {
            margin-top: 10px;
        }
        /* Hide the reference number input by default */
        #gcashSection {
            display: none;
        }
        /* Input and Dropdown Styling */
input[type="text"], 
select {
    width: 100%;
    padding: 10px 12px;
    margin: 8px 0;
    display: inline-block;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
    font-size: 16px;
    transition: border-color 0.3s, box-shadow 0.3s;
}

input[type="text"]:focus,
select:focus {
    border-color: #0be407;
    outline: none;
    box-shadow: 0 0 5px rgba(11, 228, 7, 0.3);
}

/* Payment Method Section Styling */
.payment-section {
    margin: 20px 0;
}

.payment-section label,
.payment-option label {
    font-weight: bold;
    margin-right: 10px;
    display: block;
    margin-bottom: 5px;
}

/* Payment Options Styling */
.payment-option {
    background-color: #f9f9f9;
    padding: 15px;
    border-radius: 5px;
    margin-top: 10px;
    border-left: 3px solid #0be407;
}

/* Select Dropdown Custom Styling */
select {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url('data:image/svg+xml;utf8,<svg fill="black" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>');
    background-repeat: no-repeat;
    background-position: right 10px center;
    background-size: 20px;
    padding-right: 30px;
    cursor: pointer;
}

select:hover {
    background-color: #f5f5f5;
}

/* Responsive adjustments */
@media (min-width: 768px) {
    .payment-section {
        display: flex;
        align-items: center;
    }
    
    .payment-section label {
        margin-bottom: 0;
        margin-right: 15px;
        width: 150px;
    }
    
    select, 
    input[type="text"] {
        width: 250px;
    }
    
    .payment-option {
        display: flex;
        align-items: center;
    }
    
    .payment-option label {
        margin-bottom: 0;
        width: 150px;
    }
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
                    <li><a href="#"><i class="fa-solid fa-basket-shopping" style="color: #0be407;"></i>Basket</a></li>
                    <li><a href="?logout=1"><i class="fa-solid fa-right-from-bracket" style="color: #FF0000"></i>Logout <?php echo $username; ?></a></li>
                </ul>
            </div>
        </nav>
    </header>

<!-- Display checkout details -->
<section class="checkout-section">
    <div class="checkout-header">
        <h1>Your Checkout</h1>
        <form id="checkoutForm" method="post">
            <?php foreach ($checkoutRows as $checkoutRow): ?>
                <input type="hidden" name="productID[]" value="<?php echo $checkoutRow['productID']; ?>" />
                <input type="hidden" name="quantity[]" value="<?php echo $checkoutRow['quantity']; ?>" />
            <?php endforeach; ?>
            
            <div class="payment-section">
                <label for="paymentMethod">Payment Method: </label>
                <select name="paymentMethod" id="paymentMethod" onchange="togglePaymentFields()">
                    <option value="cash">Cash</option>
                    <option value="gcash">GCash</option>
                </select>
            </div>
            
            <div id="cashSection" class="payment-option">
                <label for="cash">Enter Cash: </label>
                <input type="text" name="cash" id="cashInput" oninput="calculateChange()" required />
            </div>
            
            <div id="gcashSection" class="payment-option">
                <label for="referenceNo">Reference No: </label>
                <input type="text" name="referenceNo" id="referenceNoInput" required />
            </div>
            
            <br>
            <br>
            <button type="submit" class="checkout-button" name="checkout">Checkout</button>
            <button type="button" onclick="printReceipt()" class="print-receipt-button">Print Receipt</button>
        </form>
    </div>
    <table>
        <thead>
            <tr>
                <th>Product Image</th>
                <th>Brand Name</th>
                <th>Generic Name</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                $totalSum = 0; // Initialize total sum variable
                foreach ($checkoutRows as $checkoutRow): 
                    $total = $checkoutRow['price'] * $checkoutRow['quantity'];
                    $totalSum += $total; // Add to total sum
            ?>
                <tr>
                    <td><img src="./productuploads/<?php echo $checkoutRow['productImg']; ?>" alt="Product Image"></td>
                    <td><?php echo $checkoutRow['brandName']; ?></td>
                    <td><?php echo $checkoutRow['genericName']; ?></td>
                    <td><?php echo $checkoutRow['price']; ?></td>
                    <td><?php echo $checkoutRow['quantity']; ?></td>
                    <td><?php echo $total; ?></td>
                    <td>
                        <form method="post" onsubmit="return confirm('Are you sure you want to remove this item?');">
                            <input type="hidden" name="removeProductID" value="<?php echo $checkoutRow['productID']; ?>">
                            <button type="submit">Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5"><strong>Total Sum</strong></td>
                <td><strong><?php echo $totalSum; ?></strong></td>
            </tr>
            <tr id="cashDisplayRow">
                <td colspan="5"><strong>Cash</strong></td>
                <td><strong id="cashDisplay">0</strong></td>
            </tr>
            <tr id="referenceDisplayRow" style="display: none;">
                <td colspan="5"><strong>Reference No.</strong></td>
                <td><strong id="referenceDisplay">-</strong></td>
            </tr>
            <tr id="changeDisplayRow">
                <td colspan="5"><strong>Change</strong></td>
                <td><strong id="changeDisplay">0</strong></td>
            </tr>
        </tfoot>
    </table>
</section>

<script>
    function toggleMenu() {
        const navigation = document.querySelector('.navigation');
        navigation.classList.toggle('show');
    }

    // Add this script block for successful checkout
    <?php if (isset($checkoutResponse) && $checkoutResponse['success']): ?>
        alert('Transaction Successful');
        window.location.href = 'pharmacist.php';
    <?php endif; ?>

    function togglePaymentFields() {
        const paymentMethod = document.getElementById('paymentMethod').value;
        const cashSection = document.getElementById('cashSection');
        const gcashSection = document.getElementById('gcashSection');
        const cashDisplayRow = document.getElementById('cashDisplayRow');
        const referenceDisplayRow = document.getElementById('referenceDisplayRow');
        const changeDisplayRow = document.getElementById('changeDisplayRow');
        const cashInput = document.getElementById('cashInput');
        const referenceNoInput = document.getElementById('referenceNoInput');
        
        if (paymentMethod === 'cash') {
            cashSection.style.display = 'block';
            gcashSection.style.display = 'none';
            cashDisplayRow.style.display = '';
            referenceDisplayRow.style.display = 'none';
            changeDisplayRow.style.display = '';
            cashInput.required = true;
            referenceNoInput.required = false;
        } else if (paymentMethod === 'gcash') {
            cashSection.style.display = 'none';
            gcashSection.style.display = 'block';
            cashDisplayRow.style.display = 'none';
            referenceDisplayRow.style.display = '';
            changeDisplayRow.style.display = 'none';
            cashInput.required = false;
            referenceNoInput.required = true;
        }
        
        // Update the validation and display
        if (paymentMethod === 'cash') {
            calculateChange();
        } else {
            updateReferenceDisplay();
        }
    }

    function calculateChange() {
        const cashInput = document.getElementById('cashInput');
        const cashDisplay = document.getElementById('cashDisplay');
        const changeDisplay = document.getElementById('changeDisplay');
        const checkoutButton = document.querySelector('.checkout-button');
        const printReceiptButton = document.querySelector('.print-receipt-button');

        // Parse input values as floats
        const cash = parseFloat(cashInput.value) || 0;
        const totalSum = <?php echo $totalSum; ?>; // Retrieve totalSum from PHP

        // Display the cash
        cashDisplay.textContent = cash.toFixed(2);

        // Calculate and display the change
        const change = cash - totalSum;
        changeDisplay.textContent = change.toFixed(2);

        // Disable buttons if conditions are met
        checkoutButton.disabled = cash === 0 || change < 0 || cash === "";
        printReceiptButton.disabled = cash === 0 || change < 0 || cash === "";
    }
    
    function updateReferenceDisplay() {
        const referenceNoInput = document.getElementById('referenceNoInput');
        const referenceDisplay = document.getElementById('referenceDisplay');
        const checkoutButton = document.querySelector('.checkout-button');
        const printReceiptButton = document.querySelector('.print-receipt-button');
        
        // Display the reference number
        referenceDisplay.textContent = referenceNoInput.value || '-';
        
        // Disable buttons if reference number is empty
        const isEmpty = referenceNoInput.value.trim() === '';
        checkoutButton.disabled = isEmpty;
        printReceiptButton.disabled = isEmpty;
    }

    function printReceipt() {
        const paymentMethod = document.getElementById('paymentMethod').value;
        const totalSum = <?php echo $totalSum; ?>;
        let receiptDetails = {
            products: <?php echo json_encode($checkoutRows); ?>,
            totalSum: totalSum.toFixed(2),
            paymentMethod: paymentMethod
        };
        
        if (paymentMethod === 'cash') {
            const cashInput = document.getElementById('cashInput');
            const cash = parseFloat(cashInput.value) || 0;
            const change = cash - totalSum;
            receiptDetails.cash = cash.toFixed(2);
            receiptDetails.change = change.toFixed(2);
        } else {
            const referenceNoInput = document.getElementById('referenceNoInput');
            receiptDetails.referenceNo = referenceNoInput.value;
        }

        // Build the receipt HTML
        let receiptHTML = '<div style="max-width: 300px; margin: auto; font-family: Arial, sans-serif;">';
        receiptHTML += '<h1 style="text-align: center; font-size: 18px;">Your Receipt</h1>';
        receiptHTML += '<hr>';

        // Display product details in the receipt
        receiptDetails.products.forEach(product => {
            const total = product.price * product.quantity;
            receiptHTML += `<p style="font-size: 12px; margin: 5px 0;">${product.brandName} - ${product.genericName} - PHP${product.price} x ${product.quantity} = PHP${total.toFixed(2)}</p>`;
        });

        receiptHTML += '<hr>';
        receiptHTML += `<p style="font-size: 14px; margin: 5px 0;">Total Sum: PHP${receiptDetails.totalSum}</p>`;
        receiptHTML += `<p style="font-size: 14px; margin: 5px 0;">Payment Method: ${receiptDetails.paymentMethod === 'cash' ? 'Cash' : 'GCash'}</p>`;
        
        if (receiptDetails.paymentMethod === 'cash') {
            receiptHTML += `<p style="font-size: 14px; margin: 5px 0;">Cash: PHP${receiptDetails.cash}</p>`;
            receiptHTML += `<p style="font-size: 14px; margin: 5px 0;">Change: PHP${receiptDetails.change}</p>`;
        } else {
            receiptHTML += `<p style="font-size: 14px; margin: 5px 0;">Reference No: ${receiptDetails.referenceNo}</p>`;
        }
        
        receiptHTML += '</div>';

        // Open a new window with the receipt details and print it
        const receiptWindow = window.open('', '_blank');
        receiptWindow.document.write('<html><head><title>Receipt</title></head><body>');
        receiptWindow.document.write(receiptHTML);
        receiptWindow.document.write('</body></html>');

        // Close the document for printing
        receiptWindow.document.close();

        // Print the receipt
        receiptWindow.print();
    }

    // Initialize the payment fields on page load
    document.addEventListener('DOMContentLoaded', function() {
        togglePaymentFields();
        
        // Add event listener for GCash reference number input
        const referenceNoInput = document.getElementById('referenceNoInput');
        referenceNoInput.addEventListener('input', updateReferenceDisplay);
    });
</script>
</body>
</html>