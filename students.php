<?php
session_start();

// Only admin can access
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

include "../config/db.php";
$error = "";

/* ================= FETCH STUDENT FOR EDIT ================= */
$editStudent = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $editStudent = mysqli_fetch_assoc(
        mysqli_query($conn, "SELECT * FROM students WHERE student_id=$id")
    );
}

/* ================= ADD STUDENT ================= */
if (isset($_POST['add_student'])) {

    $roll_number  = trim($_POST['roll_number']);
    $student_name = trim($_POST['student_name']);
    $dept_id      = intval($_POST['dept_id']);
    $program_id   = intval($_POST['program_id']);
    $session_id   = intval($_POST['session_id']);
    $semester     = intval($_POST['semester']);

    $image_name = null;
    if (!empty($_FILES['student_image']['name'])) {
        $ext = pathinfo($_FILES['student_image']['name'], PATHINFO_EXTENSION);
        $image_name = uniqid('stu_').".".$ext;
        move_uploaded_file($_FILES['student_image']['tmp_name'], "../uploads/".$image_name);
    }

    if ($roll_number && $student_name && $dept_id && $program_id && $session_id && $semester) {
        $stmt = mysqli_prepare($conn, "
            INSERT INTO students
            (roll_number, student_name, dept_id, program_id, session_id, semester, student_image)
            VALUES (?,?,?,?,?,?,?)
        ");
        mysqli_stmt_bind_param($stmt, "ssiiiis",
            $roll_number, $student_name, $dept_id,
            $program_id, $session_id, $semester, $image_name
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header("Location: students.php");
        exit;
    } else {
        $error = "All fields are required!";
    }
}

/* ================= UPDATE STUDENT ================= */
if (isset($_POST['update_student'])) {

    $student_id   = intval($_POST['student_id']);
    $roll_number  = trim($_POST['roll_number']);
    $student_name = trim($_POST['student_name']);
    $dept_id      = intval($_POST['dept_id']);
    $program_id   = intval($_POST['program_id']);
    $session_id   = intval($_POST['session_id']);
    $semester     = intval($_POST['semester']);

    // Old image
    $old = mysqli_fetch_assoc(
        mysqli_query($conn, "SELECT student_image FROM students WHERE student_id=$student_id")
    );
    $image_name = $old['student_image'];

    // New image upload
    if (!empty($_FILES['student_image']['name'])) {
        if ($image_name && file_exists("../uploads/".$image_name)) {
            unlink("../uploads/".$image_name);
        }
        $ext = pathinfo($_FILES['student_image']['name'], PATHINFO_EXTENSION);
        $image_name = uniqid('stu_').".".$ext;
        move_uploaded_file($_FILES['student_image']['tmp_name'], "../uploads/".$image_name);
    }

    $stmt = mysqli_prepare($conn, "
        UPDATE students SET
            roll_number=?,
            student_name=?,
            dept_id=?,
            program_id=?,
            session_id=?,
            semester=?,
            student_image=?
        WHERE student_id=?
    ");
    mysqli_stmt_bind_param($stmt, "ssiiiisi",
        $roll_number, $student_name, $dept_id,
        $program_id, $session_id, $semester,
        $image_name, $student_id
    );
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: students.php");
    exit;
}

/* ================= DELETE STUDENT ================= */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    $res = mysqli_query($conn, "SELECT student_image FROM students WHERE student_id=$id");
    $row = mysqli_fetch_assoc($res);
    if ($row && $row['student_image']) {
        $file = "../uploads/".$row['student_image'];
        if (file_exists($file)) unlink($file);
    }

    mysqli_query($conn, "DELETE FROM students WHERE student_id=$id");
    header("Location: students.php");
    exit;
}

/* ================= FETCH DATA ================= */
$students = mysqli_query($conn, "
    SELECT s.*, d.name AS dept_name, p.program_name, se.session_name
    FROM students s
    JOIN departments d ON s.dept_id=d.dept_id
    JOIN programs p ON s.program_id=p.program_id
    JOIN sessions se ON s.session_id=se.session_id
");

$departments = mysqli_query($conn, "SELECT * FROM departments");
$programs    = mysqli_query($conn, "SELECT * FROM programs");
$sessions    = mysqli_query($conn, "SELECT * FROM sessions");
?>

<!DOCTYPE html>
<html>
<head>
    <title align="center">Student Management</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border:1px solid #000; padding:6px; text-align:center; }
        input, select { padding:5px; margin:3px; }
       <img src="../uploads/<?= $row['student_image'] ?>" style="max-width:150px; max-height:150px;">

    </style>
</head>
<body>

<h2>Student Management</h2>
<a href="dashboard.php">Dashboard</a> | 
<a href="../auth/logout.php">Logout</a>

<?php if ($error) echo "<p style='color:red'>$error</p>"; ?>

<h3><?= $editStudent ? 'Update Student' : 'Add Student' ?></h3>

<form method="POST" enctype="multipart/form-data">

<?php if ($editStudent): ?>
    <input type="hidden" name="student_id" value="<?= $editStudent['student_id'] ?>">
<?php endif; ?>

<input type="text" name="roll_number" placeholder="Roll Number"
       value="<?= $editStudent['roll_number'] ?? '' ?>" required>

<input type="text" name="student_name" placeholder="Student Name"
       value="<?= $editStudent['student_name'] ?? '' ?>" required>

<select name="dept_id" required>
    <option value="">Department</option>
    <?php while($d = mysqli_fetch_assoc($departments)) { ?>
        <option value="<?= $d['dept_id'] ?>"
            <?= ($editStudent && $editStudent['dept_id']==$d['dept_id'])?'selected':'' ?>>
            <?= $d['name'] ?>
        </option>
    <?php } ?>
</select>

<select name="program_id" required>
    <option value="">Program</option>
    <?php while($p = mysqli_fetch_assoc($programs)) { ?>
        <option value="<?= $p['program_id'] ?>"
            <?= ($editStudent && $editStudent['program_id']==$p['program_id'])?'selected':'' ?>>
            <?= $p['program_name'] ?>
        </option>
    <?php } ?>
</select>

<select name="session_id" required>
    <option value="">Session</option>
    <?php while($s = mysqli_fetch_assoc($sessions)) { ?>
        <option value="<?= $s['session_id'] ?>"
            <?= ($editStudent && $editStudent['session_id']==$s['session_id'])?'selected':'' ?>>
            <?= $s['session_name'] ?>
        </option>
    <?php } ?>
</select>

<input type="number" name="semester" placeholder="Semester"
       value="<?= $editStudent['semester'] ?? '' ?>" required>

<input type="file" name="student_image" accept="image/*">

<input type="submit"
       name="<?= $editStudent ? 'update_student' : 'add_student' ?>"
       value="<?= $editStudent ? 'Update Student' : 'Add Student' ?>">
</form>

<h3>Existing Students</h3>

<table>
<tr>
    <th>ID</th>
    <th>Roll</th>
    <th>Name</th>
    <th>Department</th>
    <th>Program</th>
    <th>Session</th>
    <th>Semester</th>
    <th>Image</th>
    <th>Action</th>
</tr>

<?php while($row = mysqli_fetch_assoc($students)) { ?>
<tr>
    <td><?= $row['student_id'] ?></td>
    <td><?= htmlspecialchars($row['roll_number']) ?></td>
    <td><?= htmlspecialchars($row['student_name']) ?></td>
    <td><?= htmlspecialchars($row['dept_name']) ?></td>
    <td><?= htmlspecialchars($row['program_name']) ?></td>
    <td><?= htmlspecialchars($row['session_name']) ?></td>
    <td><?= $row['semester'] ?></td>
    <td>
        <?php if ($row['student_image']): ?>
            <img src="../uploads/<?= $row['student_image'] ?>" width="50" height="50">
        <?php else: ?>
            N/A
        <?php endif; ?>
    </td>
    <td>
        <a href="students.php?edit=<?= $row['student_id'] ?>">Edit</a> |
        <a href="students.php?delete=<?= $row['student_id'] ?>"
           onclick="return confirm('Delete this student?')">Delete</a>
    </td>
</tr>
<?php } ?>
</table>

</body>
</html>
