<?php
require_once('all_functions.php');
if(isset($_POST['username']) && isset($_POST['password'])) {
 if(register($_POST['username'], $_POST['password'])) {
   redirect("/phpfinalproject/fp/index.php");
 }
}

create_register("Register");

create_footer();
?>