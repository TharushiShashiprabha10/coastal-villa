<?php
session_start();
require_once 'config/db.php';

$check_in = $_GET['check_in'] ?? '';
$check_out = $_GET['check_out'] ?? '';
$guests = (int)($_GET['guests'] ?? 1);
$error = '';

$params = [];
$sql = "SELECT * FROM rooms WHERE room_status = 'Available'";

if ($guests > 1) {
    $sql .= " AND max_guests >= :guests";
    $params['guests'] = $guests;
}

if ($check_in !== '' && $check_out !== '') {
    if ($check_out <= $check_in) {
        $error = "Check-out date must be after check-in date.";
    } else {
        $sql .= " AND room_id NOT IN (
            SELECT room_id FROM bookings
            WHERE booking_status = 'Confirmed'
              AND check_in < :check_out
              AND check_out > :check_in
        )";
        $params['check_in'] = $check_in;
        $params['check_out'] = $check_out;
    }
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rooms = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rooms & Availability - Serene Coast Villa</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f4f8fa; color: #222; }
        header { background: #0b4f6c; color: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        header a { color: #fff; text-decoration: none; margin-left: 16px; font-weight: bold; }
        .container { max-width: 1100px; margin: 30px auto; padding: 0 20px; }
        .search-box { background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 3px 10px rgba(0,0,0,0.07); margin-bottom: 25px; display: flex; gap: 15px; flex-wrap: wrap; align-items: end; }
        .search-box div { flex: 1; min-width: 180px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input { width: 100%; padding: 9px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; }
        button, .btn { background: #0b4f6c; color: #fff; border: none; padding: 10px 18px; border-radius: 6px; cursor: pointer; text-decoration: none; display: inline-block; font-weight: bold; }
        .btn-book { background: #f59e0b; }
        .rooms-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .card { background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 3px 10px rgba(0,0,0,0.07); }
        .error { background: #ffe5e5; color: #990000; padding: 10px; border-radius: 6px; margin-bottom: 15px; }
    </style>
</head>
<body>

<header>
    <div><strong>Serene Coast Villa</strong></div>
    <nav>
        <a href="index.php">Home</a>
        <a href="rooms.php">Rooms</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="my_bookings.php">My Bookings</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </nav>
</header>

<div class="container">
    <h2>Search Available Rooms</h2>

    <?php if ($error !== ''): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="GET" action="" class="search-box">
        <div>
            <label>Check-in Date</label>
            <input type="date" name="check_in" min="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($check_in); ?>">
        </div>
        <div>
            <label>Check-out Date</label>
            <input type="date" name="check_out" min="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($check_out); ?>">
        </div>
        <div>
            <label>Number of Guests</label>
            <input type="number" name="guests" min="1" max="10" value="<?php echo $guests; ?>">
        </div>
        <div>
            <button type="submit">Check Availability</button>
        </div>
    </form>

    <div class="rooms-grid">
        <?php if (empty($rooms)): ?>
            <p>No available rooms match your selected dates or guest count.</p>
        <?php else: ?>
            <?php foreach ($rooms as $room): ?>
                <div class="card">
                    <h3><?php echo htmlspecialchars($room['room_name']); ?></h3>
                    <p><strong>Type:</strong> <?php echo htmlspecialchars($room['room_type']); ?></p>
                    <p><strong>Max Occupancy:</strong> <?php echo (int)$room['max_guests']; ?> Guests</p>
                    <p><?php echo htmlspecialchars($room['description']); ?></p>
                    <p><small><strong>Facilities:</strong> <?php echo htmlspecialchars($room['facilities']); ?></small></p>
                    <p><strong>Price:</strong> LKR <?php echo number_format($room['price_per_night'], 2); ?> / night</p>
                    <a class="btn btn-book" href="booking.php?room_id=<?php echo (int)$room['room_id']; ?>&check_in=<?php echo urlencode($check_in); ?>&check_out=<?php echo urlencode($check_out); ?>&guests=<?php echo $guests; ?>">Book Now</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>