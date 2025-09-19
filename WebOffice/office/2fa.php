<?php
use WebOffice\Locales, WebOffice\Addons, WebOffice\Security, WebOffice\tools\Markdown,WebOffice\Storage;
$addon = new Addons();
$lang = new Locales(implode('-',LANGUAGE));
$security = new Security();
$md = new Markdown();
$storage = new Storage();

if($storage->session('weboffice_auth', action: 'Get')||$storage->cookie(name: 'weboffice_auth',action: 'load')){
    header('Location: '.URL.DS.'dashboard');
}
?>
<!DOCTYPE html>
<html>
    <head>
        <?php
        echo $addon->hook('head');
        echo $addon->hook('css');
        ?>
    </head>
    <body>
        <div class="tfa-panel">
            <h1 class='text-center'><?php echo $lang->load(['mfa','_form']);?></h1>
            <form method="post" novalidate>
                <div class="alert alert-danger d-none"></div>
                <input type="text" class="form-control" name="mfa_code" required/>
                <span class="error-msg alert alert-danger"><?php echo $lang->load(['errors','emptyInput']);?></span>
                <button type="submit" class="btn btn-primary mt-2"><?php echo $lang->load(['buttons','submit']);?></button>
            </form>
        </div>

        <footer><?php echo $addon->hook('footer');?></footer>
        <?php
        echo $addon->hook('scripts');
        ?>
    </body>
</html>