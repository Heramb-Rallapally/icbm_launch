<?php
include("../config/database.php");
session_start();

if(isset($_POST['login'])){

$username = $_POST['username'];
$password = $_POST['password'];

$query = "SELECT * FROM Users WHERE Username='$username' AND Password='$password'";
// VULNERABILITY: SQL injection because user input is directly used in query

$result = mysqli_query($conn,$query);

if(mysqli_num_rows($result) > 0){

$user = mysqli_fetch_assoc($result);

$_SESSION['username'] = $user['Username'];
$_SESSION['role'] = $user['Role'];
$_SESSION['clearance'] = $user['Security_Clearance'];

header("Location: dashboard.php");
exit();

}
else{
echo "Invalid login";
}

}
?>

<h2>Missile Control System Login</h2>

<form method="POST">

Username<br>
<input type="text" name="username"><br><br>

Password<br>
<input type="password" name="password"><br><br>

<button name="login">Login</button>

</form>