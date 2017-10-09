<?php
session_start();
require_once('all_functions.php');
if(isset($_POST['username']) && isset($_POST['password'])) {
  if(login($_POST['username'], $_POST['password'])) {
    $_SESSION['user'] = $_POST['username'];
    redirect("/phpfinalproject/fp/index.php");
  }
 }
create_login("Login");

create_footer();
?>