<?php

error_reporting(E_ALL);

require_once 'coorg/coorg.class.php';
require_once 'coorg/deployment/coorgsmarty.class.php';
require_once 'coorg/deployment/header.class.php';
require_once 'coorg/deployment/state.class.php';
require_once 'coorg/deployment/mail.class.php';
CoOrg::run();

?>
