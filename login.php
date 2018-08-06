<?php declare(strict_types=1);

session_start();
if (isset($_SESSION['logged_in'])) {
    unset($_SESSION['logged_in']);
    header('Location: index.php');
}

$password = require 'password.php';
if (isset($_POST['password']) && $_POST['password'] === $password) {
    $_SESSION['logged_in'] = true;
    header('Location: index.php');
    exit;
}

?><!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>TXT Notes</title>
  </head>
  <body>
    <form method="post">
      <input type="password" name="password">
      <button type="submit">Login</button>
    </form>
  </body>
</html>
