<?php
include("../config/database.php");

$value = $_GET['value'];

/*
VULNERABILITY:

No authentication check.
Anyone can directly call this endpoint.

Example attack:
http://localhost/missile_ctf/api/update_health.php?value=0
*/

$query = "UPDATE System_Health SET Health_Percentage=$value";

mysqli_query($conn,$query);

echo "System Health Updated to $value";

?>
