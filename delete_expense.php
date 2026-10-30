
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

$sql = "DELETE FROM expenses WHERE expense_id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $user_id);

if ($stmt->execute()) {
    header("Location: view_expenses.php");
    exit();
} else {
    echo "Error deleting expense.";
}

$stmt->close();
?>
