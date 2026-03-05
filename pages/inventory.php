<?php
session_start();
include("../config/database.php");

if(!isset($_SESSION['username'])){
    header("Location: login.php");
    exit();
}

$user_clearance = $_SESSION['clearance'];

/* Read filter from URL */
$level = isset($_GET['level']) ? $_GET['level'] : 'Delta';

/*
VULNERABILITY:
User-controlled parameter directly used in SQL query.
No check against user's clearance.
*/

$query = "SELECT * FROM Missiles WHERE Classification_Level = '$level'";
$result = mysqli_query($conn,$query);
?>

<!DOCTYPE html>
<html>
<head>
<title>Missile Inventory</title>
</head>

<body>

<h1>Missile Inventory</h1>

<a href="dashboard.php">Back to Dashboard</a>

<hr>

<p>Showing missiles with classification: <b><?php echo $level; ?></b></p>

<table border="1" cellpadding="10">

<tr>
<th>ID</th>
<th>Type</th>
<th>Range</th>
<th>Fuel</th>
<th>Status</th>
<th>Target</th>
<th>Classification</th>
</tr>

<?php

$flag_shown = false;

while($row = mysqli_fetch_assoc($result)){

echo "<tr>";

echo "<td>".$row['Missile_ID']."</td>";
echo "<td>".$row['Type']."</td>";
echo "<td>".$row['Range_km']."</td>";
echo "<td>".$row['Fuel']."</td>";
echo "<td>".$row['Status']."</td>";
echo "<td>".$row['Locked_Target_ID']."</td>";
echo "<td>".$row['Classification_Level']."</td>";

echo "</tr>";

/* Flag only appears if a non-Alpha user accesses Alpha missiles */

if($row['Classification_Level'] == "Alpha" && $user_clearance != "Alpha" && !$flag_shown){

echo "<tr><td colspan='7'><b>FLAG{ALPHA_CLEARANCE_GRANTED}</b></td></tr>";

$flag_shown = true;

}

}

?>

</table>

</body>
</html>