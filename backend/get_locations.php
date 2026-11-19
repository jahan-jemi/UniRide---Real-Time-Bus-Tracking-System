<?php
require 'config.php';
header('Content-Type: application/json');
$sql = "SELECT buses.bus_name,drivers.full_name,locations.latitude,locations.longitude FROM driver_status INNER JOIN drivers ON driver_status.driver_id=drivers.id LEFT JOIN buses ON drivers.bus_id=buses.id INNER JOIN driver_locations AS locations ON locations.id=(SELECT MAX(id) FROM driver_locations WHERE driver_id=drivers.id) WHERE driver_status.is_active=1 AND driver_status.last_seen >= NOW()-INTERVAL 2 MINUTE";
$result=mysqli_query($conn,$sql);
$all=[];
while($row=mysqli_fetch_assoc($result)) {
    $all[]=['bus_name'=>$row['bus_name'] ?? 'No bus','driver_name'=>$row['full_name'],'latitude'=>(float)$row['latitude'],'longitude'=>(float)$row['longitude']];
}
echo json_encode($all);
?>
