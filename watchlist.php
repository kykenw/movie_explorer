<?php
session_start();
require_once('all_functions.php');
if(isset($_SESSION['user'])) {
  create_watchlist($_SESSION['user'], false, true);
  
  create_footer();
}else{
  redirect('login.php');
}
?>