<?php
session_start();

// Admin check
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

// DB connection
include "../config/db.php";

$error = "";

/* ================= ADD SESSION ================= */
if (isset($_POST['add_session'])) {
    $session_name = trim($_POST['session_name']);

    if ($session_name != "") {
        $stmt = mysqli_prepare($conn, "INSERT INTO sessions (session_name) VALUES (?)");
        mysqli_stmt_bind_param($stmt, "s", $session_name);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header("Location: sessions.php");
        exit;
    } else {
        $error = "Session name is required!";
    }
}

/* ================= UPDATE SESSION ================= */
if (isset($_POST['edit_session'])) {
    $session_id = intval($_POST['session_id']);
    $session_name = trim($_POST['session_name']);

    if ($session_id > 0 && $session_name != "") {
        $stmt = mysqli_prepare(
            $conn,
            "UPDATE sessions SET session_name=? WHERE session_id=?"
        );
        mysqli_stmt_bind_param($stmt, "si", $session_name, $session_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header("Location: sessions.php");
        exit;
    }
}

/* ================= DELETE SESSION ================= */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    if ($id > 0) {
        $stmt = mysqli_prepare($conn, "DELETE FROM sessions WHERE session_id=?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header("Location: sessions.php");
        exit;
    }
}

/* ================= FETCH SESSIONS ================= */
$sessions = mysqli_query($conn, "SELECT * FROM sessions");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Sessions</title>
    <style>
        body { font-family: Arial; margin: 20px; background-color:lightyellow; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border:1px solid #000; padding:8px; text-align:center; }
        input[type=text] { padding:6px; }
        input[type=submit] { padding:6px 12px; cursor:pointer; }
        a { text-decoration:none; color:blue; }
                 #b{
                 color:white;
                 background-color:green;
                 padding:5px 10px ;
                 border-radius:4px ; 
                 font-size:15px;"
            }
            #c{
                color:white;
                 background-color:red;
                 padding:6px 11px ;
                 border-radius:14px ; 
                 font-size:15px;"
            }
            #c:hover {
                color:blue;
                 background-color:#08e2e2ff;
            }           
#b:hover  {
    color: blue;
    background-color: #08e2e2;
}
#d:hover  {
    color: white;
    background-color:purple;
   
}
  #d{
                 color:white;
                 background-color:blue;
                 padding:6px 11px ;
                 border-radius:14px ; 
                 font-size:15px;"
  
  }
    </style>
</head>
<body >

<h2 align='center'>Session Management</h2>
<a href="dashboard.php">Dashboard</a> | <a href="../auth/logout.php">Logout</a>

<?php if ($error != "") { ?>
    <p style="color:red;"><?php echo $error; ?></p>
<?php } ?>

<!-- ADD SESSION -->
<h3>Add New Session</h3>
<form method="POST">
    <input type="text" name="session_name" placeholder="e.g. Fall 2024" required  >
    <input type="submit" name="add_session" value="Add Session"  id='b'>
</form>

<!-- SESSION TABLE -->
<h3>Existing Sessions</h3>

<?php if (mysqli_num_rows($sessions) > 0) { ?>
<table>
    <tr>
        <th>ID</th>
        <th>Session Name</th>
        <th>Actions</th>
    </tr>

    <?php while ($row = mysqli_fetch_assoc($sessions)) { ?>
    <tr>
        <td><?php echo $row['session_id']; ?></td>

        <td>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="session_id" value="<?php echo $row['session_id']; ?>">
                <input type="text" name="session_name"
                       value="<?php echo htmlspecialchars($row['session_name']); ?>" required>
                
            
        </td>

        <td>
            <input type="submit" name="edit_session" value="Update" id='c'>
            <a href="sessions.php?delete=<?php echo $row['session_id']; ?>"
               onclick="return confirm('Delete this session?')" id='d'>
               Delete
            </a>
             </form>
        </td>
    </tr>
   
    <?php } ?>
</table>
<?php } else { ?>
    <p>No sessions found.</p>
<?php } ?>

</body>
</html>
