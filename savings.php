
<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Add savings goal
if (isset($_POST['add_goal'])) {

    $goal_name = trim($_POST['goal_name']);
    $target_amount = $_POST['target_amount'];
    $saved_amount = $_POST['saved_amount'];
    $target_date = $_POST['target_date'];

    if (
        $goal_name == "" ||
        !is_numeric($target_amount) || $target_amount <= 0 ||
        !is_numeric($saved_amount) || $saved_amount < 0 ||
        $saved_amount > $target_amount
    ) {
        $message = "Please enter valid goal details.";
    } else {

        $sql = "INSERT INTO savings_goals
                (user_id, goal_name, target_amount, saved_amount, target_date)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "isdds",
            $user_id,
            $goal_name,
            $target_amount,
            $saved_amount,
            $target_date
        );

        if ($stmt->execute()) {
            $message = "Savings goal added successfully!";
        } else {
            $message = "Error adding savings goal.";
        }

        $stmt->close();
    }
}

// Get user's savings goals
$sql = "SELECT * FROM savings_goals WHERE user_id = ? ORDER BY goal_id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Savings Goals - SpendWise</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<header class="savings-header">
    <h2>SpendWise - Savings Goals</h2>
</header>

<div class="savings-container">

    <h2>Create a Savings Goal</h2>

    <form method="POST">

        <input type="text" name="goal_name"
               placeholder="Goal Name (Example: Buy a Laptop)"
               maxlength="100" required>

        <input type="number" name="target_amount"
               placeholder="Target Amount"
               min="0.01" step="0.01" required>

        <input type="number" name="saved_amount"
               placeholder="Amount Already Saved"
               min="0" step="0.01" value="0" required>

        
        <input type="date" name="target_date">

        <button type="submit" name="add_goal">
            Add Savings Goal
        </button>

    </form>

    <p><?php echo htmlspecialchars($message); ?></p>

    <hr>

    <h2>My Savings Goals</h2>

    <table class="savings-table">

        <tr>
            <th>Goal Name</th>
            <th>Target Amount</th>
            <th>Saved Amount</th>
            <th>Remaining Amount</th>
            <th>Target Date</th>
            <th>Progress</th>
        </tr>

        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {

                $remaining = $row['target_amount'] - $row['saved_amount'];

                $progress = ($row['saved_amount'] / $row['target_amount']) * 100;
        ?>

        <tr>
            <td><?php echo htmlspecialchars($row['goal_name']); ?></td>

            <td>₹<?php echo number_format($row['target_amount'], 2); ?></td>

            <td>₹<?php echo number_format($row['saved_amount'], 2); ?></td>

            <td>₹<?php echo number_format($remaining, 2); ?></td>

            <td><?php echo htmlspecialchars($row['target_date'] ?? 'Not set'); ?></td>

            <td><?php echo number_format($progress, 1); ?>%</td>
        </tr>

        <?php
            }
        } else {
            echo "<tr><td colspan='6'>No savings goals found.</td></tr>";
        }
        ?>

    </table>

    <p><a href="dashboard.php">Back to Dashboard</a></p>

</div>

</body>
</html>

<?php
$stmt->close();
?>
