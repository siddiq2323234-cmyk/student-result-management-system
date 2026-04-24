<?php
session_start();
if(!isset($_SESSION['admin_id'])){
    header("Location: ../auth/login.php");
    exit;
}

include "../config/db.php";

// Fetch all students
$students = mysqli_query($conn, "SELECT * FROM students");

$selected_student_id = isset($_POST['student_id']) ? intval($_POST['student_id']) : 0;

// Fetch student info and subjects with marks
$student_info = [];
$subjects = [];

if($selected_student_id){
    // Student info
    $res_student = mysqli_query($conn, "SELECT * FROM students WHERE student_id=$selected_student_id");
    if($res_student) $student_info = mysqli_fetch_assoc($res_student);

    // Subjects and marks
    $res_subjects = mysqli_query($conn, "
        SELECT su.subject_name, su.credit_hours, m.midterm_marks, m.final_marks, m.sessional_marks, m.total_marks
        FROM subjects su
        JOIN marks m ON m.subject_id = su.subject_id
        WHERE m.student_id = $selected_student_id
    ");
    while($row = mysqli_fetch_assoc($res_subjects)){
        $subjects[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Student Semester Results</title>
</head>
<body>
<h2>Student Semester Results</h2>
<a href="dashboard.php">Dashboard</a> | <a href="../auth/logout.php">Logout</a>

<!-- Select Student -->
<form method="POST">
    <select name="student_id" onchange="this.form.submit()">
        <option value="">Select Student</option>
        <?php while($stu = mysqli_fetch_assoc($students)){ ?>
            <option value="<?= $stu['student_id'] ?>" <?= ($stu['student_id'] == $selected_student_id) ? 'selected' : '' ?>>
                <?= $stu['student_name'] ?> (<?= $stu['roll_number'] ?>)
            </option>
        <?php } ?>
    </select>
</form>

<?php if($selected_student_id && $subjects){ ?>
    <h3>Results for <?= $student_info['student_name'] ?> (Roll #: <?= $student_info['roll_number'] ?>)</h3>
    <table border="1" cellpadding="5">
        <tr>
            <th>Subject</th>
            <th>Credit Hours</th>
            <th>Midterm</th>
            <th>Final</th>
            <th>Sessional</th>
            <th>Total</th>
        </tr>
        <?php
        $semester_total = 0;
        foreach($subjects as $sub){
            $semester_total += $sub['total_marks'];
        ?>
        <tr>
            <td><?= $sub['subject_name'] ?></td>
            <td><?= $sub['credit_hours'] ?></td>
            <td><?= $sub['midterm_marks'] ?></td>
            <td><?= $sub['final_marks'] ?></td>
            <td><?= $sub['sessional_marks'] ?></td>
            <td><?= $sub['total_marks'] ?></td>
        </tr>
        <?php } ?>
        <tr>
            <td colspan="5"><strong>Semester Total</strong></td>
            <td><strong><?= $semester_total ?></strong></td>
        </tr>
    </table>
<?php } elseif($selected_student_id){ ?>
    <p>No marks found for this student yet.</p>
<?php } ?>
</body>
</html>
