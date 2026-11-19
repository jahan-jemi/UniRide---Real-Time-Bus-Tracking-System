<?php
session_start();
if (!isset($_SESSION['admin'])) { 
    header('Location: login.php'); 
    exit; 
}
require '../backend/config.php';

$message = "";

// Add bus
if (isset($_POST['add_bus'])) {
    $bus_name = mysqli_real_escape_string($conn, $_POST['bus_name']);
    if (mysqli_query($conn, "INSERT INTO buses (bus_name) VALUES ('$bus_name')")) {
        $message = "Bus added successfully";
    } else {
        $message = "Could not add bus";
    }
}

// Add driver
if (isset($_POST['add_driver'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $username  = mysqli_real_escape_string($conn, $_POST['username']);
    $password  = $_POST['password'];
    $bus_id    = (int)$_POST['bus_id'];
    $bus_value = ($bus_id == 0) ? "NULL" : $bus_id;

    $check = mysqli_query($conn, "SELECT id FROM drivers WHERE username='$username'");
    if (mysqli_num_rows($check) > 0) {
        $message = "Username already exists";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql  = "INSERT INTO drivers (username, password_hash, full_name, bus_id) VALUES ('$username', '$hash', '$full_name', $bus_value)";
        $message = mysqli_query($conn, $sql) ? "Driver added successfully" : "Could not add driver";
    }
}

// Delete bus
if (isset($_POST['delete_bus'])) {
    $id = (int)$_POST['bus_id'];
    mysqli_query($conn, "DELETE FROM buses WHERE id=$id");
    $message = "Bus deleted";
}

// Delete driver
if (isset($_POST['delete_driver'])) {
    $id = (int)$_POST['driver_id'];
    mysqli_query($conn, "DELETE FROM drivers WHERE id=$id");
    $message = "Driver deleted";
}

// Logout
if (isset($_POST['logout'])) { 
    session_destroy(); 
    header('Location: login.php'); 
    exit; 
}

$buses = mysqli_query($conn, "SELECT * FROM buses ORDER BY id DESC");
$drivers = mysqli_query($conn, "
    SELECT drivers.id, drivers.full_name, drivers.username, buses.bus_name, driver_status.is_active, driver_status.last_seen 
    FROM drivers 
    LEFT JOIN buses ON drivers.bus_id = buses.id 
    LEFT JOIN driver_status ON drivers.id = driver_status.driver_id 
    ORDER BY drivers.id DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>UniRide Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div id="topbar">
        <h1>🚌 UniRide Admin</h1>
        <form method="POST">
            <button name="logout" class="btn-logout">Log Out</button>
        </form>
    </div>

    <?php if (!empty($message)): ?>
        <p class="msg" style="text-align:center;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <!-- Bus Management Panel -->
    <div class="panel">
        <h2>Add Bus</h2>
        <form method="POST">
            <input name="bus_name" placeholder="Bus name" required>
            <button type="submit" name="add_bus">Add Bus</button>
        </form>

        <h2>Bus List</h2>
        <table>
            <tr>
                <th>Bus Name</th>
                <th>Added</th>
                <th>Action</th>
            </tr>
            <?php while ($bus = mysqli_fetch_assoc($buses)): ?>
            <tr>
                <td><?php echo htmlspecialchars($bus['bus_name']); ?></td>
                <td><?php echo htmlspecialchars($bus['created_at']); ?></td>
                <td>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this bus?');">
                        <input type="hidden" name="bus_id" value="<?php echo $bus['id']; ?>">
                        <button type="submit" name="delete_bus" class="btn-delete">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <!-- Driver Management Panel -->
    <div class="panel">
        <h2>Add Driver</h2>
        <form method="POST">
            <input name="full_name" placeholder="Full name" required>
            <input name="username" placeholder="Username" required>
            <input name="password" type="password" placeholder="Password" required>
            <select name="bus_id">
                <option value="0">No bus assigned</option>
                <?php 
                $options = mysqli_query($conn, "SELECT id, bus_name FROM buses");
                while ($b = mysqli_fetch_assoc($options)): 
                ?>
                    <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['bus_name']); ?></option>
                <?php endwhile; ?>
            </select>
            <button type="submit" name="add_driver">Add Driver</button>
        </form>

        <h2>Driver List</h2>
        <table>
            <tr>
                <th>Name</th>
                <th>Username</th>
                <th>Bus</th>
                <th>Status</th>
                <th>Last Seen</th>
                <th>Action</th>
            </tr>
            <?php while ($d = mysqli_fetch_assoc($drivers)): ?>
            <tr>
                <td><?php echo htmlspecialchars($d['full_name']); ?></td>
                <td><?php echo htmlspecialchars($d['username']); ?></td>
                <td><?php echo htmlspecialchars($d['bus_name'] ?? 'None'); ?></td>
                <td>
                    <span class="status <?php echo ($d['is_active'] == 1) ? 'active' : 'offline'; ?>">
                        <?php echo ($d['is_active'] == 1) ? 'Tracking' : 'Offline'; ?>
                    </span>
                </td>
                <td><?php echo htmlspecialchars($d['last_seen'] ?? '-'); ?></td>
                <td>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this driver?');">
                        <input type="hidden" name="driver_id" value="<?php echo $d['id']; ?>">
                        <button type="submit" name="delete_driver" class="btn-delete">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

</body>
</html>