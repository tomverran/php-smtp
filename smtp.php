<?php
/**
 * smtp.php
 * @author Tom
 * @since 29/08/13
 */

//include our composer file
require(dirname(__FILE__).'/vendor/autoload.php');

//Create an event loop
$loop = new \Event\Loop();

//create an SMTP server
new \Smtp\Server($loop);

//Start looping
$loop->start();