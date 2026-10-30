
<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'];

$month = date('n');
$year = date('Y');

// Get total monthly expenses
$sql = "SELECT SUM(amount) AS total
        FROM expenses
        WHERE user_id = ?
        AND MONTH(expense_date) = ?
        AND YEAR(expense_date) = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $user_id, $month, $year);
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();

$total_expenses = $row['total'] ?? 0;
$stmt->close();

// Get monthly budget
$sql = "SELECT budget_amount FROM budgets
        WHERE user_id = ? AND month = ? AND year = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $user_id, $month, $year);
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();

$monthly_budget = $row ? $row['budget_amount'] : 0;
$stmt->close();

// Calculate remaining budget
$remaining_budget = $monthly_budget - $total_expenses;
?>

<!DOCTYPE html>
<html>
<head>
    <title>SpendWise Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<header class="dashboard-header">
    <h1>SpendWise</h1>
    <p>Smart Expense Tracker</p>
</header>

<nav class="dashboard-nav">
    <a href="dashboard.php">Dashboard</a>
    <a href="add_expense.php">Add Expense</a>
    <a href="view_expenses.php">View Expenses</a>
    <a href="budget.php">Budget</a>
    <a href="reports.php">Reports</a>
    <a href="savings.php">Savings</a>
    <a href="logout.php">Logout</a>
</nav>

<div class="container">

    <h2>Welcome, <?php echo htmlspecialchars($name); ?>!</h2>

    <p>Manage your expenses and track your spending easily.</p>

    <div class="cards">

        <div class="dashboard-card">
            <h3>Total Expenses</h3>
            <p>₹<?php echo number_format($total_expenses, 2); ?></p>
        </div>

        <div class="dashboard-card">
            <h3>Monthly Budget</h3>
            <p>₹<?php echo number_format($monthly_budget, 2); ?></p>
        </div>

        <div class="dashboard-card">
            <h3>Remaining Budget</h3>
            <p style="color:<?php echo $remaining_budget < 0 ? 'red' : '#32845b'; ?>">
                ₹<?php echo number_format($remaining_budget, 2); ?>
            </p>
        </div>

    </div>

</div>

</body>
</html>
