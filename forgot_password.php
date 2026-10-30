
<?php
session_start();
include "db.php";

$message = "";

if (isset($_POST['reset'])) {

    $email = trim($_POST['email']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $message = "Passwords do not match!";
    } elseif (strlen($new_password) < 8) {
        $message = "Password must contain at least 8 characters!";
    } else {

        $sql = "SELECT user_id FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows == 1) {

            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            $update = "UPDATE users SET password = ? WHERE email = ?";
            $stmt2 = $conn->prepare($update);
            $stmt2->bind_param("ss", $hashed_password, $email);

            if ($stmt2->execute()) {
                $message = "Password changed successfully! You can login now.";
            } else {
                $message = "Something went wrong!";
            }

        } else {
            $message = "Email is not registered!";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password - SpendWise</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="form">

    <h2>Reset Password</h2>
    <p>Enter your registered email and new password.</p>

    <form method="POST">

        <input type="email" name="email"
               placeholder="Enter Registered Email" required>

        <input type="password" name="new_password"
               placeholder="Enter New Password" required>

        <input type="password" name="confirm_password"
               placeholder="Confirm New Password" required>

        <button type="submit" name="reset">Reset Password</button>

    </form>

    <p><?php echo htmlspecialchars($message); ?></p>

    <a href="login.php">Back to Login</a>

</div>

</body>
</html>
