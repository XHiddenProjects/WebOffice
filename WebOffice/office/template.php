<?php
use WebOffice\Addons;

$addon = new Addons(); // Instantiate the concrete subclass
?>
<html>
    <head>
        <?php
        echo $addon->hook('head');
        echo $addon->hook('css');
        ?>
    </head>
    <body>
        <?php
        echo $addon->hook('beforeMain');
        echo $addon->hook('afterMain');
        echo $addon->hook('footer');
        echo $addon->hook('scripts');
        ?>
        <button id="snapFacial">Take Picture</button>
        <button id="checkFacial">Check</button>
    </body>
</html>