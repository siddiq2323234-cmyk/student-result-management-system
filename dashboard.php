<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }

        
        .settings-btn {
            position: fixed;
            top: 10px;
            left: 10px;
            background-color: #ec2e15;
            color: #ffffff;
            padding: 10px 15px;
            border-radius: 6px;
            font-weight: bold;
            text-decoration: none;
            z-index: 1000;
            transition: 0.3s;
        }
        .settings-btn:hover {
            background-color: #3152e6;
        }

        .sidebar {
            width: 250px;
            height: 100vh;
            /* background: 
             url("/student_result_system/assets/welcome2.jpg") no-repeat center center/cover; */
            background-color: #79f3e3fd;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 60px; 
        }

        .sidebar h2 {
            color: #0c0c0c;
            text-align: center;
            margin-bottom: 30px;
        }

        .sidebar a {
            display: block;
            color: #050505;
            padding: 12px 20px;
            text-decoration: none;
            margin: 8px 15px;
            background-color: rgb(233, 235, 109);
            border-radius: 6px;
            transition: 0.3s;
            text-align: center;
            font-weight: bold;
        }

        .sidebar a:hover {
            background-color: #ec0ca2ff;
            color: #f2f8f4ff;
        }

        
        .content {
            margin-left: 250px;
            height: 100vh;
            background: 
                /* linear-gradient(rgba(241, 146, 206, 0.99), rgb(221, 180, 187)),  */
                url("/student_result_system/assets/welcome1.jpg") no-repeat center center/cover;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        
        .welcome {
            background: rgba(157, 241, 136, 0.9);
            padding: 40px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(14, 245, 33, 0.3);
            max-width: 600px;
        }
     

        .welcome h1 {
            margin-bottom: 15px;
            font-size: 40px;
        }

        .welcome p {
            font-size: 20px;
            color: #555fe6;
        }
        #log{
            color:white;
            background:blue;
        }
       #log:hover{
        color:green;
        background:white;

        }
    </style>
</head>
<body>


<a href="settings.php" class="settings-btn">⚙️ Settings</a>


<div class="sidebar">
    <h2>Admin Panel</h2>

    <a href="departments.php">Manage Departments</a>
    <a href="programs.php">Manage Programs</a>
    <a href="sessions.php">Manage Sessions</a>
    <a href="subjects.php">Manage Subjects</a>
    <a href="students.php">Manage Students</a>
    <a href="marks.php">Enter Marks</a>
    <a href="results.php">View Results</a>
    <a href="../auth/logout.php" id="log">Logout</a>
</div>


<div class="content">
    <div class="welcome">
        <h1>Welcome! <?php echo htmlspecialchars($_SESSION['admin_name']); ?> 👋</h1><br>
        <p>Select an option from the sidebar to manage the system.</p>
    </div>
</div>
<marquee behavior="slide" direction="left" text-color="red"> This is my semester Project</marquee>
</body>
</html>
