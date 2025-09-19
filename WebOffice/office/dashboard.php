<?php
use WebOffice\Addons;
use WebOffice\Locales;
use WebOffice\Storage;
use WebOffice\URI;
use WebOffice\Users;
$addon = new Addons(); // Instantiate the concrete subclass
$storage = new Storage();
$users = new Users();
$uri = new URI();
if(!$storage->session('weboffice_auth', action: 'Get')&&!$storage->cookie(name: 'weboffice_auth',action: 'load')) header('Location: '.URL.DS.'auth');

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
            <button class="border-0 bg-transparent fs-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#dashboard-nav" aria-controls="dashboard-navLabel">
                <i class="fa-solid fa-bars"></i>
            </button>
            <div class="offcanvas offcanvas-start" tabindex="-1" id="dashboard-nav" aria-labelledby="dashboard-navLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="dashboard-navLabel"><?php echo $lang->load(['authorization','_dashboard']);?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <ul class="list-group">
                        <a href="./dashboard" class="text-decoration-none"><li class="list-group-item"><?php echo $lang->load(['authorization','_dashboard']);?></li></a>
                    </ul>
                </div>
            </div>
            <?php
                if($uri->match('dashboard')){
            ?>
            <div wo-clock="true" wo-clock-format="d.m.Y H:i:s"></div>
            <?php
            }
            ?>
        </div>
        <?php
        echo $addon->hook('afterMain');?>
        <footer><?php echo $addon->hook('footer');?></footer>
        <?php
        echo $addon->hook('scripts');
        ?>
    </body>
</html>