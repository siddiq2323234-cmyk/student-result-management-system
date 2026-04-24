<?php
session_start();


if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php");
    exit;
}


include "../config/db.php";

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

$error = "";


if (isset($_POST['add_department'])) {
    $name = trim($_POST['name']);

    if ($name != "") {
        $stmt = mysqli_prepare($conn, "INSERT INTO departments (name) VALUES (?)");
        mysqli_stmt_bind_param($stmt, "s", $name);

        if (!mysqli_stmt_execute($stmt)) {
            $error = "Insert failed: " . mysqli_error($conn);
        }

        mysqli_stmt_close($stmt);
        header("Location: departments.php");
        exit;
    } else {
        $error = "Please enter a department name!";
    }
}


if (isset($_POST['edit_department'])) {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);

    if ($id > 0 && $name != "") {
        $stmt = mysqli_prepare($conn, "UPDATE departments SET name=? WHERE dept_id=?");
        mysqli_stmt_bind_param($stmt, "si", $name, $id);

        if (!mysqli_stmt_execute($stmt)) {
            $error = "Update failed: " . mysqli_error($conn);
        }

        mysqli_stmt_close($stmt);
        header("Location: departments.php");
        exit;
    } else {
        $error = "Invalid department data!";
    }
}


if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    if ($id > 0) {
        $stmt = mysqli_prepare($conn, "DELETE FROM departments WHERE dept_id=?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header("Location: departments.php");
        exit;
    }
}


$departments = mysqli_query($conn, "SELECT dept_id, name FROM departments");

if (!$departments) {
    die("Fetch failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Departments</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 30px; }
        th{ border:3px solid #131212ff; padding:8px; text-align:center;font-size:25px; background-color:blue; }
        td { border:2px solid #181815ff; padding:8px; text-align:center;font-size:20px; background-color:#e0e257ff; }
        input[type=text] { padding:5px 30px; }
        input[type=submit] { padding:3px 400px; cursor:pointer; }
        a { text-decoration:none; color:yellow; }
    </style>
</head>
<body bgcolor='#e0e257ff'>

<h2 align='center'>Department Management</h2>
<a href="dashboard.php">Dashboard</a> | <a href="../auth/logout.php">Logout</a>

<?php if ($error != "") { ?>
    <p style="color:red;"><?php echo $error; ?></p>
<?php } ?>


<h3>Add New Department</h3>
<form method="POST">
    <input type="text" name="name" placeholder="Department Name" required
    style="padding:10px 30px ;border-radius:6px;  background-color:#77ddaaff;">
    <input type="submit" name="add_department" value="Add" 
    style="color :white;background-color:orange;padding:10px 30px ;border-radius:6px"
    >
</form>


<h3>Existing Departments</h3>

<?php if (mysqli_num_rows($departments) > 0) { ?>
<table border="2px"  >
    <tr>
        <th>ID</th>
        <th>Department Name</th>
        <th>Actions</th>
    </tr>

    <?php while ($row = mysqli_fetch_assoc($departments)) { ?>
    <tr>
        <td><?php echo $row['dept_id']; ?></td>
        <td>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="id" value="<?php echo $row['dept_id']; ?>">
                <input type="text" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" required>
             
           
        </td>
        <td>
              <input type="submit" name="edit_department" value="Update"
              style="color :white;background-color:green;padding:5px 10px ;border-radius:4px ; font-size:15px;"> 
            <a href="departments.php?delete=<?php echo $row['dept_id']; ?>"
               onclick="return confirm('Are you sure you want to delete this department?');"
               style="color:white; background-color:red; padding:5px 10px; border-radius:4px; text-decoration:none;font-size:17px;">
           
               Delete
            </a>
             </form>
        </td>
    </tr>
    <?php } ?>
</table>
<?php } else { ?>
    <p>No departments found.</p>
<?php } ?>

</body>
</html>
