<?php

include("../config/database.php");

/*
VULNERABILITY:
No authentication or authorization check.

Anyone who discovers this endpoint
can delete the missile database.
*/

$query = "DELETE FROM Missiles";

mysqli_query($conn,$query);

echo "<h2>Missile Database Wiped</h2>";

echo "<p>All missile records have been removed.</p>";

echo "<b>FLAG{DATABASE_DESTROYED}</b>";

?>