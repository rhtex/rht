<?php
$mysqli = new mysqli("localhost", "root", "", "rasidev_hr");
$mysqli->query("INSERT INTO role_permissions (role_id,permission_id,created_at,updated_at) SELECT 1,id,NOW(),NOW() FROM permissions WHERE permission_name LIKE 'bill.%' AND id NOT IN (SELECT permission_id FROM role_permissions WHERE role_id=1)");
echo "Assigned " . $mysqli->affected_rows . " Bills permissions to Admin role.";
$mysqli->close();
