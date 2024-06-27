<?php

// Secure session cookie settings (optional, should be consistent across all scripts)
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Strict'
]);

// Start the session
session_start();

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Redirect to overview if already logged in
if (isset($_SESSION['userID'])) {
    header("Location: overview.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars(trim($_POST['username']), ENT_QUOTES, 'UTF-8');
    $password = trim($_POST['password']);

    // Debug output
    echo "Received Username: " . htmlspecialchars($username, ENT_QUOTES, 'UTF-8') . "<br>";
    echo "Received Password: " . htmlspecialchars($password, ENT_QUOTES, 'UTF-8') . "<br>";

    // Database connection details
    $servername = "sql105.infinityfree.com"; // Update with your database server details
    $dbname = "if0_36474140_collegeorganizr"; // Update with your database name
    $dbusername = "if0_36474140"; // Update with your database username
    $dbpassword = "0ilkuIuVkQ"; // Update with your database password

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $dbpassword);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT * FROM user WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (password_verify($password, $user['password'])) {
                // Regenerate session ID to prevent session fixation attacks
                session_regenerate_id(true);

                // Set session variables
                $_SESSION['userID'] = $user['userID'];
                $_SESSION['username'] = $user['username'];

                echo "Login successful. Session userID: " . $_SESSION['userID'] . "<br>"; // Debugging output
                echo "Redirecting to overview...<br>";

                header("Location: overview.php");
                exit;
            } else {
                $error = "Invalid password.";
                echo $error . "<br>"; // Debugging output
            }
        } else {
            $error = "User not found.";
            echo $error . "<br>"; // Debugging output
        }

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>


<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <link rel="icon" type="image/png" href="favicon.png">
  <link rel="shortcut icon" href="favicon.ico">
  <link rel="apple-touch-icon-precomposed" href="apple-touch-icon.png">
  <link href="https://fonts.googleapis.com/css?family=Azeret+Mono:400,400i,700,700i|VT323:300,300i,400,400i,700,700i" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/gh/n0nspace/tumblr-themes@main/framework/nnspc-fw.css" rel="stylesheet">
  <link rel="manifest" href="/manifest.json">


  <style>
    /* General Styles */
    body {
      font-family: 'Azeret Mono', sans-serif;
      background-color: #fff;
      color: #000;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
      text-align: center;
      flex-direction: column;
    }

    h1 {
      font-family: 'VT323', monospace;
      font-size: 56px;
      color: #ff1e00;
      margin-bottom: 40px;
    }

    .form-container {
      display: flex;
      flex-direction: column;
      gap: 10px;
      width: 300px;
    }

    .input {
      padding: 10px;
      font-size: 16px;
      font-family: 'Azeret Mono', sans-serif;
      border: 1px solid #ccc;
      border-radius: 5px;
      margin: 5px 0;
    }

    .button {
      background-color: #ff1e00;
      color: #fff;
      font-family: 'Azeret Mono', sans-serif;
      font-size: 20px;
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      text-decoration: none;
      width: 100%;
      align-self: center;
    }

    .button:hover {
      background-color: #ff8a00;
    }

    .error {
      color: red;
      font-size: 16px;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>
  <div>
    <h1>Login</h1>
    <?php if (isset($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    <form action="" method="post" class="form-container">
      <label for="username">Username:</label>
      <input type="text" id="username" name="username" class="input" required>
      <label for="password">Password:</label>
      <input type="password" id="password" name="password" class="input" required>
      <button type="submit" class="button">Log in</button>
    </form>
  </div>
</body>
</html>
