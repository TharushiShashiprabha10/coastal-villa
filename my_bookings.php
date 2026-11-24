<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking_id'])) {
    $booking_id = (int)$_POST['cancel_booking_id'];

    $stmt = $pdo->prepare("
        UPDATE bookings
        SET booking_status = 'Cancelled'
        WHERE booking_id = :booking_id
          AND user_id = :user_id
          AND booking_status = 'Pending'
    ");
    $stmt->execute([
        'booking_id' => $booking_id,
        'user_id' => $user_id
    ]);

    if ($stmt->rowCount() > 0) {
        $message = "Booking #" . $booking_id . " has been cancelled.";
    } else {
        $error = "Only your own Pending bookings can be cancelled.";
    }
}

$listStmt = $pdo->prepare("
    SELECT b.*, r.room_name, r.room_type
    FROM bookings b
    JOIN rooms r ON b.room_id = r.room_id
    WHERE b.user_id = :user_id
    ORDER BY b.booking_date DESC
");
$listStmt->execute(['user_id' => $user_id]);
$bookings = $listStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Serene Coast Villa</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f4f8fa; color: #222; }
        header { background: #0b4f6c; color: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        header a { color: #fff; text-decoration: none; margin-left: 16px; font-weight: bold; }
        .container { max-width: 1050px; margin: 30px auto; padding: 0 20px; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 3px 10px rgba(0,0,0,0.07); }
        th, td { padding: 12px 14px; border-bottom: 1px solid #e5e7eb; text-align: left; }
        th { background: #0b4f6c; color: #fff; }
        .badge { padding: 4px 10px; border-radius: 12px; font-size: 13px; font-weight: bold; }
        .Pending { background: #fef3c7; color: #92400e; }
        .Confirmed { background: #d1fae5; color: #065f46; }
        .Cancelled { background: #fee2e2; color: #991b1b; }
        button { background: #dc2626; color: #fff; border: none; padding: 6px 12px; border-radius: 5px; cursor: pointer; }
        .msg { background: #e3f9e5; color: #155724; padding: 10px; border-radius: 6px; margin-bottom: 15px; }
        .err { background: #ffe5e5; color: #990000; padding: 10px; border-radius: 6px; margin-bottom: 15px; }
    </style>
</head>
<body>

<header>
    <div><strong>Serene Coast Villa</strong></div>
    <nav>
        <a href="index.php">Home</a>
        <a href="rooms.php">Rooms</a>
        <a href="booking.php">New Booking</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <h2>My Bookings</h2>

    <?php if ($message !== ''): ?><div class="msg"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
    <?php if ($error !== ''): ?><div class="err"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Room</th>
                <th>Guests</th>
                <th>Check-in</th>
                <th>Check-out</th>
                <th>Total (LKR)</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($bookings)): ?>
                <tr><td colspan="8">No bookings found yet. <a href="rooms.php">Browse rooms</a></td></tr>
            <?php else: ?>
                <?php foreach ($bookings as $b): ?>
                    <tr>
                        <td>#<?php echo (int)$b['booking_id']; ?></td>
                        <td><?php echo htmlspecialchars($b['room_name']); ?> (<?php echo htmlspecialchars($b['room_type']); ?>)</td>
                        <td><?php echo (int)$b['guests_count']; ?></td>
                        <td><?php echo htmlspecialchars($b['check_in']); ?></td>
                        <td><?php echo htmlspecialchars($b['check_out']); ?></td>
                        <td><?php echo number_format($b['total_amount'], 2); ?></td>
                        <td><span class="badge <?php echo htmlspecialchars($b['booking_status']); ?>"><?php echo htmlspecialchars($b['booking_status']); ?></span></td>
                        <td>
                            <?php if ($b['booking_status'] === 'Pending'): ?>
                                <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Cancel this pending booking?');">
                                    <input type="hidden" name="cancel_booking_id" value="<?php echo (int)$b['booking_id']; ?>">
                                    <button type="submit">Cancel</button>
                                </form>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>