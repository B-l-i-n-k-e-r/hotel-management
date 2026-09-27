<?php
require_once(__DIR__ . "/../../config.php");

if (!isLoggedIn() || !isAdmin($pdo)) {
    header("Location: ../../login.php");
    exit;
}

if (!isset($_GET["user_id"]) || $_GET["user_id"] === "") {
    $_SESSION["error"] = "Invalid user id";
    header("Location: ../users.php");
    exit;
}

$user_id = (int)$_GET["user_id"];

// Don't allow an admin to delete themselves
if ($user_id === (int)($_SESSION["user_id"] ?? 0)) {
    $_SESSION["error"] = "You cannot delete your own account";
    header("Location: ../users.php");
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $_SESSION["message"] = "User deleted";
} catch (PDOException $e) {
    $_SESSION["error"] = "Could not delete user: " . $e->getMessage();
}

header("Location: ../users.php");
exit;