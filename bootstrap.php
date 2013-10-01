<?php

use Psc\Boot\BootLoader;

/**
 * Bootstrap and Autoload whole application
 *
 * you can use this file to bootstrap for tests or bootstrap for scripts / others
 */
$ds = DIRECTORY_SEPARATOR;

require_once __DIR__.$ds.'lib'.$ds.'package.boot.php';
$bootLoader = new BootLoader(__DIR__);
$bootLoader->loadComposer();
//$bootLoader->registerCMSContainer();
$bootLoader->registerPackageRoot();

?>