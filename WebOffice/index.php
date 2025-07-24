<?php
include_once 'init.php';
include_once 'office/template.php';
use WebOffice\Hardware;
$n = new Hardware();
print_r($n->CPU());