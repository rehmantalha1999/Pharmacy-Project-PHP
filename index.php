<?php
session_start();
require "queries.php";

if (!empty($_POST)) {
  extract($_POST);

  if (checkUser($email, $password)) {
    $user = getUser($email);
    $_SESSION["user"] = $user;
    $_SESSION["cart"] = [];
    header("Location: pharmacy/pharmacy.php");
    exit;
  }
  $error = "Wrong email or password";
}

// auto login (homework)
if (validSession()) {
  header("Location: pharmacy/pharmacy.php");
  exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login | Medicine Tracker</title>
<link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Anybody:wght@100&family=Inter:wght@100;200;300;400;600&family=Work+Sans:wght@300&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="index.css">
</head>

<body>
  <div id="root">
    <div class="background">
      <div class="navbar">
        <div class="navbar-login">
          <h1 class="vms">λ</h1>
          <h4 class="valvemgmt">STOCK</h4>
          <hr class="hr-one" />
          <h1 class="qa">FAMILY LOGIN</h1>
          <div class="form">
            <form action="" method="POST" enctype="multipart/form-data">
              <label htmlFor="email" class="label"> Email </label>
              <br />
              <input class="inp-text" type="email" name="email" id="email" value="<?= isset($email) ? $email : '' ?>" />
              <br />
              <label htmlFor="password" class="label"> Password </label>
              <br />
              <input class="inp-text" type="password" name="password" id="password" />
              <br />
              <button class="btn">CONTINUE →</button>
              <?php
              if (isset($error)) {
                echo "<p class='error'>$error</p>";
              }
              ?>
            </form>
            <div class='dont'>
              <p>Don't have an account? <a href="./signup.php">Sign Up here</a></p>
            </div>
          </div>
          <hr class="hr-two" />
          <div class="footer">
            <h2 class="meta">MEDICINE</h2>
            <p class="note">TRACKER</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>

</html>