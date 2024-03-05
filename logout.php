<?php
include_once 'App_Code/User.class.php';

Users::Logout();
header("Location: https://www.sobrare.com.br");
?>