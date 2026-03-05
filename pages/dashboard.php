<?php
session_start();
include("../config/database.php");

/* Redirect if not logged in */
if(!isset($_SESSION['username'])){
    header("Location: login.php");
    exit();
}

/* Get missile count */
$missile_query = "SELECT COUNT(*) as total FROM Missiles";
$missile_result = mysqli_query($conn,$missile_query);
$missile_data = mysqli_fetch_assoc($missile_result);

/* Get system health */
$health_query = "SELECT Health_Percentage FROM System_Health";
$health_result = mysqli_query($conn,$health_query);
$health_data = mysqli_fetch_assoc($health_result);
?>

<!DOCTYPE html>
<html>
<head>
<title>Missile Control Dashboard</title>
</head>

<body>

<h1>MISSILE CONTROL DASHBOARD</h1>

<h3>Welcome <?php echo $_SESSION['username']; ?></h3>

<hr>

<h2>MISSION OBJECTIVES</h2>

<ol>
<li>Gain Alpha Clearance Access (100 pts)</li>
<li>Delete Missile Database (60 pts)</li>
<li>Escalate Privilege to Admin (50 pts)</li>
<li>Submit Hidden Flags (10 pts)</li>
</ol>

<hr>

<!-- MISSILE INVENTORY SUMMARY -->

<h2>Missile Inventory Summary</h2>

<a href="inventory.php?level=Delta">View Delta Inventory</a>

<hr>

<!-- SYSTEM HEALTH -->

<h2>System Health Meter</h2>

<p>System Health: <?php echo $health_data['Health_Percentage']; ?>%</p>

<p>Simulate Attack:</p>

<a href="../api/update_health.php?value=50">Reduce Health to 50%</a>

<?php

/* Flag appears only if attacker sets health to 0 */
if($health_data['Health_Percentage'] == 0){
echo "<h4>System Compromised</h4>";
echo "<b>FLAG{SYSTEM_HEALTH_COMPROMISED}</b>";
}

?>

<hr>

<!-- SYSTEM LOGS (XSS Vulnerability) -->

<h3>System Logs</h3>
<table border="1" cellpadding="8">
<tr>
<th>Log ID</th>
<th>Encoded Log Entry</th>
</tr>

<tr>
<td>1</td>
<td>PGlORk9dIE1pc3NpbGUgY29udHJvbCBzeXN0ZW0gaW5pdGlhbGl6ZWQ=</td>
</tr>

<tr>
<td>2</td>
<td>W0lORk9dIEludmVudG9yeSBtb2R1bGUgbG9hZGVk</td>
</tr>

<tr>
<td>3</td>
<td>W1dBUk5dIEJhY2t1cCBtYWludGVuYW5jZSBlbmRwb2ludCBhY2Nlc3NlZDogL3BhZ2VzL2RlbGV0ZV9taXNzaWxlcy5waHA=</td>
</tr>

<tr>
<td>4</td>
<td>W0lORk9dIEhlYWx0aCBtb25pdG9yIGFjdGl2ZQ==</td>
</tr>

</table>
<form method="POST">

Enter Log Message:<br>
<input type="text" name="log_message">

<button type="submit" name="add_log">Add Log</button>

</form>

<?php

if(isset($_POST['add_log'])){

$log = $_POST['log_message'];

echo "<p>$log</p>";

/*
VULNERABILITY:
User input printed directly → XSS possible.

Example payload:
<script>alert('XSS')</script>
*/

if(strpos($log,"<script>") !== false){
echo "<b>FLAG{XSS_TRIGGERED}</b>";
}

}

?>

<hr>

<!-- ADMIN LOGIN (SQL Injection) -->

<h2>Admin Login</h2>

<form method="POST">

Username<br>
<input type="text" name="admin_user"><br>

Password<br>
<input type="password" name="admin_pass"><br><br>

<button name="admin_login">Login as Admin</button>

</form>

<?php

if(isset($_POST['admin_login'])){

$user = $_POST['admin_user'];
$pass = $_POST['admin_pass'];

$query = "SELECT * FROM Users WHERE Username='$user' AND Password='$pass' AND Role='Admin'";

/*
VULNERABILITY:
SQL injection possible here because user input
is directly inserted into query.
*/

$result = mysqli_query($conn,$query);

if(mysqli_num_rows($result) > 0){

echo "<h3>Admin Access Granted</h3>";
echo "<b>FLAG{ADMIN_PRIV_ESC}</b>";

}
else{
echo "Access Denied";
}

}

?>
<hr>


<!-- MISSILE LAUNCH -->

<h2>Launch Missile</h2>

<form method="POST">

Enter Launch Code:<br>
<input type="password" name="launch_code">

<button name="launch">Launch</button>

</form>

<?php

if(isset($_POST['launch'])){

$code = $_POST['launch_code'];

if($code == "delta_launch_445"){
echo "<h3>MISSILE LAUNCHED</h3>";
echo "<b>FLAG{MISSILE_LAUNCH_SUCCESS}</b>";
}
else{
echo "Invalid Launch Code";
}

}

?>

</body>
</html>