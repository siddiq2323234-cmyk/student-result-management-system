<?php
session_start();

// Admin protection
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

include "../config/db.php";
$error = "";

/* ================= ADD SUBJECT ================= */
if (isset($_POST['add_subject'])) {
    $name   = trim($_POST['subject_name']);
    $code   = trim($_POST['subject_code']);
    $program_id = intval($_POST['program_id']);
    $semester   = intval($_POST['semester']);
    $credit     = intval($_POST['credit_hours']);
    $type       = trim($_POST['type']);

    if ($name && $code && $program_id && $semester && $credit && $type) {
        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO subjects 
            (subject_name, subject_code, program_id, semester, credit_hours, type)
            VALUES (?,?,?,?,?,?)"
        );
        mysqli_stmt_bind_param($stmt, "ssiiis", $name, $code, $program_id, $semester, $credit, $type);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header("Location: subjects.php");
        exit;
    } else {
        $error = "All fields are required!";
    }
}

/* ================= EDIT SUBJECT ================= */
if (isset($_POST['edit_subject'])) {
    $id = intval($_POST['subject_id']);
    $name   = trim($_POST['subject_name']);
    $code   = trim($_POST['subject_code']);
    $program_id = intval($_POST['program_id']);
    $semester   = intval($_POST['semester']);
    $credit     = intval($_POST['credit_hours']);
    $type       = trim($_POST['type']);

    if ($id && $name && $code && $program_id && $semester && $credit && $type) {
        $stmt = mysqli_prepare(
            $conn,
            "UPDATE subjects SET subject_name=?, subject_code=?, program_id=?, semester=?, credit_hours=?, type=? WHERE subject_id=?"
        );
        mysqli_stmt_bind_param($stmt, "ssiiisi", $name, $code, $program_id, $semester, $credit, $type, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header("Location: subjects.php");
        exit;
    } else {
        $error = "All fields are required for update!";
    }
}

/* ================= DELETE SUBJECT ================= */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM subjects WHERE subject_id=$id");
    header("Location: subjects.php");
    exit;
}

/* ================= FETCH DATA ================= */
$subjects = mysqli_query($conn, "
    SELECT s.*, p.program_name
    FROM subjects s
    JOIN programs p ON s.program_id = p.program_id
");
$programs = mysqli_query($conn, "SELECT * FROM programs");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Subject Management</title>
    <style>
        body { font-family: Arial; margin: 20px;  background-color:rgb(245, 239, 183)}
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border:1px solid rgb(20, 20, 19); padding:6px; text-align:center; }
        input, select { padding:5px; margin-bottom:5px; }
        form { margin:0; }
    </style>
</head>
<body>

<h2 align='center'>Subject Management</h2>
<a href="dashboard.php">Dashboard</a> | <a href="../auth/logout.php">Logout</a>

<?php if ($error) echo "<p style='color:red'>$error</p>"; ?>

<!-- ADD SUBJECT -->
<h3>Add Subject</h3>
<form method="POST">
    <input type="text" name="subject_name" placeholder="Subject Name" required>
    <input type="text" name="subject_code" placeholder="Subject Code" required>

    <select name="program_id" required>
        <option value="">Select Program</option>
        <?php while ($p = mysqli_fetch_assoc($programs)) { ?>
            <option value="<?= $p['program_id'] ?>"><?= $p['program_name'] ?></option>
        <?php } ?>
    </select>

    <input type="number" name="semester" placeholder="Semester" required>
    <input type="number" name="credit_hours" placeholder="Credit Hours" required>

    <select name="type" required>
        <option value="">Subject Type</option>
        <option value="Core">Core</option>
        <option value="Elective">Elective</option>
    </select>

    <input type="submit" name="add_subject" value="Add Subject">
</form>

<!-- SUBJECT TABLE -->
<h3>Existing Subjects</h3>

<table>
<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Code</th>
    <th>Program</th>
    <th>Semester</th>
    <th>Credit</th>
    <th>Type</th>
    <th>Action</th>
</tr>

<?php
mysqli_data_seek($subjects, 0); // reset pointer to start
while ($row = mysqli_fetch_assoc($subjects)) { ?>
<tr>
    <td><?= $row['subject_id'] ?></td>
    <td>
        <form method="POST" style="display:inline-block;">
            <input type="hidden" name="subject_id" value="<?= $row['subject_id'] ?>">
            <input type="text" name="subject_name" value="<?= htmlspecialchars($row['subject_name']) ?>" required>
    </td>
    <td><input type="text" name="subject_code" value="<?= htmlspecialchars($row['subject_code']) ?>" required></td>
    <td>
        <select name="program_id" required>
            <?php
            mysqli_data_seek($programs, 0);
            while ($p = mysqli_fetch_assoc($programs)) {
                $selected = ($p['program_id'] == $row['program_id']) ? 'selected' : '';
                echo "<option value='{$p['program_id']}' $selected>{$p['program_name']}</option>";
            }
            ?>
        </select>
    </td>
    <td><input type="number" name="semester" value="<?= $row['semester'] ?>" required></td>
    <td><input type="number" name="credit_hours" value="<?= $row['credit_hours'] ?>" required></td>
    <td>
        <select name="type" required>
            <option value="Core" <?= ($row['type']=='Core')?'selected':'' ?>>Core</option>
            <option value="Elective" <?= ($row['type']=='Elective')?'selected':'' ?>>Elective</option>
        </select>
    </td>
    <td>
            <input type="submit" name="edit_subject" value="Update">
            </form>
            <a href="subjects.php?delete=<?= $row['subject_id'] ?>" onclick="return confirm('Delete this subject?')">Delete</a>
    </td>
</tr>
<?php } ?>
</table>

</body>
</html>
