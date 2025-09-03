<?php
use WebOffice\Addons;
use WebOffice\Locales;

$addon = new Addons(); // Instantiate the concrete subclass
?>
<!DOCTYPE html>
<html>
    <head>
        <?php
        $lang = new Locales(implode('-',LANGUAGE));
        echo $addon->hook('head');
        echo $addon->hook('css');
        ?>
    </head>
    <body>
        <?php
        echo $addon->hook('beforeMain');
        ?>
        <div class="container-fluid">
            
        </div>
        <?php
        echo $addon->hook('afterMain');
        echo $addon->hook('footer');
        ?>
        <?php
        echo $addon->hook('scripts');
        ?>
    </body>
</html>