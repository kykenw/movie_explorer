<?php
session_start();
require_once('all_functions.php');
if(isset($_SESSION['user'])) {
  create_page($_SESSION['user'], true, true);
  
  create_footer();
}else{
  redirect('login.php');
}

?>