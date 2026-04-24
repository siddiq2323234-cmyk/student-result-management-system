<?php
session_start();
if(!isset($_SESSION['admin_id'])){
    header("Location: ../auth/login.php");
    exit;
}

include "../config/db.php";

// Fetch students for dropdown
$students = mysqli_query($conn, "SELECT * FROM students");

$error = $success = "";

// ----- SAVE MARKS -----
if(isset($_POST['save_marks'])){
    $student_id = intval($_POST['student_id']);
    foreach($_POST['marks'] as $subject_id => $marks){
        $mid = intval($marks['midterm']);
        $final = intval($marks['final']);
        $sessional = intval($marks['sessional']);
        $total = $mid + $final + $sessional;

        // Calculate GPA and Grade
        if($total >= 90){ $gpa = 4.0; $grade = 'A+'; }
        elseif($total >=80){ $gpa = 3.7; $grade = 'A'; }
        elseif($total >=70){ $gpa = 3.0; $grade = 'B'; }
        elseif($total >=60){ $gpa = 2.0; $grade = 'C'; }
        elseif($total >=50){ $gpa = 1.0; $grade = 'D'; }
        else { $gpa = 0.0; $grade = 'F'; }

        // Insert or Update marks with GPA and grade
        $check = mysqli_query($conn, "SELECT * FROM marks WHERE student_id=$student_id AND subject_id=$subject_id");
        if(mysqli_num_rows($check) > 0){
            mysqli_query($conn, "UPDATE marks 
                SET midterm_marks=$mid, final_marks=$final, sessional_marks=$sessional, 
                    total_marks=$total, gpa=$gpa, grade='$grade' 
                WHERE student_id=$student_id AND subject_id=$subject_id
            ");
        } else {
            mysqli_query($conn, "INSERT INTO marks(student_id, subject_id, midterm_marks, final_marks, sessional_marks, total_marks, gpa, grade)
                VALUES($student_id, $subject_id, $mid, $final, $sessional, $total, $gpa, '$grade')
            ");
        }
    }
    $success = "Marks saved successfully!";
}

// ----- FETCH SUBJECTS FOR SELECTED STUDENT -----
$selected_student_id = isset($_POST['student_id']) ? intval($_POST['student_id']) : 0;
$subjects = [];
if($selected_student_id){
    $res = mysqli_query($conn, "
        SELECT su.*
        FROM subjects su
        JOIN students st ON st.program_id = su.program_id
        WHERE st.student_id = $selected_student_id
    ");
    while($row = mysqli_fetch_assoc($res)){
        $subjects[] = $row;
    }
}

// ----- FETCH EXISTING MARKS FOR PREFILL -----
$existing_marks = [];
if($selected_student_id){
    $res_marks = mysqli_query($conn, "SELECT * FROM marks WHERE student_id=$selected_student_id");
    while($row = mysqli_fetch_assoc($res_marks)){
        $existing_marks[$row['subject_id']] = $row;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Marks Entry</title>
</head>
<body>
<h2>Marks Entry</h2>
<a href="dashboard.php">Dashboard</a> | <a href="../auth/logout.php">Logout</a>

<?php if($error) echo "<p style='color:red;'>$error</p>"; ?>
<?php if($success) echo "<p style='color:green;'>$success</p>"; ?>

<!-- STUDENT SELECTION -->
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

<!-- MARKS ENTRY TABLE -->
<?php if($selected_student_id && $subjects){ ?>
<form method="POST">
    <input type="hidden" name="student_id" value="<?= $selected_student_id ?>">
    <table border="1" cellpadding="5">
        <tr>
            <th>Subject</th>
            <th>Credit Hours</th>
            <th>Midterm</th>
            <th>Final</th>
            <th>Sessional</th>
            <th>Total</th>
            <th>GPA</th>
            <th>Grade</th>
        </tr>
        <?php foreach($subjects as $sub){ 
            $mid = $existing_marks[$sub['subject_id']]['midterm_marks'] ?? 0;
            $final = $existing_marks[$sub['subject_id']]['final_marks'] ?? 0;
            $sessional = $existing_marks[$sub['subject_id']]['sessional_marks'] ?? 0;
            $total = $mid + $final + $sessional;

            // GPA calculation
            if($total >= 90){ $gpa = 4.0; $grade = 'A+'; }
            elseif($total >=80){ $gpa = 3.7; $grade = 'A'; }
            elseif($total >=70){ $gpa = 3.0; $grade = 'B'; }
            elseif($total >=60){ $gpa = 2.0; $grade = 'C'; }
            elseif($total >=50){ $gpa = 1.0; $grade = 'D'; }
            else { $gpa = 0.0; $grade = 'F'; }
        ?>
        <tr>
            <td><?= $sub['subject_name'] ?></td>
            <td><?= $sub['credit_hours'] ?></td>
            <td><input type="number" name="marks[<?= $sub['subject_id'] ?>][midterm]" value="<?= $mid ?>" max="100"></td>
            <td><input type="number" name="marks[<?= $sub['subject_id'] ?>][final]" value="<?= $final ?>" max="100"></td>
            <td><input type="number" name="marks[<?= $sub['subject_id'] ?>][sessional]" value="<?= $sessional ?>" max="100"></td>
            <td><?= $total ?></td>
            <td><?= $gpa ?></td>
            <td><?= $grade ?></td>
        </tr>
        <?php } ?>
    </table>
    <input type="submit" name="save_marks" value="Save Marks">
</form>
<?php } ?>
</body>
</html>
