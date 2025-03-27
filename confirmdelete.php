<?php
// Start the session
session_start();

// Check if the user is logged in, otherwise redirect to index.html
if (!isset($_SESSION['username'])) {
    header('Location: index.html');
    exit();
}

// Include your database connection
include "./php/connection.php";

// Check if employeeID is set in the query parameters
if (isset($_GET['employeeID'])) {
    $employeeID = $_GET['employeeID'];

    // Fetch employee details for confirmation message
    $employeeQuery = "SELECT * FROM employees WHERE employeeID = ?";
    $stmt = $conn->prepare($employeeQuery);
    $stmt->bind_param('i', $employeeID);
    $stmt->execute();
    $employeeResult = $stmt->get_result();

    // Check if there are rows in the result
    if ($employeeResult->num_rows > 0) {
        $employeeRow = $employeeResult->fetch_assoc();
    } else {
        // Redirect or handle error if employee not found
        header('Location: employees.php');
        exit();
    }

    // Close the statement
    $stmt->close();
} else {
    // Redirect if employeeID is not set
    header('Location: employees.php');
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

    <p>Are you sure you want to delete the following employee?</p>

    <ul>
        <li>Employee ID: <?php echo $employeeRow['employeeID']; ?></li>
        <li>First Name: <?php echo $employeeRow['firstName']; ?></li>
        <li>Last Name: <?php echo $employeeRow['lastName']; ?></li>
        <!-- Add other details as needed -->
    </ul>

    <form method="post" action="employees.php">
        <input type="hidden" name="employeeID" value="<?php echo $employeeRow['employeeID']; ?>">
        <button type="submit" name="removeEmployee">Yes, Delete Employee</button>
        <a href="employees.php">No, Cancel</a>
    </form>

</body>
</html>

<?php
// Close the database connection
$conn->close();
?>