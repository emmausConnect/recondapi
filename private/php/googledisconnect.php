<?php
declare(strict_types=1);
require_once __DIR__.'/util01.php';
require_once __DIR__.'/googleconnect.php';
googleDisconnect();

$url = getProtocole().'://'.$_SERVER["HTTP_HOST"].'/';
header("Location:".$url);