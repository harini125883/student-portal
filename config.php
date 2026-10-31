<?php
/* config.php - database connection, constants and small helpers shared by every page */
session_start();

const DB_HOST = 'localhost';
const DB_NAME = 'student_portal';
const DB_USER = 'root';
const DB_PASS = '';            // change to your MySQL password

const UPLOAD_DIR = __DIR__ . '/uploads/';   // physical folder
const UPLOAD_URL = 'uploads/';              // relative path stored in DB

$COURSES = ['B.E. CSE', 'B.E. ECE', 'B.E. EEE', 'B.E. Mechanical', 'B.Tech IT', 'B.Tech AI & DS'];

/* PDO with exceptions + real prepared statements = protection against SQL injection */
try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    exit('Database connection failed. Check config.php and that schema.sql was imported.');
}

/* Escape output to prevent XSS */
function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

/* Flash message: store in session, show once after redirect */
function flash(string $type, string $msg): void { $_SESSION['flash'] = [$type, $msg]; }
function show_flash(): void {
    if (!empty($_SESSION['flash'])) {
        [$t, $m] = $_SESSION['flash'];
        echo '<div class="alert ' . e($t) . '" role="status">' . e($m) . '</div>';
        unset($_SESSION['flash']);
    }
}

/* Validate the text fields. Returns array of error messages (empty = valid). */
function validate_student(array $d, array $courses): array {
    $err = [];
    if (!preg_match("/^[A-Za-z][A-Za-z .'-]{2,59}$/", trim($d['name'] ?? '')))   $err[] = 'Name must be 3-60 letters.';
    if (!preg_match('/^\d{2}[A-Za-z]{2,4}\d{3}$/', trim($d['roll_no'] ?? '')))   $err[] = 'Roll number format is invalid (e.g. 21CS001).';
    if (!filter_var($d['email'] ?? '', FILTER_VALIDATE_EMAIL))                   $err[] = 'Email address is invalid.';
    if (!in_array($d['course'] ?? '', $courses, true))                           $err[] = 'Please select a valid course.';
    return $err;
}
