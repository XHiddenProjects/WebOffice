<?php
include_once 'init.php';
use WebOffice\URI, WebOffice\tools\Markdown;
use WebOffice\Locales;
$uri = new URI();
$md = new Markdown();

$paths = $uri->arrPath($_SERVER['REQUEST_URI']);
$locales = new Locales(implode('-',LANGUAGE));
?>
<!DOCTYPE html>
<html>
    <head>
        <title><?php echo $locales->load([strtolower(preg_replace('/-/','_',$paths[2]))])??$locales->load(['policies','__notFound']);?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
        <style>
            body{
                padding: 1rem;
            }
        </style>
    </head>
    <body>
        <?php
        echo match(strtolower($paths[2])){
            'terms-and-conditions'=>$md->parse(file_get_contents(POLICIES_PATH.DS.strtolower(LANGUAGE[0]).DS.'terms-and-conditions.md')),
            'privacy-policy'=>$md->parse(file_get_contents(POLICIES_PATH.DS.strtolower(LANGUAGE[0]).DS.'privacy-policy.md')),
            'legal'=>$md->parse(file_get_contents(POLICIES_PATH.DS.strtolower(LANGUAGE[0]).DS.'legal.md')),
            default=>"<h1>{$locales->load(['policies','__notFound'])}</h1>"
        };
        ?>
        <button class="btn btn-primary w-100 mt-2"><?php echo $locales->load(['buttons','back']);?></button>
        <script>
            document.querySelector('button').addEventListener('click',()=>{window.history.back();});
        </script>
    </body>
</html>