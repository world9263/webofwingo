<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: me");
    exit;
}
$time_minutes = date('H') * 60 + date('i');
$period_num = floor($time_minutes / 3) + 1;
$period = date('Ymd') . sprintf("%03d", $period_num);
echo $period;

?>