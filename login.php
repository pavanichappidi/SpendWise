
<?php
session_start();
include "db.php";

$message = "";

if (isset($_POST['login'])) {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];

            header("Location: dashboard.php");
            exit();

        } else {
            $message = "Incorrect password!";
        }

    } else {
        $message = "Email is not registered!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>SpendWise Login</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="form">

    <h2>Welcome Back!</h2>
    <p>Login to SpendWise</p>

    <form method="POST">

        <input type="email" name="email"
               placeholder="Enter Email" required>

        <input type="password" name="password"
               placeholder="Enter Password" required>

        <p style="text-align:right;">
            <a href="forgot_password.php">Forgot Password?</a>
        </p>

        <button type="submit" name="login">Login</button>

    </form>

    <p style="color:red;">
        <?php echo htmlspecialchars($message); ?>
    </p>

    <p>Don't have an account?
        <a href="register.php">Register</a>
    </p>

</div>

</body>
</html>
