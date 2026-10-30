
<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid expense ID.");
}

$id = (int) $_GET['id'];
$message = "";

// Get existing expense
$sql = "SELECT * FROM expenses WHERE expense_id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Expense not found.");
}

$expense = $result->fetch_assoc();
$stmt->close();

// Update expense
if (isset($_POST['update_expense'])) {

    $expense_name = trim($_POST['expense_name']);
    $amount = $_POST['amount'];
    $category = $_POST['category'];
    $expense_date = $_POST['expense_date'];
    $payment_method = $_POST['payment_method'];
    $description = trim($_POST['description']);

    if (!is_numeric($amount) || $amount <= 0) {
        $message = "Please enter a valid amount.";
    } else {

        $sql = "UPDATE expenses
                SET expense_name = ?, amount = ?, category = ?,
                    expense_date = ?, payment_method = ?, description = ?
                WHERE expense_id = ? AND user_id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sdssssii",
            $expense_name,
            $amount,
            $category,
            $expense_date,
            $payment_method,
            $description,
            $id,
            $user_id
        );

        if ($stmt->execute()) {
            header("Location: view_expenses.php");
            exit();
        } else {
            $message = "Error updating expense.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Expense - SpendWise</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="edit-form">

    <h2>Edit Expense</h2>

    <form method="POST">

        <input type="text" name="expense_name"
               value="<?php echo htmlspecialchars($expense['expense_name']); ?>"
               required>

        <input type="number" name="amount"
               value="<?php echo htmlspecialchars($expense['amount']); ?>"
               min="0.01" step="0.01" required>

        <select name="category" required>
            <?php
            $categories = ["Food", "Travel", "Shopping", "Education", "Bills", "Other"];

            foreach ($categories as $category) {
                $selected = ($expense['category'] == $category) ? "selected" : "";
                echo "<option value='" . htmlspecialchars($category) . "' $selected>" . htmlspecialchars($category) . "</option>";
            }
            ?>
        </select>

        <label>Expense Date</label>
        <input type="date" name="expense_date"
               value="<?php echo htmlspecialchars($expense['expense_date']); ?>"
               required>

        <select name="payment_method" required>
            <?php
            $methods = ["Cash", "UPI", "Card", "Net Banking"];

            foreach ($methods as $method) {
                $selected = ($expense['payment_method'] == $method) ? "selected" : "";
                echo "<option value='" . htmlspecialchars($method) . "' $selected>" . htmlspecialchars($method) . "</option>";
            }
            ?>
        </select>

        <textarea name="description"><?php echo htmlspecialchars($expense['description']); ?></textarea>

        <button type="submit" name="update_expense">Update Expense</button>

    </form>

    <p><?php echo htmlspecialchars($message); ?></p>

    <a href="view_expenses.php">Back to Expenses</a>

</div>

</body>
</html>
