<?php
require_once '../private/class/evaluationerror.class.php';
require_once '../private/class/evaluationerrors.class.php';

$err1 = EvaluationError::getInstance();
$err1->setCode("err1Code");
$err1->setMsg("err1Code texte");
$err2 = EvaluationError::getInstance();
$err2->setCode("err2Code");
$err2->setMsg("err2Code texte");

$errAr = EvaluationErrors::getInstance();
$errAr->addError($err1);
$t = $errAr->getErrorsMsgAsString();

$errAr->addError($err2);
$t = $errAr->getErrorsMsgAsString();

$errb1 = EvaluationError::getInstance();
$errb1->setCode("berr1Code");
$errb1->setMsg("berr1Code texte");
$errb2 = EvaluationError::getInstance();
$errb2->setCode("berr2Code");
$errb2->setMsg("berr2Code texte");

$errArb = EvaluationErrors::getInstance();
$errArb->addError($errb1);
$t = $errArb->getErrorsMsgAsString();

$errArb->addError($errb2);
$t = $errArb->getErrorsMsgAsString();

$errAr->mergeErrorArray($errArb);
$t = $errAr->getErrorsMsgAsString();

exit();
