<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$selected_room_id = (int)($_GET['room_id'] ?? ($_POST['room_id'] ?? 0));
$check_in_val = $_GET['check_in'] ?? ($_POST['check_in'] ?? '');
$check_out_val = $_GET['check_out'] ?? ($_POST['check_out'] ?? '');
$guests_val = (int)($_GET['guests'] ?? ($_POST['guests_count'] ?? 1));

$roomsStmt = $pdo->query("SELECT * FROM rooms WHERE room_status = 'Available' ORDER BY room_name ASC");
$rooms = $roomsStmt->fetchAll();

$userStmt = $pdo->prepare("SELECT name, email, contact_number FROM users WHERE user_id = :user_id");
$userStmt->execute(['user_id' => $user_id]);
$currentUser = $userStmt->fetch();

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id = (int)($_POST['room_id'] ?? 0);
    $guests_count = (int)($_POST['guests_count'] ?? 1);
    $check_in = $_POST['check_in'] ?? '';
    $check_out = $_POST['check_out'] ?? '';
    $today = date('Y-m-d');

    $roomCheck = $pdo->prepare("SELECT * FROM rooms WHERE room_id = :room_id AND room_status = 'Available'");
    $roomCheck->execute(['room_id' => $room_id]);
    $room = $roomCheck->fetch();

    if (!$room) {
        $errors[] = "Selected room is not available.";
    }
    if ($check_in === '' || $check_out === '') {
        $errors[] = "Please select both check-in and check-out dates.";
    } elseif ($check_in < $today) {
        $errors[] = "Check-in date cannot be in the past.";
    } elseif ($check_out <= $check_in) {
        $errors[] = "Check-out date must be after check-in date.";
    }

    if ($guests_count < 1) {
        $errors[] = "Number of guests must be at least 1.";
    } elseif ($room && $guests_count > (int)$room['max_guests']) {
        $errors[] = "Maximum guests allowed for this room is " . (int)$room['max_guests'] . ".";
    }

    if (empty($errors) && $room) {
        $overlapStmt = $pdo->prepare("
            SELECT COUNT(*) FROM bookings
            WHERE room_id = :room_id
              AND booking_status = 'Confirmed'
              AND check_in < :check_out
              AND check_out > :check_in
        ");
        $overlapStmt->execute([
            'room_id' => $room_id,
            'check_in' => $check_in,
            'check_out' => $check_out
        ]);
        $overlapCount = (int)$overlapStmt->fetchColumn();

        if ($overlapCount > 0) {
            $errors[] = "This room already has a confirmed booking for the selected dates.";
        } else {
            $d1 = new DateTime($check_in);
            $d2 = new DateTime($check_out);
            $nights = (int)$d1->diff($d2)->days;
            $total_amount = $nights * (float)$room['price_per_night'];

            $insertStmt = $pdo->prepare("
                INSERT INTO bookings (user_id, room_id, guests_count, check_in, check_out, total_amount, booking_status)
                VALUES (:user_id, :room_id, :guests_count, :check_in, :check_out, :total_amount, 'Pending')
            ");
            $insertStmt->execute([
                'user_id' => $user_id,
                'room_id' => $room_id,
                'guests_count' => $guests_count,
                'check_in' => $check_in,
                'check_out' => $check_out,
                'total_amount' => $total_amount
            ]);
            $success = "Booking request submitted successfully! Status: Pending (" . $nights . " night(s), Total: LKR " . number_format($total_amount, 2) . ").";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Room - Serene Coast Villa</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f4f8fa; color: #222; }
        header { background: #0b4f6c; color: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        header a { color: #fff; text-decoration: none; margin-left: 16px; font-weight: bold; }
        .box { max-width: 560px; margin: 35px auto; background: #fff; padding: 25px; border-radius: 10px; box-shadow: 0 3px 12px rgba(0,0,0,0.08); }
        label { display: block; font-weight: bold; margin-top: 12px; }
        input, select { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; }
        .summary { background: #eef6fa; padding: 14px; border-radius: 8px; margin-top: 16px; }
        button { width: 100%; background: #f59e0b; color: #fff; border: none; padding: 12px; margin-top: 18px; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; }
        .error { background: #ffe5e5; color: #990000; padding: 10px; border-radius: 6px; margin-bottom: 12px; }
        .success { background: #e3f9e5; color: #155724; padding: 10px; border-radius: 6px; margin-bottom: 12px; }
    </style>
</head>
<body>

<header>
    <div><strong>Serene Coast Villa</strong></div>
    <nav>
        <a href="index.php">Home</a>
        <a href="rooms.php">Rooms</a>
        <a href="my_bookings.php">My Bookings</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="box">
    <h2>Room Booking Request</h2>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $err): ?>
                <div>• <?php echo htmlspecialchars($err); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($success !== ''): ?>
        <div class="success">
            <?php echo htmlspecialchars($success); ?>
            <br><a href="my_bookings.php">View My Bookings</a>
        </div>
    <?php endif; ?>

    <form method="POST" action="" id="bookingForm">
        <label>Guest Name</label>
        <input type="text" value="<?php echo htmlspecialchars($currentUser['name'] ?? ''); ?>" readonly>

        <label>Email</label>
        <input type="email" value="<?php echo htmlspecialchars($currentUser['email'] ?? ''); ?>" readonly>

        <label>Contact Number</label>
        <input type="text" value="<?php echo htmlspecialchars($currentUser['contact_number'] ?? ''); ?>" readonly>

        <label>Select Room</label>
        <select name="room_id" id="roomSelect" required>
            <option value="">-- Choose a Room --</option>
            <?php foreach ($rooms as $r): ?>
                <option value="<?php echo (int)$r['room_id']; ?>"
                        data-price="<?php echo (float)$r['price_per_night']; ?>"
                        data-max="<?php echo (int)$r['max_guests']; ?>"
                        <?php echo ($selected_room_id === (int)$r['room_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($r['room_name']); ?> (LKR <?php echo number_format($r['price_per_night'], 2); ?>/night, Max <?php echo (int)$r['max_guests']; ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <label>Number of Guests</label>
        <input type="number" name="guests_count" id="guestsInput" min="1" value="<?php echo max(1, $guests_val); ?>" required>

        <label>Check-in Date</label>
        <input type="date" name="check_in" id="checkIn" min="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($check_in_val); ?>" required>

        <label>Check-out Date</label>
        <input type="date" name="check_out" id="checkOut" min="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($check_out_val); ?>" required>

        <div class="summary" id="liveSummary">
            <div><strong>Nights:</strong> <span id="nightsCount">0</span></div>
            <div><strong>Estimated Total:</strong> LKR <span id="totalCost">0.00</span></div>
            <div id="jsMsg" style="color:#b91c1c; margin-top:6px;"></div>
        </div>

        <button type="submit">Submit Booking Request</button>
    </form>
</div>

<script>
const roomSelect = document.getElementById('roomSelect');
const checkIn = document.getElementById('checkIn');
const checkOut = document.getElementById('checkOut');
const guestsInput = document.getElementById('guestsInput');
const nightsCount = document.getElementById('nightsCount');
const totalCost = document.getElementById('totalCost');
const jsMsg = document.getElementById('jsMsg');
const form = document.getElementById('bookingForm');

function calculateEstimate() {
    jsMsg.textContent = '';
    const selectedOption = roomSelect.options[roomSelect.selectedIndex];
    const price = selectedOption ? parseFloat(selectedOption.getAttribute('data-price') || '0') : 0;
    const maxGuests = selectedOption ? parseInt(selectedOption.getAttribute('data-max') || '0', 10) : 0;
    const guests = parseInt(guestsInput.value || '0', 10);

    if (maxGuests > 0 && guests > maxGuests) {
        jsMsg.textContent = 'Selected room allows up to ' + maxGuests + ' guests.';
    }

    if (!checkIn.value || !checkOut.value) {
        nightsCount.textContent = '0';
        totalCost.textContent = '0.00';
        return;
    }

    const start = new Date(checkIn.value + 'T00:00:00');
    const end = new Date(checkOut.value + 'T00:00:00');
    const diffDays = Math.round((end - start) / (1000 * 60 * 60 * 24));

    if (diffDays <= 0) {
        jsMsg.textContent = 'Check-out date must be after check-in date.';
        nightsCount.textContent = '0';
        totalCost.textContent = '0.00';
        return;
    }

    nightsCount.textContent = diffDays;
    totalCost.textContent = (diffDays * price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

roomSelect.addEventListener('change', calculateEstimate);
checkIn.addEventListener('change', calculateEstimate);
checkOut.addEventListener('change', calculateEstimate);
guestsInput.addEventListener('input', calculateEstimate);

form.addEventListener('submit', function(e) {
    calculateEstimate();
    if (jsMsg.textContent !== '') {
        e.preventDefault();
    }
});

calculateEstimate();
</script>

</body>
</html>