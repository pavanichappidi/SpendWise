
<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get search and filter values
$search = trim($_GET['search'] ?? '');
$category = $_GET['category'] ?? '';
$date = $_GET['date'] ?? '';

// Fetch filtered expenses
$sql = "SELECT * FROM expenses
        WHERE user_id = ?
        AND expense_name LIKE ?
        AND (category = ? OR ? = '')
        AND (expense_date = ? OR ? = '')
        ORDER BY expense_date DESC";

$stmt = $conn->prepare($sql);

$search_value = "%" . $search . "%";

$stmt->bind_param(
    "isssss",
    $user_id,
    $search_value,
    $category,
    $category,
    $date,
    $date
);

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Expenses - SpendWise</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<header class="view-header">
    <h2>SpendWise - My Expenses</h2>
</header>

<div class="view-container">

    <h2>Expense History</h2>

    <!-- Search and Filter Form -->
    <form method="GET">

        <input type="text" name="search"
               placeholder="Search expense name"
               value="<?php echo htmlspecialchars($search); ?>">

        <select name="category">
            <option value="">All Categories</option>

            <?php
            $categories = ["Food", "Travel", "Shopping", "Education", "Bills", "Other"];

            foreach ($categories as $item) {
                $selected = ($category == $item) ? "selected" : "";
                echo "<option value='" . htmlspecialchars($item) . "' $selected>" . htmlspecialchars($item) . "</option>";
            }
            ?>
        </select>

        <input type="date" name="date"
               value="<?php echo htmlspecialchars($date); ?>">

        <button type="submit">Search</button>

        <a href="view_expenses.php">Clear Filters</a>

    </form>

    <br>

    <table class="view-table">

        <tr>
            <th>ID</th>
            <th>Expense Name</th>
            <th>Amount</th>
            <th>Category</th>
            <th>Date</th>
            <th>Payment Method</th>
            <th>Description</th>
            <th>Action</th>
        </tr>

        <?php
        if ($result->num_rows > 0) {

            while ($row = $result->fetch_assoc()) {
        ?>

        <tr>
            <td><?php echo $row['expense_id']; ?></td>

            <td><?php echo htmlspecialchars($row['expense_name']); ?></td>

            <td>₹<?php echo number_format($row['amount'], 2); ?></td>

            <td><?php echo htmlspecialchars($row['category']); ?></td>

            <td><?php echo htmlspecialchars($row['expense_date']); ?></td>

            <td><?php echo htmlspecialchars($row['payment_method']); ?></td>

            <td><?php echo htmlspecialchars($row['description']); ?></td>

            <td>
                <a href="edit_expense.php?id=<?php echo $row['expense_id']; ?>">Edit</a>
                |
                <a href="delete_expense.php?id=<?php echo $row['expense_id']; ?>"
                   onclick="return confirm('Are you sure you want to delete this expense?');">
                    Delete
                </a>
            </td>
        </tr>

        <?php
            }

        } else {
            echo "<tr><td colspan='8'>No expenses found.</td></tr>";
        }
        ?>

    </table>

    <p><a href="add_expense.php">Add Another Expense</a></p>

    <p><a href="dashboard.php">Back to Dashboard</a></p>

</div>

</body>
</html>

<?php
$stmt->close();
?>
