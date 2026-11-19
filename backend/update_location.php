<?php
session_start();
require 'config.php';
if (!isset($_SESSION['driver_id'])) { http_response_code(403); exit('Not logged in'); }
$driver_id = (int)$_SESSION['driver_id'];
$latitude = (float)$_POST['lat'];
$longitude = (float)$_POST['lng'];
mysqli_query($conn, "INSERT INTO driver_locations (driver_id,latitude,longitude) VALUES ($driver_id,$latitude,$longitude)");
mysqli_query($conn, "INSERT INTO driver_status (driver_id,is_active,last_seen) VALUES ($driver_id,1,NOW()) ON DUPLICATE KEY UPDATE is_active=1,last_seen=NOW()");
echo 'Location updated';
?>
