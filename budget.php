
<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$month = date('n');
$year = date('Y');

$message = "";

// Save or update monthly budget
if (isset($_POST['save_budget'])) {

    $budget_amount = $_POST['budget_amount'];

    if (!is_numeric($budget_amount) || $budget_amount <= 0) {
        $message = "Please enter a valid budget amount.";
    } else {

        $sql = "INSERT INTO budgets (user_id, month, year, budget_amount)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE budget_amount = VALUES(budget_amount)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiid", $user_id, $month, $year, $budget_amount);

        if ($stmt->execute()) {
            $message = "Monthly budget saved successfully!";
        } else {
            $message = "Error saving budget.";
        }

        $stmt->close();
    }
}

// Get monthly budget
$sql = "SELECT budget_amount FROM budgets
        WHERE user_id = ? AND month = ? AND year = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $user_id, $month, $year);
$stmt->execute();

$result = $stmt->get_result();
$budget = $result->fetch_assoc();

$budget_amount = $budget ? $budget['budget_amount'] : 0;
$stmt->close();

// Calculate monthly expenses
$sql = "SELECT SUM(amount) AS total FROM expenses
        WHERE user_id = ?
        AND MONTH(expense_date) = ?
        AND YEAR(expense_date) = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $user_id, $month, $year);
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();

$total_expenses = $row['total'] ?? 0;

$remaining = $budget_amount - $total_expenses;

$percentage = ($budget_amount > 0)
    ? ($total_expenses / $budget_amount) * 100
    : 0;

$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Monthly Budget - SpendWise</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<header class="budget-header">
    <h2>SpendWise - Monthly Budget</h2>
</header>

<div class="budget-container">

    <h2>Set Your Budget</h2>

    <form method="POST">
        <input type="number" name="budget_amount"
               placeholder="Enter Monthly Budget"
               min="0.01" step="0.01" required>

        <button type="submit" name="save_budget">
            Save Budget
        </button>
    </form>

    <p><?php echo htmlspecialchars($message); ?></p>

    <hr>

    <h3>Monthly Summary</h3>

    <p>Monthly Budget: ₹<?php echo number_format($budget_amount, 2); ?></p>

    <p>Total Expenses: ₹<?php echo number_format($total_expenses, 2); ?></p>

    <p>Remaining Budget: ₹<?php echo number_format($remaining, 2); ?></p>

    <p>Budget Used: <?php echo number_format($percentage, 2); ?>%</p>

    <?php if ($budget_amount > 0 && $percentage >= 100) { ?>

        <p style="color:red;">Warning! You have exceeded your budget.</p>

    <?php } elseif ($budget_amount > 0 && $percentage >= 80) { ?>

        <p style="color:orange;">Warning! You have used 80% of your budget.</p>

    <?php } ?>

    <p><a href="dashboard.php">Back to Dashboard</a></p>

</div>

</body>
</html>

