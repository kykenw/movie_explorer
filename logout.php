<?php
session_start();
require_once('all_functions.php');
if(isset($_SESSION['user'])) {
  unset($_SESSION['user']);
  session_destroy();
  if(!isset($_SESSION['user'])) {
    echo " You have logged out successfully";
    redirect("login.php");
  }
}else {
  redirect("login.php");
}


?>