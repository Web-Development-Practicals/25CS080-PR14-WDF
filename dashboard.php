<?php
session_start();
require_once 'db.php';

// Quick Role switcher simulator for lab demonstration
if (isset($_GET['role'])) {
    $_SESSION['role'] = $_GET['role'];
    $_SESSION['username'] = ($_GET['role'] === 'admin') ? 'Admin User' : '25CS080';
    $_SESSION['user_id'] = ($_GET['role'] === 'admin') ? 1 : 2;

    // Log admin login to audit_logs
    if ($_SESSION['role'] === 'admin') {
        $logStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action) VALUES (?, 'Admin accessed dashboard')");
        $logStmt->execute([$_SESSION['user_id']]);
    }
}

// Defaults to admin if not set
$current_role = $_SESSION['role'] ?? 'admin';
$current_user = $_SESSION['username'] ?? 'Admin User';

// 1. Dynamic Statistics Queries
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalStudents = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$totalEvents = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();

// 2. Latest Registrations Query
$recentUsers = $pdo->query("SELECT username, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();

// 3. Dynamic Menu Query according to Role (RBAC)
if ($current_role === 'admin') {
    $menuStmt = $pdo->prepare("SELECT title, link FROM menu_items WHERE min_role IN ('student', 'admin')");
    $menuStmt->execute();
} else {
    $menuStmt = $pdo->prepare("SELECT title, link FROM menu_items WHERE min_role = 'student'");
    $menuStmt->execute();
}
$menuItems = $menuStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Practical 14 - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">Portal Dashboard</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto">
                <?php foreach ($menuItems as $item): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo htmlspecialchars($item['link']); ?>"><?php echo htmlspecialchars($item['title']); ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="d-flex align-items-center text-white gap-2">
                <span>Role: <strong><?php echo ucfirst($current_role); ?></strong></span>
                <a href="dashboard.php?role=admin" class="btn btn-sm btn-outline-light">Switch to Admin</a>
                <a href="dashboard.php?role=student" class="btn btn-sm btn-outline-warning">Switch to Student</a>
                <a href="admin_only.php" class="btn btn-sm btn-danger">Admin Page Test</a>
            </div>
        </div>
    </div>
</nav>

<div class="container my-4">
    <h2 class="mb-4">Welcome, <?php echo htmlspecialchars($current_user); ?></h2>

    <!-- Dynamic Metric Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Total Users</h5>
                    <h2 class="card-text"><?php echo $totalUsers; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Total Students</h5>
                    <h2 class="card-text"><?php echo $totalStudents; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-info shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Total Events</h5>
                    <h2 class="card-text"><?php echo $totalEvents; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Latest Registrations Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Latest Registrations</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Registered Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentUsers as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($user['role']); ?></span></td>
                            <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>