<?php
use WebOffice\Locales, WebOffice\Addons, WebOffice\Security, WebOffice\tools\Markdown,WebOffice\Storage;
$addon = new Addons();
$lang = new Locales(implode('-',LANGUAGE));
$security = new Security();
$md = new Markdown();
$storage = new Storage();

if($storage->session('weboffice_auth', action: 'Get')||$storage->cookie(name: 'weboffice_auth',action: 'load'))
    header('Location: '.URL.DS.'dashboard');
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
        <div class="modal fade" id="terms_and_conditions" tabindex="-1" aria-labelledby="tac">
                            <div class="modal-dialog modal-dialog-scrollable">
                                <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="tac"><?php echo $lang->load(['terms_and_conditions']);?></h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <?php
                                        echo $md->parse(file_get_contents(POLICIES_PATH.DS.strtolower(LANGUAGE[0]).DS.'terms-and-conditions.md'));
                                    ?>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $lang->load(['buttons','close']);?></button>
                                </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal fade" id="legal" tabindex="-1" aria-labelledby="lg">
                                            <div class="modal-dialog modal-dialog-scrollable">
                                                <div class="modal-content">
                                                <div class="modal-header">
                                                    <h1 class="modal-title fs-5" id="lg"><?php echo $lang->load(['legal']);?></h1>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <?php
                                                        echo $md->parse(file_get_contents(POLICIES_PATH.DS.strtolower(LANGUAGE[0]).DS.'legal.md'));
                                                    ?>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $lang->load(['buttons','close']);?></button>
                                                </div>
                                                </div>
                                            </div>
                        </div>
                        <div class="modal fade" id="privacy_policy" tabindex="-1" aria-labelledby="pp">
                            <div class="modal-dialog modal-dialog-scrollable">
                                <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="pp"><?php echo $lang->load(['privacy_policy']);?></h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <?php
                                        echo $md->parse(file_get_contents(POLICIES_PATH.DS.strtolower(LANGUAGE[0]).DS.'privacy-policy.md'));
                                    ?>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $lang->load(['buttons','close']);?></button>
                                </div>
                                </div>
                            </div>
                        </div>
        <div class="authorization-form card border-0 bg-transparent">
            <div class="tabs d-flex justify-content-around">
                <div class="tab register p-2 active"><span><?php echo $lang->load(['authorization','signUp'],false);?></span></div>
                <div class="tab login p-2"><span><?php echo $lang->load(['authorization','login'],false);?></span></div>
            </div>
            <div class="card-body">
                <form method="post" class="sign_up_form" novalidate enctype="multipart/form-data">
                    <div class="alert alert-danger d-none"></div>
                    <input name="token" type="hidden" value="<?php echo $security->CSRF('load');?>"/>
                    <input name="main" type="hidden" value="<?php echo base64_encode(URL);?>"/>
                    <input name="timezone" type="hidden" data-timezone/>
                    <div class="form-register">
                        <div class="row">
                            <div class="col">
                                <label for="fname" class="form-label text-light"><i class="fa-solid fa-asterisk icon-required"></i> <?php echo $lang->load(['users','first_name']);?></label>
                                <input type="text" name="fname" id="fname" class="form-control" required/>
                                <span class="error-msg alert alert-danger"><?php echo $lang->load(['errors','emptyInput']);?></span>
                            </div>
                            <div class="col">
                                <label for="mname" class="form-label text-light"><?php echo $lang->load(['users','middle_name']);?></label>
                                <input type="text" name="mname" id="mname" class="form-control"/>
                            </div>
                            <div class="col">
                                <label for="lname" class="form-label text-light"><i class="fa-solid fa-asterisk icon-required"></i> <?php echo $lang->load(['users','last_name']);?></label>
                                <input type="text" name="lname" id="lname" class="form-control" required/>
                                <span class="error-msg alert alert-danger"><?php echo $lang->load(['errors','emptyInput']);?></span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <label for="username" class="form-label text-light"><i class="fa-solid fa-asterisk icon-required"></i> <?php echo $lang->load(['users','username']);?></label>
                                <input type="text" name="username" id="username" class="form-control" required/>
                                <span class="error-msg alert alert-danger"><?php echo $lang->load(['errors','emptyInput']);?></span>
                            </div>
                            <div class="col">
                                <label for="email" class="form-label text-light"><i class="fa-solid fa-asterisk icon-required"></i> <?php echo $lang->load(['users','email']);?></label>
                                <input type="email" name="email" id="email" class="form-control" required/>
                                <span class="error-msg alert alert-danger"><?php echo $lang->load(['errors','emptyInput']);?></span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <label for="language" class="form-label text-light"><?php echo $lang->load(['users','language']);?></label>
                                <select name="language" id="language" class="form-select locales-select"><?php 
                                foreach($lang->list() as $l){
                                    $l = str_replace('.json','',$l);
                                    $p = explode('-',$l);
                                    echo "<option data-lang=\"$p[0]\" data-region=\"$p[1]\" value=\"$l\"></option>";
                                }
                                ?></select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <label for="password" class="form-label text-light"><i class="fa-solid fa-asterisk icon-required"></i> <?php echo $lang->load(['users','password']);?></label>
                                <input type="password" data-password-tools="true" data-password-length="12" data-password-measure="true" data-password-use="lowercase,uppercase,numbers,symbols" data-password-no="copy,paste,select" name="password" id="password" class="form-control" required/>
                                <span class="error-msg alert alert-danger"><?php echo $lang->load(['errors','emptyInput']);?></span>
                            </div>
                            <div class="col">
                                <label for="confirm_password" class="form-label text-light"><i class="fa-solid fa-asterisk icon-required"></i> <?php echo $lang->load(['users','confirm_password']);?></label>
                                <input type="password" data-password-tools="true" data-password-use="lowercase,uppercase,numbers,symbols" data-password-noGen data-password-no="copy,paste,select" name="confirm_password" id="confirm_password" class="form-control" required/>
                                <span class="error-msg alert alert-danger"><?php echo $lang->load(['errors','emptyInput']);?></span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="form-check">
                                    <input class="form-check-input" required type="checkbox" value="" id="terms-and-conditions">
                                    <label class="form-check-label text-light text-decoration-underline cursor-pointer" data-bs-toggle="modal" data-bs-target="#terms_and_conditions" for="terms-and-conditions"><i class="fa-solid fa-asterisk icon-required"></i> <?php echo $lang->load(['terms_and_conditions']);?></label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="form-check">
                                    <input class="form-check-input" required type="checkbox" value="" id="privacy-policy">
                                    <label class="form-check-label text-light text-decoration-underline cursor-pointer" data-bs-toggle="modal" data-bs-target="#privacy_policy" for="privacy-policy"><i class="fa-solid fa-asterisk icon-required"></i> <?php echo $lang->load(['privacy_policy']);?></label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="form-check">
                                    <input class="form-check-input" required type="checkbox" value="" id="legal-policy">
                                    <label class="form-check-label text-light text-decoration-underline cursor-pointer" data-bs-toggle="modal" data-bs-target="#legal" for="legal-policy"><i class="fa-solid fa-asterisk icon-required"></i> <?php echo $lang->load(['legal']);?></label>
                                </div>
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
                    <form method="post" class="login_form" novalidate enctype="multipart/form-data">
                        <div class="alert alert-danger d-none"></div>
                        <input name="token" type="hidden" value="<?php echo $security->CSRF('load');?>"/>
                        <input name="main" type="hidden" value="<?php echo base64_encode(URL);?>"/>
                        <div class="row">
                            <div class="col">
                                <label for="login_username" class="form-label text-light"><i class="fa-solid fa-asterisk icon-required"></i> <?php echo $lang->load(['authorization','userLogin']);?></label>
                                <input type="text" name="username" id="login_username" class="form-control" required/>
                                <span class="error-msg alert alert-danger"><?php echo $lang->load(['errors','emptyInput']);?></span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <label for="login_psw" class="form-label text-light"><i class="fa-solid fa-asterisk icon-required"></i> <?php echo $lang->load(['users','password']);?></label>
                                <input type="password" data-password-tools="true" data-password-nogen="true" data-password-no="copy,paste,select" name="password" id="login_psw" class="form-control" required/>
                                <span class="error-msg alert alert-danger"><?php echo $lang->load(['errors','emptyInput']);?></span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="rememberMe">
                                    <label class="form-check-label text-light" for="rememberMe"><?php echo $lang->load(['authorization','rememberMe']);?></label>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col">
                                <a href="./"><button type="button" class="btn btn-primary w-100"><?php echo $lang->load(['buttons','back']);?></button></a>
                            </div>
                            <div class="col">
                                <button type="submit" class="btn btn-success w-100"><?php echo $lang->load(['authorization','login']);?></button>
                            </div>
                        </div>
                    </form>
                </div>
                
            </div>
        </div>

        <footer><?php echo $addon->hook('footer');?></footer>
        <?php
        echo $addon->hook('scripts');
        ?>
    </body>
</html>