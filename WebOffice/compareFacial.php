<?php
echo json_encode(array_values(array_diff(scandir(dirname(__FILE__).'/facials'),['.','..'])),JSON_UNESCAPED_SLASHES);