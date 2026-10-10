<?php

    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASSWORD
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // Force the session timezone so PHP's NOW() and MariaDB's NOW() agree.
        $pdo->exec("SET time_zone = '+03:00'");
        session_start();
    } catch (PDOException $err) {
        die($err->getMessage());
    }