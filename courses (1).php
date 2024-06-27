<?php
include 'session.php';

// Get the semester ID from the URL parameters
$semesterID = isset($_GET['semesterID']) ? intval($_GET['semesterID']) : 0;

// Database connection details
$servername = "sql105.infinityfree.com";
$dbname = "if0_36474140_collegeorganizr";
$dbusername = "if0_36474140";
$dbpassword = "0ilkuIuVkQ";

// Fetch the semester name and courses for the selected semester (if any)
$semesterName = '';
$courses = [];
$totalECTS = 0;

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $dbpassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch the semester name
    $stmt = $conn->prepare("SELECT semesterName FROM semesters WHERE semesterID = :semesterID");
    $stmt->bindParam(':semesterID', $semesterID);
    $stmt->execute();
    $semesterName = $stmt->fetchColumn();

    $stmt = $conn->prepare("SELECT * FROM courses WHERE semesterID = :semesterID ORDER BY courseID ASC");
    $stmt->bindParam(':semesterID', $semesterID);
    $stmt->execute();

    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($courses as $course) {
        $totalECTS += intval($course['ects']);
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Handle form submission for adding a new course
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['courseName']) && isset($_POST['courseNumber']) && isset($_POST['ects']) && isset($_POST['status'])) {
    // Get the input values
    $courseName = htmlspecialchars(trim($_POST['courseName']), ENT_QUOTES, 'UTF-8');
    $courseNumber = htmlspecialchars(trim($_POST['courseNumber']), ENT_QUOTES, 'UTF-8');
    $ects = intval($_POST['ects']);
    $status = htmlspecialchars(trim($_POST['status']), ENT_QUOTES, 'UTF-8');
    $userID = $_SESSION['userID'];

    try {
        $stmt = $conn->prepare("INSERT INTO courses (semesterID, courseName, courseNumber, ects, status) VALUES (:semesterID, :courseName, :courseNumber, :ects, :status)");
        $stmt->bindParam(':semesterID', $semesterID);
        $stmt->bindParam(':courseName', $courseName);
        $stmt->bindParam(':courseNumber', $courseNumber);
        $stmt->bindParam(':ects', $ects);
        $stmt->bindParam(':status', $status);
        $stmt->execute();

        // Fetch the newly added course
        $newCourseID = $conn->lastInsertId();
        $stmt = $conn->prepare("SELECT * FROM courses WHERE courseID = :courseID");
        $stmt->bindParam(':courseID', $newCourseID);
        $stmt->execute();

        $newCourse = $stmt->fetch(PDO::FETCH_ASSOC);
        $courses[] = $newCourse; // Append the new course to the end of the array

        // Update the total ECTS
        $totalECTS += $ects;

        
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Courses</title>
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
      position: relative; /* Ensure position for absolute elements inside */
      box-sizing: border-box; /* Include padding and border in width calculation */
      max-height: 80vh; /* Increased height */
      overflow-y: auto; /* Enable vertical scroll if content overflows */
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
      color: #000;
      max-height: calc(80vh - 250px); /* Adjust the height to account for padding and other content */
      overflow-y: auto; /* Make this box scrollable */
      margin-bottom: 20px; /* Add some space below the content */
    }

    .button, .course-button, .logout-button, .back-button {
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

    .button:hover, .course-button:hover, .logout-button:hover, .back-button:hover {
      background-color: #ff8a00;
      color: #fff;
    }

    .course-button {
      display: block;
      margin: 10px auto;
      width: calc(100% - 40px);
      color: #000; /* Change font color to black */
    }

    form {
      display: flex;
      flex-direction: column;
      align-items: center;
      width: 100%;
    }

    label, input[type="text"], input[type="number"], select, .button {
      width: calc(100% - 40px);
      margin-bottom: 10px;
    }

    ul {
      list-style-type: none;
      padding: 0;
      margin: 0;
    }

    .total-ects {
      position: absolute;
      bottom: 20px;
      left: 50%;
      transform: translateX(-50%);
      font-weight: bold;
      font-size: 18px;
      padding-top: 20px; /* Added padding on top */
    }

    select {
      padding: 10px;
      border: 2px solid #ffffff;
      border-radius: 5px;
      background-color: #fff;
      color: #000;
      font-family: 'Azeret Mono', sans-serif;
      font-size: 18px;
      cursor: pointer;
    }

    .logout-button, .back-button {
      width: auto;
      padding: 10px 20px;
      margin: 20px auto 0;
      display: inline-block;
    }
  </style>
</head>
<body>
  <h1>Courses of <?php echo htmlspecialchars($semesterName); ?></h1>
  <div class="box">
    <div class="box-title">Your Courses (<?php echo $totalECTS; ?> ECTS):</div>
    <div class="box-content">
      <?php if (empty($courses)): ?>
        <p>You have no courses saved yet.</p>
      <?php else: ?>
        <ul>
          <?php foreach ($courses as $course): ?>
            <li><button class="course-button" onclick="location.href='insidecourse.php?courseID=<?php echo $course['courseID']; ?>'">
          <?php echo htmlspecialchars($course['courseName']); ?>
            </button></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
    <div class="box-title" style="margin-top: 40px;">Add a New Course</div>
    <form action="" method="post">
    <label for="courseName">Course Name:</label>
    <input type="text" id="courseName" name="courseName" required>
    <label for="courseNumber">Course Number:</label>
    <input type="text" id="courseNumber" name="courseNumber" required>
    <label for="ects">ECTS:</label>
    <input type="number" id="ects" name="ects" required>
    <label for="status">Status:</label>
    <select id="status" name="status" required>
      <option value="ongoing">Ongoing</option>
      <option value="completed">Completed</option>
      <option value="failed">Failed</option>
      <option value="dropped">Dropped</option>
    </select>
  <button type="submit" class="button">Add Course</button>
  </form>
  </div>
  <a href="overview.php" class="back-button">Back to Semester Overview</a>
  <a href="logout.php" class="logout-button">Logout</a>
</body>
</html>
