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

// Initialize a variable to check if the form is submitted
$registrationSuccess = false;

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Include the database connection
    include './php/connection.php';

    // Retrieve form data
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $position = $_POST['position'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Call the stored procedure
    $procedureCall = "CALL addemployee('$firstname', '$lastname', '$position', '$username', '$hashedPassword')";
    $conn->query($procedureCall);

    // Set the registration success flag to true
    $registrationSuccess = true;

    // Close the database connection
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <link rel="stylesheet" href="./css/register.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body>
<header>
        <nav>
            <div class="logo">Botika</div>
            <div class="burger-menu" onclick="toggleMenu()">☰</div>
            <div class="navigation">
                <ul>
                    <li><a href="admin.php"><i class="fa-solid fa-house"></i>Home</a></li>
                    <li> <a href="employees.php"><i class="fa-solid fa-user"></i>Employees</a></li>
                    <li><a href="backup.php"><i class="fa-solid fa-database"></i>Backup/Restore Database</a></li>
                    <li><a href="register.php"><i class="fa-solid fa-user-pen"></i>Register</a></li>
                    <li><a href="?logout=1"><i class="fa-solid fa-right-from-bracket" style="color: #FF0000"></i>Logout <?php echo $username; ?></a></li>
                </ul>
            </div>
        </nav>
    </header>
    <section class="registration-section">
        <h1>Create an Account</h1>
        <form class="registration-form" method="post" action="">
            <div class="form-group">
                <label for="firstname">Firstname:</label>
                <input type="text" id="firstname" name="firstname" required>
            </div>

            <div class="form-group">
                <label for="lastname">Lastname:</label>
                <input type="text" id="lastname" name="lastname" required>
            </div>

            <div class="form-group">
                <label for="position">Position:</label>
                <select id="position" name="position" required>
                <?php
                    include './php/connection.php';

                    $query = "SELECT * FROM positions";
                    $result = $conn->query($query);

                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . $row['position'] . "'>" . $row['position'] . "</option>";
                    }

                    $conn->close();
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit">Submit</button>
        </form>
    </section>

    <?php
        // Display JavaScript alert if registration is successful
        if ($registrationSuccess) {
            echo '<script>alert("Successfully registered"); window.location.href = "index.html";</script>';
        }
        ?>
    <script>
        function toggleMenu() {
            const navigation = document.querySelector('.navigation');
            navigation.classList.toggle('show');
        }
    </script>
</body>
</html>