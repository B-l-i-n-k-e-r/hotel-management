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

// Don't allow an admin to demote themselves
if ($user_id === (int)($_SESSION["user_id"] ?? 0)) {
    $_SESSION["error"] = "You cannot change your own admin role";
    header("Location: ../users.php");
    exit;
}

$check = $pdo->prepare("SELECT user_admin FROM users WHERE user_id = ?");
$check->execute([$user_id]);
$current = $check->fetchColumn();

if ($current === false) {
    $_SESSION["error"] = "User not found";
    header("Location: ../users.php");
    exit;
}

$new = $current == 1 ? 0 : 1;
$stmt = $pdo->prepare("UPDATE users SET user_admin = ? WHERE user_id = ?");
$stmt->execute([$new, $user_id]);

$_SESSION["message"] = $new ? "User promoted to admin" : "Admin privileges removed";
header("Location: ../users.php");
exit;