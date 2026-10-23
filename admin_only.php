<?php
session_start();

$role = $_SESSION['role'] ?? 'student';

if ($role !== 'admin') {
    http_response_code(403);
    echo "<!DOCTYPE html><html><head><title>403 Forbidden</title></head><body style='font-family: Arial; text-align: center; margin-top: 100px;'>";
    echo "<h1 style='color: red;'>403 Forbidden</h1>";
    echo "<p>Unauthorized Access: Administrative privileges are required to view this resource.</p>";
    echo "<a href='dashboard.php'>Back to Dashboard</a>";
    echo "</body></html>";
    exit;
}
?>
<!DOCTYPE html>
<html>
<head><title>Admin Control Panel</title></head>
<body style="font-family: Arial; padding: 20px;">
    <h2>Admin Restricted Area</h2>
    <p>Welcome, Administrator. You have full access.</p>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>