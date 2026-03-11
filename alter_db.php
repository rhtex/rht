<?php
$db = new mysqli('localhost', 'root', '', 'rasidev_hr');
if ($db->connect_error) die('Connect Error: ' . $db->connect_error);
$res = $db->query("ALTER TABLE transports ADD COLUMN transport_type ENUM('Courier Service', 'Parcel Service') DEFAULT 'Parcel Service' AFTER transport_name");
if (!$res) echo 'Error: ' . $db->error; else echo 'Success';
?>
