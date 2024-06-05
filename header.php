<?php
session_start();
$con = mysqli_connect("localhost", "root", "", "userdemo") or die(mysqli_error());
$sessid = addslashes($_SESSION['sessionid']);
$q = "select userid from sessions where sessionid = '$sessid'";
$res = mysqli_query($con, $q);
if (mysqli_num_rows($res) != 1)
	die("Invalid session");
?>