<?php
session_start();
require '../backend/config.php';
$message = "";

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $sql = "SELECT drivers.id, drivers.password_hash, drivers.full_name, buses.bus_name FROM drivers LEFT JOIN buses ON drivers.bus_id=buses.id WHERE drivers.username='$username'";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) == 1) {
        $driver = mysqli_fetch_assoc($result);
        if (password_verify($password, $driver['password_hash'])) {
            $_SESSION['driver_id'] = $driver['id'];
        } else {
            $message = "Wrong username or password";
        }
    } else {
        $message = "Wrong username or password";
    }
}

if (isset($_POST['logout'])) { 
    session_destroy(); 
    header('Location: index.php'); 
    exit; 
}

$driver = null;
if (isset($_SESSION['driver_id'])) {
    $id = (int)$_SESSION['driver_id'];
    $result = mysqli_query($conn, "SELECT drivers.id, drivers.full_name, buses.bus_name FROM drivers LEFT JOIN buses ON drivers.bus_id=buses.id WHERE drivers.id=$id");
    $driver = mysqli_fetch_assoc($result);
}

if (isset($_POST['stop_trip']) && isset($_SESSION['driver_id'])) {
    $id = (int)$_SESSION['driver_id'];
    mysqli_query($conn, "UPDATE driver_status SET is_active=0, last_seen=NOW() WHERE driver_id=$id");
    $message = "Trip stopped";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>UniRide Driver</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f4f8;
            text-align: center;
            margin: 0;
        }
        .card {
            background: white;
            max-width: 360px;
            margin: 60px auto;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #1a3c6e;
        }
        input, button {
            width: 100%;
            box-sizing: border-box;
            padding: 12px;
            margin: 7px 0;
            font-size: 16px;
        }
        button {
            background: #1a3c6e;
            color: white;
            border: 0;
            border-radius: 6px;
            cursor: pointer;
        }
        .stop {
            background: #c0392b;
        }
        .small {
            color: #777;
            font-size: 13px;
        }
    </style>
</head>
<body>

<?php if (!$driver): ?>
    <div class="card">
        <h1>🚌 UniRide Driver</h1>
        <form method="POST">
            <input name="username" placeholder="Username" required>
            <input name="password" type="password" placeholder="Password" required>
            <button name="login">Log In</button>
        </form>
        <p><?php echo htmlspecialchars($message); ?></p>
    </div>
<?php else: ?>
    <div class="card">
        <h1>Welcome, <?php echo htmlspecialchars($driver['full_name']); ?></h1>
        <p>Bus: <?php echo htmlspecialchars($driver['bus_name'] ?? 'Unassigned'); ?></p>
        <p id="status">Ready. Press Start Trip to share GPS.</p>
        <p class="small" id="coords"></p>
        <button id="startButton" onclick="startTrip()">Start Trip</button>
        <form method="POST" onsubmit="stopTracking()">
            <button class="stop" name="stop_trip">Stop Trip</button>
        </form>
        <form method="POST">
            <button name="logout" style="background:#777">Log Out</button>
        </form>
    </div>

    <script>
        // JavaScript is only used for browser GPS. PHP cannot read phone GPS directly.
        let tracking = false;
        let gpsTimer = null;

        function startTrip() {
            if (!navigator.geolocation) {
                document.getElementById('status').innerText = 'GPS is not supported';
                return;
            }
            tracking = true;
            document.getElementById('startButton').disabled = true;
            document.getElementById('status').innerText = 'Getting GPS location...';
            getLocation();
            gpsTimer = setInterval(getLocation, 5000);
        }

        function getLocation() {
            if (!tracking) return;
            navigator.geolocation.getCurrentPosition(function(position) {
                let lat = position.coords.latitude;
                let lng = position.coords.longitude;
                document.getElementById('coords').innerText = 'Lat: ' + lat.toFixed(5) + ' | Lng: ' + lng.toFixed(5);
                document.getElementById('status').innerText = 'Sharing live location';
                fetch('../backend/update_location.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'driver_id=<?php echo (int)$driver['id']; ?>&lat=' + lat + '&lng=' + lng
                });
            }, function() {
                document.getElementById('status').innerText = 'Allow GPS permission and turn on location';
            }, {enableHighAccuracy: true});
        }

        function stopTracking() {
            tracking = false;
            if (gpsTimer !== null) clearInterval(gpsTimer);
        }
    </script>
<?php endif; ?>

</body>
</html>