<?php
session_start();
include "../config/db.php";

if(isset($_POST['username']) && isset($_POST['password'])) {

    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    
    $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows == 1){
        $admin = $result->fetch_assoc();

        
        if(password_verify($password, $admin['password'])){
            session_regenerate_id(true); 
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['username'];
            header("Location: ../admin/dashboard.php");
            exit;
        } else {
            echo "Incorrect password";
        }
    } else {
        echo "Admin not found";
    }

} else {
    header("Location: login.php");
    exit;
}
?>

