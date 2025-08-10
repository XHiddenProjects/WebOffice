<?php
use WebOffice\Addons;
use WebOffice\Language;

$addon = new Addons(); // Instantiate the concrete subclass
?>
<!DOCTYPE html>
<html>
    <head>
        <?php
        $lang = new Language(implode('-',LANGUAGE));
        echo $addon->hook('head');
        echo $addon->hook('css');
        ?>
    </head>
    <body>
        <?php
        echo $addon->hook('beforeMain');
        ?>
        <nav class="navbar navbar-expand-lg bg-main-nav">
            <div class="container-fluid">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#MainNav" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="MainNav">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                    </ul>
                </div>
            </div>
        </nav>
        <div class="container-fluid">
            <section>
                <div class="row p-5 bg-main-nav text-center">
                    <div class="col">
                        <h1 class="main-title"><?php echo $lang->load('name');?></h1>
                        <p class="text-secondary"><?php echo $lang->load('description');?></p>
                    </div>
                </div>
            </section>
            <section class="pt-4">
                <h2 class="text-center mb-4"><?php echo $lang->load('features');?></h2>
                <div class="ms-auto w-50" data-aos="fade-left">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $lang->load(['features_security','_title']);?></h5>
                            <div class="features-icon bg-primary text-light rounded mx-auto bg-gradient">
                                <i class="fa-solid fa-shield-check"></i>
                            </div>
                            <p class="card-text"><?php echo $lang->load(['features_security','_description']);?></p>
                        </div>
                    </div>
                </div>
                <div class="me-auto w-50 mt-5" data-aos="fade-right">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $lang->load(['features_privacy','_title']);?></h5>
                            <div class="features-icon bg-primary text-light rounded mx-auto bg-gradient">
                                <i class="fa-solid fa-eye-slash"></i>
                            </div>
                            <p class="card-text"><?php echo $lang->load(['features_privacy','_description']);?></p>
                        </div>
                    </div>
                </div>
                <div class="ms-auto w-50 mt-5" data-aos="fade-left">
                    <div class="card">
                        <div class="card-body">
                            
                            <h5 class="card-title"><?php echo $lang->load(['features_network','_title']);?></h5>
                            <div class="features-icon bg-primary text-light rounded mx-auto bg-gradient">
                                <i class="fa-solid fa-network-wired"></i>
                            </div>
                            <p class="card-text"><?php echo $lang->load(['features_network','_description']);?></p>
                        </div>
                    </div>
                </div>
                <div class="me-auto w-50 mt-5" data-aos="fade-right">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $lang->load(['features_server','_title']);?></h5>
                            <div class="features-icon bg-primary text-light rounded mx-auto bg-gradient">
                                <i class="fa-solid fa-server"></i>
                            </div>
                            <p class="card-text"><?php echo $lang->load(['features_server','_description']);?></p>
                        </div>
                    </div>
                </div>
                <div class="ms-auto w-50 mt-5" data-aos="fade-left">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $lang->load(['features_ad','_title']);?></h5>
                            <div class="features-icon bg-primary text-light rounded mx-auto bg-gradient">
                                <i class="fa-solid fa-screen-users"></i>
                            </div>
                            <p class="card-text"><?php echo $lang->load(['features_ad','_description']);?></p>
                        </div>
                    </div>
                </div>
                <div class="me-auto w-50 mt-5" data-aos="fade-right">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $lang->load(['features_backup','_title']);?></h5>
                            <div class="features-icon bg-primary text-light rounded mx-auto bg-gradient">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                            </div>
                            <p class="card-text"><?php echo $lang->load(['features_backup','_description']);?></p>
                        </div>
                    </div>
                </div>
                <div class="ms-auto w-50 mt-5" data-aos="fade-left">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $lang->load(['features_updates','_title']);?></h5>
                            <div class="features-icon bg-primary text-light rounded mx-auto bg-gradient">
                                <i class="fa-solid fa-cloud-arrow-down"></i>
                            </div>
                            <p class="card-text"><?php echo $lang->load(['features_updates','_description']);?></p>
                        </div>
                    </div>
                </div>
                <div class="me-auto w-50 mt-5" data-aos="fade-right">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $lang->load(['features_hr','_title']);?></h5>
                            <div class="features-icon bg-primary text-light rounded mx-auto bg-gradient">
                                <i class="fa-solid fa-users-gear"></i>
                            </div>
                            <p class="card-text"><?php echo $lang->load(['features_hr','_description']);?></p>
                        </div>
                    </div>
                </div>
                <div class="ms-auto w-50 mt-5" data-aos="fade-left">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $lang->load(['features_terminal','_title']);?></h5>
                            <div class="features-icon bg-primary text-light rounded mx-auto bg-gradient">
                                <i class="fa-solid fa-terminal"></i>
                            </div>
                            <p class="card-text"><?php echo $lang->load(['features_terminal','_description']);?></p>
                        </div>
                    </div>
                </div>
                <div class="me-auto w-50 mt-5" data-aos="fade-right">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $lang->load(['features_addons_themes','_title']);?></h5>
                            <div class="features-icon bg-primary text-light rounded mx-auto bg-gradient">
                                <i class="fa-solid fa-puzzle-piece"></i>
                            </div>
                            <p class="card-text"><?php echo $lang->load(['features_addons_themes','_description']);?></p>
                        </div>
                    </div>
                </div>
                <div class="ms-auto w-50 mt-5" data-aos="fade-left">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $lang->load(['features_multilingual','_title']);?></h5>
                            <div class="features-icon bg-primary text-light rounded mx-auto bg-gradient">
                                <i class="fa-solid fa-language"></i>
                            </div>
                            <p class="card-text"><?php echo $lang->load(['features_multilingual','_description']);?></p>
                        </div>
                    </div>
                </div>
                <div class="me-auto w-50 mt-5" data-aos="fade-right">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $lang->load(['features_office_suite','_title']);?></h5>
                            <div class="features-icon bg-primary text-light rounded mx-auto bg-gradient">
                                <i class="fa-solid fa-computer"></i>
                            </div>
                            <p class="card-text"><?php echo $lang->load(['features_office_suite','_description']);?></p>
                        </div>
                    </div>
                </div>
            </section>
            <?php
            echo $addon->hook('afterMain');
            echo $addon->hook('footer');
            ?>
        </div>
        <?php
        echo $addon->hook('scripts');
        ?>
    </body>
</html>