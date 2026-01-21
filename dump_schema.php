<?php
$mysqli = new mysqli("localhost", "root", "", "rasidev_hr");

if ($mysqli->connect_errno) {
    file_put_contents("schema_dump.txt", "Failed to connect to MySQL: " . $mysqli->connect_error);
    exit();
}

$output = "";
foreach (['modules', 'permissions'] as $table) {
    $output .= "--- Data: $table ---\n";
    if ($result = $mysqli->query("SELECT * FROM $table")) {
        while ($row = $result->fetch_assoc()) {
            foreach ($row as $key => $val) {
                $output .= "$key: $val | ";
            }
            $output .= "\n";
        }
        $result->free_result();
    }
}
$mysqli->close();
file_put_contents("schema_dump.txt", $output);
echo "Done";
