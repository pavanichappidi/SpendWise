
<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";

if (isset($_POST['add_expense'])) {

    $user_id = $_SESSION['user_id'];
    $expense_name = trim($_POST['expense_name']);
    $amount = $_POST['amount'];
    $category = $_POST['category'];
    $expense_date = $_POST['expense_date'];
    $payment_method = $_POST['payment_method'];
    $description = trim($_POST['description']);

    if (!is_numeric($amount) || $amount <= 0) {
        $message = "Please enter a valid amount.";
    } else {

        $sql = "INSERT INTO expenses
        (user_id, expense_name, amount, category, expense_date, payment_method, description)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "isdssss",
            $user_id,
            $expense_name,
            $amount,
            $category,
            $expense_date,
            $payment_method,
            $description
        );

        if ($stmt->execute()) {
            $message = "Expense added successfully!";
        } else {
            $message = "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Expense - SpendWise</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<header class="expense-header">
    <h2>SpendWise - Add Expense</h2>
</header>

<div class="expense-form">

    <h2>Enter Expense Details</h2>

    <form method="POST">

        <input type="text" name="expense_name"
               placeholder="Expense Name" required maxlength="100">

        <input type="number" name="amount"
               placeholder="Amount" min="0.01"
               step="0.01" required>

        <select name="category" required>
            <option value="">Select Category</option>
            <option value="Food">Food</option>
            <option value="Travel">Travel</option>
            <option value="Shopping">Shopping</option>
            <option value="Education">Education</option>
            <option value="Bills">Bills</option>
            <option value="Other">Other</option>
        </select>

        <input type="date" name="expense_date" required>

        <select name="payment_method" required>
            <option value="">Payment Method</option>
            <option value="Cash">Cash</option>
            <option value="UPI">UPI</option>
            <option value="Card">Card</option>
            <option value="Net Banking">Net Banking</option>
        </select>

        <textarea name="description"
                  placeholder="Description (optional)"></textarea>

        <button type="submit" name="add_expense">
            Add Expense
        </button>

    </form>

    <p><?php echo htmlspecialchars($message); ?></p>

    <p><a href="dashboard.php">Back to Dashboard</a></p>

</div>

</body>
</html>
