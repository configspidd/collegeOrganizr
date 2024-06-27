<?php
include 'session.php';

// Get the course ID from the URL parameters
$courseID = isset($_GET['courseID']) ? intval($_GET['courseID']) : 0;

// Database connection details
$servername = "sql105.infinityfree.com";
$dbname = "if0_36474140_collegeorganizr";
$dbusername = "if0_36474140";
$dbpassword = "0ilkuIuVkQ";

// Fetch course details
$course = [];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $dbpassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("SELECT * FROM courses WHERE courseID = :courseID");
    $stmt->bindParam(':courseID', $courseID);
    $stmt->execute();

    $course = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Handle form submission for updating course status and adding links
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['status'])) {
        $status = htmlspecialchars(trim($_POST['status']), ENT_QUOTES, 'UTF-8');

        try {
            $stmt = $conn->prepare("UPDATE courses SET status = :status WHERE courseID = :courseID");
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':courseID', $courseID);
            $stmt->execute();

            echo "Course status updated successfully.";
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    if (isset($_POST['link'])) {
        $link = htmlspecialchars(trim($_POST['link']), ENT_QUOTES, 'UTF-8');

        try {
            $stmt = $conn->prepare("INSERT INTO course_links (courseID, link) VALUES (:courseID, :link)");
            $stmt->bindParam(':courseID', $courseID);
            $stmt->bindParam(':link', $link);
            $stmt->execute();

            echo "Link added successfully.";
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    // Handle form submission for adding a new assignment
    if (isset($_POST['assignmentName']) && isset($_POST['assignmentPoints'])) {
        $assignmentName = htmlspecialchars(trim($_POST['assignmentName']), ENT_QUOTES, 'UTF-8');
        $assignmentPoints = floatval($_POST['assignmentPoints']);

        try {
            $stmt = $conn->prepare("INSERT INTO assignments (courseID, name, points) VALUES (:courseID, :name, :points)");
            $stmt->bindParam(':courseID', $courseID);
            $stmt->bindParam(':name', $assignmentName);
            $stmt->bindParam(':points', $assignmentPoints);
            $stmt->execute();

            
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}

// Fetch course links
$courseLinks = [];
try {
    $stmt = $conn->prepare("SELECT * FROM course_links WHERE courseID = :courseID");
    $stmt->bindParam(':courseID', $courseID);
    $stmt->execute();

    $courseLinks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Fetch assignments
$assignments = [];
$totalPoints = 0;
try {
    $stmt = $conn->prepare("SELECT * FROM assignments WHERE courseID = :courseID");
    $stmt->bindParam(':courseID', $courseID);
    $stmt->execute();

    $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($assignments as $assignment) {
        $totalPoints += $assignment['points'];
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Course Details - <?php echo htmlspecialchars($course['courseName']); ?></title>
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
      overflow-y: auto; /* Enable vertical scroll if content overflows */
      max-height: 80vh; /* Increased height */
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
    }

    .button, .logout-button, .back-button {
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

    .button:hover, .logout-button:hover, .back-button:hover {
      background-color: #ff8a00;
      color: #fff;
    }

    form {
      display: flex;
      flex-direction: column;
      align-items: center;
      width: 100%;
    }

    label, input[type="text"], input[type="number"], .button {
      width: calc(100% - 40px);
      margin-bottom: 10px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    th, td {
      border: 1px solid #ddd;
      padding: 8px;
      text-align: left;
    }

    th {
      background-color: #f2f2f2;
    }

    ul {
      list-style-type: none;
      padding: 0;
      margin: 0;
    }

    .total-points {
      margin-top: 20px;
      font-weight: bold;
    }
  </style>
</head>
<body>
  <h1>Course Details for <?php echo htmlspecialchars($course['courseName']); ?></h1>
  <div class="box">
    <div class="box-title"><?php echo htmlspecialchars($course['courseName']); ?></div>
    <div class="box-content">
      <p><strong>Course Number:</strong> <?php echo htmlspecialchars($course['courseNumber']); ?></p>
      <p><strong>ECTS:</strong> <?php echo htmlspecialchars($course['ects']); ?></p>
      <form action="" method="post">
        <label for="status">Status:</label>
        <select id="status" name="status" required>
          <option value="ongoing" <?php echo ($course['status'] == 'ongoing') ? 'selected' : ''; ?>>Ongoing</option>
          <option value="completed" <?php echo ($course['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
          <option value="failed" <?php echo ($course['status'] == 'failed') ? 'selected' : ''; ?>>Failed</option>
          <option value="dropped" <?php echo ($course['status'] == 'dropped') ? 'selected' : ''; ?>>Dropped</option>
        </select>
        <button type="submit" class="button">Update Status</button>
      </form>
      <form action="" method="post">
        <label for="link">Add Link:</label>
        <input type="text" id="link" name="link" required>
        <button type="submit" class="button">Add Link</button>
      </form>
      <h3>Links:</h3>
      <ul>
        <?php foreach ($courseLinks as $link): ?>
          <li><a href="<?php echo htmlspecialchars($link['link']); ?>" target="_blank"><?php echo htmlspecialchars($link['link']); ?></a></li>
        <?php endforeach; ?>
      </ul>
      <br></br>
      <form action="" method="post">
        <label for="assignmentName">Assignment Name:</label>
        <input type="text" id="assignmentName" name="assignmentName" required>
        <label for="assignmentPoints">Assignment Points:</label>
        <input type="number" id="assignmentPoints" name="assignmentPoints" required>
        <button type="submit" class="button">Add Assignment</button>
      </form>
      <h3>Assignments:</h3>
      <table>
        <thead>
          <tr>
            <th>Assignment Name</th>
            <th>Points</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($assignments as $assignment): ?>
            <tr>
              <td><?php echo htmlspecialchars($assignment['name']); ?></td>
              <td><?php echo htmlspecialchars($assignment['points']); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <p class="total-points">Total Points: <?php echo $totalPoints; ?></p>
    </div>
  </div>
  <a href="courses.php?semesterID=<?php echo $course['semesterID']; ?>" class="back-button">Back to Courses</a>
  <a href="logout.php" class="logout-button">Logout</a>
</body>
</html>
