<?php
use WebOffice\Locales, WebOffice\Addons;
$addon = new Addons();
$lang = new Locales(implode('-',LANGUAGE));
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

        <div class="authorization-form card position-absolute top-50 start-50 translate-middle w-75">
            <div class="tabs d-flex justify-content-around">
                <div class="tab register p-2 active"><span><?php echo $lang->load(['authorization','signUp'],false);?></span></div>
                <div class="tab login p-2"><span><?php echo $lang->load(['authorization','login'],false);?></span></div>
            </div>
            <div class="card-body">
                <form method="post" class="sign_up_form" novalidate>
                    <div class="form-register">
                        <div class="row">
                            <div class="col">
                                <label for="fname" class="form-label text-light"><i class="fa-solid fa-asterisk icon-required"></i> <?php echo $lang->load(['users','first_name']);?></label>
                                <input type="text" name="fname" id="fname" class="form-control" required/>
                                <span class="error-msg"></span>
                            </div>
                            <div class="col">
                                <label for="mname" class="form-label text-light"><?php echo $lang->load(['users','middle_name']);?></label>
                                <input type="text" name="mname" id="mname" class="form-control"/>
                            </div>
                            <div class="col">
                                <label for="lname" class="form-label text-light"><i class="fa-solid fa-asterisk icon-required"></i> <?php echo $lang->load(['users','last_name']);?></label>
                                <input type="text" name="lname" id="lname" class="form-control" required/>
                                <span class="error-msg"></span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <label for="username" class="form-label text-light"><i class="fa-solid fa-asterisk icon-required"></i> <?php echo $lang->load(['users','username']);?></label>
                                <input type="text" name="username" id="username" class="form-control" required/>
                                <span class="error-msg"></span>
                            </div>
                            <div class="col">
                                <label for="email" class="form-label text-light"><i class="fa-solid fa-asterisk icon-required"></i> <?php echo $lang->load(['users','email']);?></label>
                                <input type="email" name="email" id="email" class="form-control" required/>
                                <span class="error-msg"></span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <label for="language" class="form-label text-light"><?php echo $lang->load(['users','language']);?></label>
                                <select name="language" id="language" class="form-select locales-select"><?php 
                                foreach($lang->list() as $l){
                                    $l = str_replace('.json','',$l);
                                    $p = explode('-',$l);
                                    echo "<option".($l===implode('-',LANGUAGE) ? ' selected="selected"' : '')." data-lang=\"$p[0]\" data-region=\"$p[1]\" value=\"$l\"></option>";
                                }
                                ?></select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <label for="password" class="form-label text-light"><i class="fa-solid fa-asterisk icon-required"></i> <?php echo $lang->load(['users','password']);?></label>
                                <input type="password" name="password" id="password" class="form-control" required/>
                                <span class="error-msg"></span>
                            </div>
                            <div class="col">
                                <label for="confirm_password" class="form-label text-light"><i class="fa-solid fa-asterisk icon-required"></i> <?php echo $lang->load(['users','confirm_password']);?></label>
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required/>
                                <span class="error-msg"></span>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col">
                                <a href="./"><button type="button" class="btn btn-primary w-100"><?php echo $lang->load(['buttons','back']);?></button></a>
                            </div>
                            <div class="col">
                                <button type="submit" class="btn btn-success w-100"><?php echo $lang->load(['authorization','signUp']);?></button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="form-login d-none">

                </div>
                
            </div>
        </div>

        <?php
        echo $addon->hook('footer');
        ?>
        <?php
        echo $addon->hook('scripts');
        ?>
    </body>
</html>