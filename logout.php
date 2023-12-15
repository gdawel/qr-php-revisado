<?php
include_once 'App_Code/User.class.php';

Users::Logout();
header("Location: /Quest/index.php");
?>