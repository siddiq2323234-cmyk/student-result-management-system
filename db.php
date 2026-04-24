<?php
$conn = mysqli_connect("localhost", "root", "", "student_result_system");

if(!$conn){
    die("DB Connection Failed: " . mysqli_connect_error());
}
?>