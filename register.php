
<?php
include "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!preg_match("/^[a-zA-Z ]{3,50}$/", $name)) {
        $message = "Invalid name. Use only letters.";
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email.";
    }
    elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^a-zA-Z0-9]).{8,}$/', $password)) {
        $message = "Password must contain uppercase, lowercase, number, special character and 8 characters.";
    }
    else {

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (name, email, password)
                VALUES (?, ?, ?)";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            $message = "Prepare Error: " . $conn->error;
        }
        else {
            $stmt->bind_param("sss", $name, $email, $hashed_password);

            if ($stmt->execute()) {
                $message = "Registration successful! User ID: " . $conn->insert_id;
            }
            else {
                $message = "Database Error: " . $stmt->error;
            }

            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>SpendWise Register</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="form">

    <h2>Create Account</h2>

    <form method="POST">

        <input type="text" name="name"
               placeholder="Enter Name"
               minlength="3" maxlength="50"
               pattern="[A-Za-z ]{3,50}" required>

        <input type="email" name="email"
               placeholder="Enter Email" required>

        <input type="password" name="password"
               placeholder="Create Password"
               minlength="8" required>

        <button type="submit" name="register">Register</button>

    </form>

    <p><?php echo htmlspecialchars($message); ?></p>

    <p>Already have an account? <a href="login.php">Login</a></p>

</div>

</body>
</html>
