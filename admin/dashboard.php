<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$totalRooms = (int)$pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
$totalBookings = (int)$pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$pendingBookings = (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE booking_status = 'Pending'")->fetchColumn();
$confirmedBookings = (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE booking_status = 'Confirmed'")->fetchColumn();
$revenue = (float)$pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM bookings WHERE booking_status = 'Confirmed'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Serene Coast Villa</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f4f8fa; color: #222; }
        header { background: #083b52; color: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; }
        header a { color: #fff; text-decoration: none; margin-left: 16px; font-weight: bold; }
        .container { max-width: 1100px; margin: 30px auto; padding: 0 20px; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 18px; margin-top: 20px; }
        .stat-card { background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 3px 10px rgba(0,0,0,0.07); }
        .stat-num { font-size: 26px; font-weight: bold; color: #0b4f6c; margin-top: 8px; }
        .actions { margin-top: 30px; display: flex; gap: 15px; flex-wrap: wrap; }
        .btn { background: #0b4f6c; color: #fff; padding: 12px 20px; border-radius: 6px; text-decoration: none; font-weight: bold; }
        .btn-amber { background: #f59e0b; }
    </style>
</head>
<body>

<header>
    <div><strong>Admin Panel — Serene Coast Villa</strong></div>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="rooms.php">Manage Rooms</a>
        <a href="bookings.php">Manage Bookings</a>
        <a href="../index.php">View Site</a>
        <a href="../logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <h2>Administrator Dashboard</h2>
    <p>Welcome, <?php echo htmlspecialchars($_SESSION['name'] ?? 'Admin'); ?>.</p>

    <div class="stats">
        <div class="stat-card"><div>Total Rooms</div><div class="stat-num"><?php echo $totalRooms; ?></div></div>
        <div class="stat-card"><div>Total Bookings</div><div class="stat-num"><?php echo $totalBookings; ?></div></div>
        <div class="stat-card"><div>Pending Requests</div><div class="stat-num"><?php echo $pendingBookings; ?></div></div>
        <div class="stat-card"><div>Confirmed Bookings</div><div class="stat-num"><?php echo $confirmedBookings; ?></div></div>
        <div class="stat-card"><div>Confirmed Revenue</div><div class="stat-num">LKR <?php echo number_format($revenue, 0); ?></div></div>
    </div>

    <div class="actions">
        <a href="rooms.php" class="btn">Add / Edit Rooms</a>
        <a href="bookings.php" class="btn btn-amber">Confirm / Cancel Bookings</a>
    </div>
</div>

</body>
</html>