<?php
echo json_encode(array_values(array_filter(array_diff(scandir(dirname(__FILE__)), ['.', '..']), fn($e) => pathinfo($e, PATHINFO_EXTENSION) !== 'php')), JSON_UNESCAPED_SLASHES);