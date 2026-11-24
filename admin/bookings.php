<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'], $_POST['new_status'])) {
    $booking_id = (int)$_POST['booking_id'];
    $new_status = $_POST['new_status'];

    if (!in_array($new_status, ['Confirmed', 'Cancelled'], true)) {
        $error = "Invalid booking status.";
    } else {
        $bStmt = $pdo->prepare("SELECT * FROM bookings WHERE booking_id = :booking_id");
        $bStmt->execute(['booking_id' => $booking_id]);
        $booking = $bStmt->fetch();

        if (!$booking) {
            $error = "Booking not found.";
        } elseif ($new_status === 'Confirmed') {
            $overlapStmt = $pdo->prepare("
                SELECT COUNT(*) FROM bookings
                WHERE room_id = :room_id
                  AND booking_id != :booking_id
                  AND booking_status = 'Confirmed'
                  AND check_in < :check_out
                  AND check_out > :check_in
            ");
            $overlapStmt->execute([
                'room_id' => $booking['room_id'],
                'booking_id' => $booking_id,
                'check_in' => $booking['check_in'],
                'check_out' => $booking['check_out']
            ]);
            if ((int)$overlapStmt->fetchColumn() > 0) {
                $error = "Cannot confirm: another Confirmed booking overlaps these dates for this room.";
            } else {
                $upd = $pdo->prepare("UPDATE bookings SET booking_status = 'Confirmed' WHERE booking_id = :booking_id");
                $upd->execute(['booking_id' => $booking_id]);
                $message = "Booking #" . $booking_id . " confirmed.";
            }
        } else {
            $upd = $pdo->prepare("UPDATE bookings SET booking_status = 'Cancelled' WHERE booking_id = :booking_id");
            $upd->execute(['booking_id' => $booking_id]);
            $message = "Booking #" . $booking_id . " cancelled.";
        }
    }
}

$stmt = $pdo->query("
    SELECT b.*, u.name AS guest_name, u.email AS guest_email, u.contact_number, r.room_name
    FROM bookings b
    JOIN users u ON b.user_id = u.user_id
    JOIN rooms r ON b.room_id = r.room_id
    ORDER BY b.booking_date DESC
");
$bookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - Admin</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f4f8fa; color: #222; }
        header { background: #083b52; color: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        header a { color: #fff; text-decoration: none; margin-left: 16px; font-weight: bold; }
        .container { max-width: 1150px; margin: 30px auto; padding: 0 20px; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 3px 10px rgba(0,0,0,0.07); }
        th, td { padding: 11px; border-bottom: 1px solid #e5e7eb; text-align: left; font-size: 14px; }
        th { background: #0b4f6c; color: #fff; }
        .badge { padding: 4px 9px; border-radius: 12px; font-size: 12px; font-weight: bold; }
        .Pending { background: #fef3c7; color: #92400e; }
        .Confirmed { background: #d1fae5; color: #065f46; }
        .Cancelled { background: #fee2e2; color: #991b1b; }
        .btn-ok { background: #059669; color: #fff; border: none; padding: 6px 10px; border-radius: 5px; cursor: pointer; }
        .btn-no { background: #dc2626; color: #fff; border: none; padding: 6px 10px; border-radius: 5px; cursor: pointer; }
        .msg { background: #e3f9e5; color: #155724; padding: 10px; border-radius: 6px; margin-bottom: 15px; }
        .err { background: #ffe5e5; color: #990000; padding: 10px; border-radius: 6px; margin-bottom: 15px; }
    </style>
</head>
<body>

<header>
    <div><strong>Manage Bookings — Admin</strong></div>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="rooms.php">Rooms</a>
        <a href="bookings.php">Bookings</a>
        <a href="../logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <h2>All Guest Reservations</h2>

    <?php if ($message !== ''): ?><div class="msg"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
    <?php if ($error !== ''): ?><div class="err"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Guest</th>
                <th>Room</th>
                <th>Dates</th>
                <th>Guests</th>
                <th>Total (LKR)</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bookings as $b): ?>
                <tr>
                    <td>#<?php echo (int)$b['booking_id']; ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($b['guest_name']); ?></strong><br>
                        <small><?php echo htmlspecialchars($b['guest_email']); ?> | <?php echo htmlspecialchars($b['contact_number']); ?></small>
                    </td>
                    <td><?php echo htmlspecialchars($b['room_name']); ?></td>
                    <td><?php echo htmlspecialchars($b['check_in']); ?> to <?php echo htmlspecialchars($b['check_out']); ?></td>
                    <td><?php echo (int)$b['guests_count']; ?></td>
                    <td><?php echo number_format($b['total_amount'], 2); ?></td>
                    <td><span class="badge <?php echo htmlspecialchars($b['booking_status']); ?>"><?php echo htmlspecialchars($b['booking_status']); ?></span></td>
                    <td>
                        <?php if ($b['booking_status'] !== 'Confirmed'): ?>
                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="booking_id" value="<?php echo (int)$b['booking_id']; ?>">
                                <input type="hidden" name="new_status" value="Confirmed">
                                <button type="submit" class="btn-ok">Confirm</button>
                            </form>
                        <?php endif; ?>
                        <?php if ($b['booking_status'] !== 'Cancelled'): ?>
                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="booking_id" value="<?php echo (int)$b['booking_id']; ?>">
                                <input type="hidden" name="new_status" value="Cancelled">
                                <button type="submit" class="btn-no">Cancel</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>