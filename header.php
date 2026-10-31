<?php /* header.php - common page header. $title is set by the including page */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title ?? 'Admin') ?> | Student Admin</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
  <strong>Student Admin Panel</strong>
  <nav>
    <a href="index.php">All students</a>
    <a href="add.php">Add student</a>
    <a href="../frontend/register.html">Public form</a>
  </nav>
</header>
<main class="wrap">
<?php show_flash(); ?>
