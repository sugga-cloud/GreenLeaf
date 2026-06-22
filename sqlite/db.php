<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/schema.php';

// Initialize the SQLite model schemas and seed database defaults
Schema::init();

// Retrieve reference to database instance
$db = Schema::getDB();
?>
