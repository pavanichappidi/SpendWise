
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

// Total monthly expenses
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

// Category-wise expenses
$sql = "SELECT category, SUM(amount) AS total
        FROM expenses
        WHERE user_id = ?
        AND MONTH(expense_date) = ?
        AND YEAR(expense_date) = ?
        GROUP BY category";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $user_id, $month, $year);
$stmt->execute();

$result = $stmt->get_result();

$categories = [];
$amounts = [];

while ($row = $result->fetch_assoc()) {
    $categories[] = $row['category'];
    $amounts[] = $row['total'];
}

$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reports - SpendWise</title>

    <link rel="stylesheet" href="style.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

<header class="reports-header">
    <h2>SpendWise - Expense Reports</h2>
</header>

<div class="reports-container">

    <h2>Monthly Expense Report</h2>

    <div class="reports-summary">
        <h3>Total Expenses This Month</h3>

        <h2>₹<?php echo number_format($total_expenses, 2); ?></h2>
    </div>

    <h3>Category-wise Expense Chart</h3>

    <div class="chart-box">
        <canvas id="expenseChart"></canvas>
    </div>

    <script>
        const categories = <?php echo json_encode($categories); ?>;
        const amounts = <?php echo json_encode($amounts); ?>;

        const ctx = document.getElementById('expenseChart');

        new Chart(ctx, {
            type: 'pie',

            data: {
                labels: categories,

                datasets: [{
                    label: 'Expenses',
                    data: amounts
                }]
            },

            options: {
                responsive: true
            }
        });
    </script>

    <p><a href="dashboard.php">Back to Dashboard</a></p>

</div>

</body>
</html>
