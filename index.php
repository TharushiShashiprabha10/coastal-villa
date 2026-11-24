<?php
session_start();
require_once 'config/db.php';

$stmt = $pdo->query("SELECT * FROM rooms WHERE room_status = 'Available' LIMIT 3");
$featuredRooms = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Serene Coast Villa - Mirissa, Sri Lanka</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f4f8fa; color: #222; }
        header { background: #0b4f6c; color: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; }
        header h1 { margin: 0; font-size: 22px; }
        nav a { color: #fff; text-decoration: none; margin-left: 18px; font-weight: bold; }
        nav a:hover { text-decoration: underline; }
        .hero { background: linear-gradient(rgba(11,79,108,0.75), rgba(11,79,108,0.75)), url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1400&q=80') center/cover; color: #fff; text-align: center; padding: 80px 20px; }
        .hero h2 { font-size: 36px; margin-bottom: 10px; }
        .hero p { max-width: 650px; margin: 0 auto 25px; font-size: 17px; line-height: 1.5; }
        .btn { display: inline-block; background: #f59e0b; color: #fff; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: bold; }
        .btn:hover { background: #d97706; }
        .container { max-width: 1100px; margin: 35px auto; padding: 0 20px; }
        .facilities { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin-bottom: 40px; }
        .card { background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 3px 10px rgba(0,0,0,0.07); }
        .rooms-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .price { color: #0b4f6c; font-weight: bold; font-size: 18px; margin: 10px 0; }
        footer { background: #083b52; color: #fff; text-align: center; padding: 18px; margin-top: 50px; }
    </style>
</head>
<body>

<header>
    <h1>Serene Coast Villa — Mirissa</h1>
    <nav>
        <a href="index.php">Home</a>
        <a href="rooms.php">Rooms & Availability</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="admin/dashboard.php">Admin Panel</a>
            <?php else: ?>
                <a href="my_bookings.php">My Bookings</a>
            <?php endif; ?>
            <a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['name']); ?>)</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </nav>
</header>

<section class="hero">
    <h2>Relax by the Southern Coast of Sri Lanka</h2>
    <p>Located minutes from Mirissa & Weligama beaches, Serene Coast Villa offers ocean-view suites, tropical garden rooms, and personalized Sri Lankan hospitality.</p>
    <a href="rooms.php" class="btn">Book Now</a>
</section>

<div class="container">
    <h3>Villa Highlights & Facilities</h3>
    <div class="facilities">
        <div class="card"><strong>Beachfront Access</strong><p>2-minute walk to calm swimming and surfing bays.</p></div>
        <div class="card"><strong>Free High-Speed Wi-Fi</strong><p>Reliable fiber internet across all rooms and garden decks.</p></div>
        <div class="card"><strong>Local & Continental Dining</strong><p>Fresh seafood and Sri Lankan breakfast served daily.</p></div>
        <div class="card"><strong>Airport Transfers & Tours</strong><p>Whale watching, Galle Fort trips, and expressway pickups.</p></div>
    </div>

    <h3>Featured Rooms</h3>
    <div class="rooms-grid">
        <?php foreach ($featuredRooms as $room): ?>
            <div class="card">
                <h4><?php echo htmlspecialchars($room['room_name']); ?></h4>
                <p><strong>Type:</strong> <?php echo htmlspecialchars($room['room_type']); ?> | <strong>Max Guests:</strong> <?php echo (int)$room['max_guests']; ?></p>
                <p><?php echo htmlspecialchars($room['description']); ?></p>
                <p><small><strong>Facilities:</strong> <?php echo htmlspecialchars($room['facilities']); ?></small></p>
                <div class="price">LKR <?php echo number_format($room['price_per_night'], 2); ?> / night</div>
                <a href="booking.php?room_id=<?php echo (int)$room['room_id']; ?>" class="btn">Book This Room</a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<footer>
    &copy; <?php echo date('Y'); ?> Serene Coast Villa, Mirissa, Sri Lanka. All Rights Reserved.
</footer>

</body>
</html>