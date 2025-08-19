<?php
include_once 'init.php';
use WebOffice\Documentation, WebOffice\Language, WebOffice\Addons;
$lang = new Language(implode('-',LANGUAGE));
$docs = new Documentation($lang->load('name',false));
?>
<!DOCTYPE html>
<html>
    <head>
        <title><?php echo "{$lang->load('name',false)} {$lang->load(['documentation','_title'],false)}";?></title>
        <link rel="stylesheet" href="<?php echo ASSETS_URL?>/css/all.min.css" type="text/css"/>
        <link rel="stylesheet" href="<?php echo ASSETS_URL;?>/css/documentation.css" type="text/css"/>
    </head>
    <body>
        <?php
            echo $docs->addSection('getting_started','Getting Started')
            ->addSubsection('quickstart','Quickstart','<i class="fa-solid fa-rocket"></i>')
            ->addContent('**Bold**')
            ->addSubsection('development','Development','<i class="fa-solid fa-rectangle-terminal"></i>')
            ->addContent('Development section content goes here')
            ->addSection('api-reference', 'API Reference')
            ->addCollapseSubsection('user-routes','Users Routes')
            ->addSubsection('get-users','Get Users','<span class="api-badge api-get">GET</span>','user-routes')
            ->addContent('Hello _World_')
            ->publish();
        ?>
        <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
        <script src="<?php echo ASSETS_URL?>/js/documentation.js"></script>
    </body>
</html>