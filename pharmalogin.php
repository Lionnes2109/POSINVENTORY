<?php
// Include the database connection
include './php/connection.php';

// Initialize a variable to check if login is unsuccessful
$loginFailed = false;

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check credentials with prepared statement
    $query = "SELECT * FROM employees WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password']) && $user['positionID'] == 1) {
        // Start a session and store the username
        session_start();
        $_SESSION['username'] = $username;

        // Redirect to pharmacist.php if login is successful
        header('Location: pharmacist.php');
        exit();
    } else {
        // Set login failed flag to true
        $loginFailed = true;
    }

    // Close the statement
    $stmt->close();
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pharmacist</title>
    <link rel="stylesheet" href="./css/loginadmin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body>
    <header>
        <nav>
        <div class="logo">Botika</div>
        <div class="burger-menu" onclick="toggleMenu()">☰</div>
        <div class="navigation">
            <ul>
                <li><a href="index.html"><i class="fa-solid fa-house"></i>Home</a></li>
            </ul>
        </div>
    </nav>
    </header>

    <section class="login-section">
        <h1>Login - Pharmacist/Cashier</h1>
        <!-- Display alert if login fails -->
        <?php if ($loginFailed): ?>
            <script>
                alert("Incorrect username or password. Please try again.");
            </script>
        <?php endif; ?>
        <form class="login-form" action="pharmalogin.php" method="post">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit">Login</button>
        </form>
    </section>

    <script>
        function toggleMenu() {
            const navigation = document.querySelector('.navigation');
            navigation.classList.toggle('show');
        }
    </script>
</body>
</html>
