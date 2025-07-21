<?php
foreach(array_diff(scandir('libs'),['.','..']) as $libs)
    include_once dirname(__DIR__)."/libs/$libs";
foreach(array_diff(scandir('addons'),['.','..']) as $addons)
    include_once dirname(__DIR__)."/addons/$addons/$addons.plg.php";
