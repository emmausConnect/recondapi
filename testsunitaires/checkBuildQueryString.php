<?php
declare(strict_types=1);
include "../private/util01.php";
$testnr=0;
$err=[];

$testnr++; // 1
$qStringArray = ["a"=>"A1", "b"=>"B1", "c"=>"C1", "d"=>"D1"];
$attendu = '&a=A1&b=B1&c=C1&d=D1';
$r = buildQueryStringAsString($qStringArray);
if ($r != $attendu ) {
    $err[$testnr] = [$qStringArray, $r, $attendu];
}

$testnr++; // 2
$qStringArray = ["a"=>"A1", "b"=>"B1", "c"=>"C1", "d"=>"D1"];
$toExclude=["a","c"];
$attendu = '&b=B1&d=D1';
$r = buildQueryStringAsString($qStringArray,$toExclude);
if ($r != $attendu) {
    $err[$testnr] = [$qStringArray, $r, $attendu];
}

$testnr++; // 3
$qStringArray = ["a"=>"A1", "b"=>"B1", "c"=>"C1", "d"=>"D1"];
$toExclude=["a","c"];
$toInclude=[
    "dft" => ["dfttrt"=>"ignore"]
];
$attendu = '&b=B1&d=D1';;
$r = buildQueryStringAsString($qStringArray,$toExclude,$toInclude);
if ($r != $attendu) {
    $err[$testnr] = [$qStringArray, $r, $attendu];
}

$testnr++; // 4
$qStringArray = ["a"=>"A1", "b"=>"B1", "c"=>"C1", "d"=>"D1"];
$toExclude=["a","c"];
$toInclude=[
    "dft" => ["dfttrt"=>"add"]
];
$attendu = '&b=B1&d=D1';;
$r = buildQueryStringAsString($qStringArray,$toExclude,$toInclude);
if ($r != $attendu) {
    $err[$testnr] = [$qStringArray, $r, $attendu];
}

$testnr++; // 4
$qStringArray = ["a"=>"A1", "b"=>"B1", "c"=>"C1", "d"=>"D1"];
$toExclude=["a","c"];
$toInclude=[
    "dft"  => ["dfttrt"=>"add"],
    "data" => [
        "b"=>["trt"=> "force", "dft"=> "dft1"]
        ]
];
$attendu = '&b=dft1&d=D1';;
$r = buildQueryStringAsString($qStringArray,$toExclude,$toInclude);
if ($r != $attendu) {
    $err[$testnr] = [$qStringArray, $r, $attendu];
}

$testnr++;