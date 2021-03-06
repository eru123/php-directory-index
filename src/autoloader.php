<?php

require_once __DIR__."/../config.php";
require_once __DIR__."/lib.php";

use \Linker\Request\Query;


$f = (string) Query::get("request","f:!r",function($q){return $q["f"];});


$raw = retrieveInfo($f);
$data = $raw !== FALSE ? filterRaw($raw) :[];

$file = retrieveFile($f);