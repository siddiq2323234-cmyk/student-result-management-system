<?php
session_start();
include "../config/db.php"; // your database connection

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$message = "";

// Handle form submission
if (isset($_POST['update_profile'])) {
    $new_username = trim($_POST['username']);
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    
    // Fetch current password from DB
    $stmt = $conn->prepare("SELECT password FROM admin  WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['admin_id']);
    $stmt->execute();
    $stmt->bind_result($hashed_password);
    $stmt->fetch();
    $stmt->close();

    // Verify current password
    if (password_verify($current_password, $hashed_password)) {
        // Hash new password
        $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);

        // Update username and password
        $stmt = $conn->prepare("UPDATE admin SET username = ?, password = ? WHERE id = ?");
        $stmt->bind_param("ssi", $new_username, $new_hashed, $_SESSION['admin_id']);
        $stmt->execute();
        $stmt->close();

        $_SESSION['admin_name'] = $new_username; // update session
        $message = "Profile updated successfully!";
    } else {
        $message = "Current password is incorrect!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Settings</title>
    <style>
        body { font-family: Arial; padding: 20px; background-color: #f2f2f2; }
        form { background: #fff; padding: 20px; border-radius: 8px; max-width: 400px; }
        label { display: block; margin: 10px 0 5px; }
        input { width: 100%; padding: 8px; margin-bottom: 10px; }
        button { padding: 10px 20px; background: #2c3e50; color: #fff; border: none; cursor: pointer; }
        button:hover { background: #1abc9c; }
        p { color: green; }
    </style>
</head>
<body>

<h2>Admin Settings</h2>
<?php if ($message) echo "<p>$message</p>"; ?>

<form method="POST">
    <label>Username:</label>
    <input type="text" name="username" value="<?php echo htmlspecialchars($_SESSION['admin_name']); ?>" required>

    <label>Current Password:</label>
    <input type="password" name="current_password" required>

    <label>New Password:</label>
    <input type="password" name="new_password" required>

    <button type="submit" name="update_profile">Update Profile</button>
</form>

</body>
</html>
