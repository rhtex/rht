<?php
$conn = new mysqli('localhost', 'root', '', 'rasidev_hr');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
$res = $conn->query('SHOW TABLES');
while($row = $res->fetch_row()) echo $row[0].PHP_EOL;
$conn->close();
