<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

include "../config/db.php";

/* ---------- ADD PROGRAM ---------- */
if (isset($_POST['add_program'])) {
    $program_name = trim($_POST['program_name']);
    $dept_id = intval($_POST['dept_id']);

    if ($program_name != "" && $dept_id > 0) {
        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO programs (program_name, dept_id) VALUES (?, ?)"
        );
        mysqli_stmt_bind_param($stmt, "si", $program_name, $dept_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header("Location: programs.php");
        exit;
    }
}

/* ---------- UPDATE PROGRAM ---------- */
if (isset($_POST['edit_program'])) {
    $program_id = intval($_POST['program_id']);
    $program_name = trim($_POST['program_name']);
    $dept_id = intval($_POST['dept_id']);

    if ($program_id > 0 && $program_name != "" && $dept_id > 0) {
        $stmt = mysqli_prepare(
            $conn,
            "UPDATE programs SET program_name=?, dept_id=? WHERE program_id=?"
        );
        mysqli_stmt_bind_param($stmt, "sii", $program_name, $dept_id, $program_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header("Location: programs.php");
        exit;
    }
}

/* ---------- DELETE PROGRAM ---------- */
if (isset($_GET['delete'])) {
    $program_id = intval($_GET['delete']);

    if ($program_id > 0) {
        $stmt = mysqli_prepare($conn, "DELETE FROM programs WHERE program_id=?");
        mysqli_stmt_bind_param($stmt, "i", $program_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header("Location: programs.php");
        exit;
    }
}

/* ---------- FETCH DEPARTMENTS ---------- */
$departments = mysqli_query(
    $conn,
    "SELECT dept_id, name FROM departments"
);

/* ---------- FETCH PROGRAMS ---------- */
$programs = mysqli_query(
    $conn,
    "SELECT 
        programs.program_id,
        programs.program_name,
        departments.name AS department_name,
        departments.dept_id
     FROM programs
     JOIN departments ON programs.dept_id = departments.dept_id"
);
?>

<!DOCTYPE html>
<html>
<head>
    <title >Programs</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 30px; }
        th{border:3px solid #030303ff; padding:8px; text-align:center;font-size:25px; background-color:rgb(106, 233, 155); }
         td { border:1px solid rgb(14, 13, 13); padding:8px; text-align:center;font-size:25px; background-color:lightyellow; }
         #a{
            text :black;
            padding:5px 10px ;
            border-radius:4px ; 
            font-size:15px;"
         }
         
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
                 border-radius:4px ; 
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
    color: blue;
   
}
  #d{
    color :white; background-color:orange;  padding:10px 30px ;  border-radius:6px"
  
  }       
    </style>
</head>
<body style="background-color:rgb(238, 183, 183);">

<h2 align='center'>Program Management</h2>
<a href="dashboard.php">Dashboard</a> |
<a href="../auth/logout.php">Logout</a>

<!-- ADD PROGRAM -->
<h3>Add New Program</h3>
<form method="POST">
    <input type="text" name="program_name" placeholder="Program Name" required
      style="padding:10px 30px ;
    border-radius:6px;
      background-color:#77ddaaff;"
    >

    <select name="dept_id" required style="padding:5px 20px ;border-radius:6px;  background-color:#77ddaaff;">
        <option value="">Select Department</option>
        <?php while ($d = mysqli_fetch_assoc($departments)) { ?>
            <option value="<?php echo $d['dept_id']; ?>">
                <?php echo $d['name']; ?>
            </option>
        <?php } ?>
    </select>

    <input type="submit" name="add_program" value="Add Program" id='d' >
</form>

<!-- PROGRAM TABLE -->
<h3>Existing Programs</h3>

<table>
    <tr>
        <th>ID</th>
        <th>Program Name</th>
        <th>Department</th>
        <th>Actions</th>
    </tr>

<?php while ($row = mysqli_fetch_assoc($programs)) { ?>
<tr id='a'>
    <td><?php echo $row['program_id']; ?></td>

    <td  >
        <form method="POST">
            <input type="hidden" name="program_id" value="<?php echo $row['program_id']; ?>"  id='a'>
            <input type="text" name="program_name"
                   value="<?php echo htmlspecialchars($row['program_name']); ?>" required  id='a' >
    </td>

    <td>
        <select name="dept_id" required  id='a'>
            <?php
            $deps = mysqli_query($conn, "SELECT dept_id, name FROM departments");
            while ($d = mysqli_fetch_assoc($deps)) {
                $selected = ($d['dept_id'] == $row['dept_id']) ? "selected" : "";
                echo "<option value='{$d['dept_id']}' $selected>{$d['name']}</option>";
            }
            ?>
        </select>
    </td>

    <td>
        <input type="submit" name="edit_program" value="Update" id='b'
        >
        </form>

        <a href="programs.php?delete=<?php echo $row['program_id']; ?>"
           onclick="return confirm('Are you sure you want to delete this program?');" id='c'>
           Delete
        </a>
    </td>
</tr>
<?php } ?>
</table>

</body>
</html>
