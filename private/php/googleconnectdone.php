<?php
declare(strict_types=1);
require_once __DIR__.'/googleconnect.php';
$logger->addLogDebugLineForce(">>>>>> googleconnectdone >>>".__FILE__.' '.__LINE__, $entete='', $force="noram");
    
googleConnect('read');
$logger->addLogDebugLineForce(">>>>>> googleconnectdone après googleConnect('read') >>>".__FILE__.' '.__LINE__, $entete='', $force="noram");

header("Location:".'exaccueil.php');
