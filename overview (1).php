<?php
include 'session.php';

// Database connection details
$servername = "sql105.infinityfree.com";
$dbname = "if0_36474140_collegeorganizr";
$dbusername = "if0_36474140";
$dbpassword = "0ilkuIuVkQ";

// Fetch semesters for the user
$semesters = [];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $dbpassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Handle form submission for creating a new semester
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['semesterName'])) {
        $semesterName = htmlspecialchars(trim($_POST['semesterName']), ENT_QUOTES, 'UTF-8');
        $userID = $_SESSION['userID'];

        try {
            $stmt = $conn->prepare("INSERT INTO semesters (userID, semesterName) VALUES (:userID, :semesterName)");
            $stmt->bindParam(':userID', $userID);
            $stmt->bindParam(':semesterName', $semesterName);
            $stmt->execute();

            // Debugging output
            echo "New semester created successfully.";

            // Refresh the page to show the new semester
            header("Location: overview.php");
            exit;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    // Fetch semesters for the user
    $stmt = $conn->prepare("SELECT * FROM semesters WHERE userID = :userID ORDER BY semesterID ASC");
    $stmt->bindParam(':userID', $_SESSION['userID']);
    $stmt->execute();

    $semesters = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($semesters as $semester) {
        // Calculate planned ECTS
        $stmt = $conn->prepare("SELECT SUM(ects) as plannedECTS FROM courses WHERE semesterID = :semesterID");
        $stmt->bindParam(':semesterID', $semester['semesterID']);
        $stmt->execute();
        $plannedECTS = $stmt->fetchColumn();
        $semester['plannedECTS'] = $plannedECTS ? $plannedECTS : 0;

        // Calculate achieved ECTS
        $stmt = $conn->prepare("SELECT SUM(ects) as achievedECTS FROM courses WHERE semesterID = :semesterID AND status = 'completed'");
        $stmt->bindParam(':semesterID', $semester['semesterID']);
        $stmt->execute();
        $achievedECTS = $stmt->fetchColumn();
        $semester['achievedECTS'] = $achievedECTS ? $achievedECTS : 0;
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Semester Overview</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <link rel="icon" type="image/png" href="favicon.png">
  <link rel="shortcut icon" href="favicon.ico">
  <link rel="apple-touch-icon-precomposed" href="apple-touch-icon.png">
  <link href="https://fonts.googleapis.com/css?family=Azeret+Mono:400,400i,700,700i|VT323:300,300i,400,400i,700,700i" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/gh/n0nspace/tumblr-themes@main/framework/nnspc-fw.css" rel="stylesheet">
  <link rel="manifest" href="/manifest.json">


  <style>
    /* General Styles */
    html, body {
      font-family: 'Azeret Mono', sans-serif;
      background-color: #fff;
      color: #000;
      margin: 0;
      height: 100%;
      overflow: hidden; /* Prevent body scroll */
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    body {
      padding: 20px;
      box-sizing: border-box; /* Include padding and border in width calculation */
      overflow-y: auto; /* Allow vertical scroll within the body */
    }

    h1 {
      font-family: 'VT323', monospace;
      font-size: 56px;
      color: #ff1e00;
      margin-bottom: 20px;
      padding-top: 20px; /* Added padding on top */
    }

    .box {
      border: 2px solid #ff1e00;
      border-radius: 10px;
      padding: 20px;
      width: 100%;
      max-width: 800px; /* Increased width */
      background-color: #f9f9f9;
      margin-bottom: 20px;
      box-sizing: border-box; /* Include padding and border in width calculation */
      overflow-y: auto; /* Make the entire box scrollable vertically */
      overflow-x: hidden; /* Prevent horizontal scrolling */
    }

    .box-title {
      font-family: 'VT323', monospace;
      font-size: 32px;
      color: #ff1e00;
      margin-bottom: 20px;
    }

    .box-content {
      font-family: 'Azeret Mono', sans-serif;
      font-size: 18px;
      color: #000; /* Change font color to black for entire box content */
    }

    .button, .semester-button, .logout-button {
      background-color: #fff;
      color: #ff1e00;
      border: 2px solid #ff1e00;
      font-family: 'Azeret Mono', sans-serif;
      font-size: 20px;
      padding: 10px 20px;
      border-radius: 5px;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
      margin-top: 20px;
    }

    .button:hover, .semester-button:hover, .logout-button:hover {
      background-color: #ff8a00;
      color: #fff;
    }

    .semester-button {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      margin: 10px auto;
      width: calc(100% - 40px);
      color: #000; /* Change font color to black */
    }

    .semester-info {
      display: flex;
      justify-content: space-between;
      width: 100%;
    }

    ul {
      list-style-type: none;
      padding: 0;
      margin: 0;
    }

    .logout-button {
      width: auto;
      padding: 10px 20px;
      margin: 20px auto 0;
      display: inline-block;
    }

    form {
      display: flex;
      flex-direction: column;
      align-items: center;
      width: 100%;
    }

    label, input[type="text"], .button {
      width: calc(100% - 40px);
      margin-bottom: 10px;
    }

    label {
      font-size: 20px; /* Adjust the font size as needed */
    }

    p {
      /*font-size: 20px;  Larger font size */
      font-weight: 500; /* Thicker font */
      text-align: center; /* Center align the text */
      margin-bottom: 20px;
    }
  </style>
</head>
<body>
  <h1>Semester Overview</h1>
  <p> Enter the name of your current semester. <br></br> It will show up as a button. 
  <br></br> Once you click on that button, you will be redirected "inside" each semester. 
  <br></br> There you can add your courses.</p>
  <div class="box">
    <div class="box-title">Your Semesters:</div>
    <div class="box-content">
      <?php if (empty($semesters)): ?>
        <p>You have no semesters saved yet.</p>
      <?php else: ?>
        <ul>
          <?php foreach ($semesters as $semester): ?>
            <li>
              <button class="semester-button" onclick="location.href='courses.php?semesterID=<?php echo $semester['semesterID']; ?>'">
                <span><?php echo htmlspecialchars($semester['semesterName']); ?></span>
                <div class="semester-info">
                  <span>Planned ECTS: <?php echo $semester['plannedECTS']; ?></span>
                  <span>Achieved ECTS: <?php echo $semester['achievedECTS']; ?></span>
                </div>
              </button>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
    <form action="" method="post">
      <label for="semesterName">Semester Name:</label>
      <input type="text" id="semesterName" name="semesterName" required>
      <button type="submit" class="button">Create New Semester</button>
    </form>
  </div>
  <a href="logout.php" class="logout-button">Logout</a>
</body>
</html>
