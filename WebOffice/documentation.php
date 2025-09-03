<?php
include_once 'init.php';
use WebOffice\Documentation, WebOffice\Locales;
$lang = new Locales(implode('-',LANGUAGE));
$docs = new Documentation($lang->load('name',false));
?>
<!DOCTYPE html>
<html>
    <head>
        <title><?php echo "{$lang->load('name',false)} {$lang->load(['documentation','_title'],false)}";?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
        <link rel="stylesheet" href="<?php echo ASSETS_URL?>/css/all.min.css" type="text/css"/>
        <link rel="stylesheet" href="<?php echo ASSETS_URL;?>/css/documentation.css" type="text/css"/>
        <link rel="stylesheet" href="<?php echo ASSETS_URL?>/css/prism.min.css" type="text/css"/>
    </head>
    <body>
        <?php
            echo $docs->addSection('getting_started','Getting Started')
            ->addSubsection('quickstart','Quickstart','<i class="fa-solid fa-rocket"></i>')
            ->importContent(DOCUMENTATION_PATH.DS.'quickstart.md')
            ->publish();
        ?>
        <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
        <script src="<?php echo ASSETS_URL?>/js/documentation.js"></script>
        <script src="<?php echo ASSETS_URL;?>/js/prism.min.js" type="text/javascript"></script>
        
    </body>
</html>