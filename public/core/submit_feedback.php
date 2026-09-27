<?php
require_once(__DIR__ . "/../config.php");
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendJson("Invalid request method", 1);
}

$impression       = trim($_POST["impression"]       ?? "");
$first_hear       = trim($_POST["first_hear"]       ?? "");
$missing_features = trim($_POST["missing_features"] ?? "");
$recommend        = (int)($_POST["recommend"]       ?? 0);
$useful_features  = trim($_POST["useful_features"]  ?? "");
$ease_of_use      = trim($_POST["ease_of_use"]      ?? "");

// At least one field must be filled
if ($impression === "" && $first_hear === "" && $missing_features === ""
    && $useful_features === "" && $ease_of_use === "") {
    sendJson("Please answer at least one question", 1);
}

if ($recommend < 0 || $recommend > 5) $recommend = 0;

$user_id = isLoggedIn() ? (int)($_SESSION["user_id"] ?? 0) : null;
if ($user_id === 0) $user_id = null;

try {
    $sql = "INSERT INTO feedback
            (user_id, impression, first_hear, missing_features, recommend, useful_features, ease_of_use)
            VALUES
            (:user_id, :impression, :first_hear, :missing_features, :recommend, :useful_features, :ease_of_use)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":user_id"          => $user_id,
        ":impression"       => $impression,
        ":first_hear"       => $first_hear,
        ":missing_features" => $missing_features,
        ":recommend"        => $recommend,
        ":useful_features"  => $useful_features,
        ":ease_of_use"      => $ease_of_use,
    ]);

    sendJson("Thanks for your feedback!", 0);
} catch (PDOException $e) {
    error_log("Feedback insert failed: " . $e->getMessage());
    sendJson("Could not save feedback. Please try again.", 1);
}