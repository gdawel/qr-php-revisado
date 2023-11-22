<?php
include_once 'App_Code/User.class';

Users::Logout();
header("Location: /Quest/index.php");
?>