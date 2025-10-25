<?php
use WebOffice\Addons,
WebOffice\Locales,
WebOffice\Storage,
WebOffice\URI,
WebOffice\Users,
WebOffice\Network,
WebOffice\Device,
WebOffice\Utils,
WebOffice\Data;
$addon = new Addons(); // Instantiate the concrete subclass
$storage = new Storage();
$users = new Users();
$uri = new URI();
$network = new Network();
$device = new Device();
$utils = new Utils();
$data = new Data();
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
            <div class="d-flex justify-content-between py-2">
                <button class="border-0 bg-transparent fs-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#dashboard-nav" aria-controls="dashboard-navLabel">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <button class="btn btn-danger logout"><i class="fa-solid fa-left-from-bracket"></i> <?php echo $lang->load(['buttons','logout']);?></button>
            </div>
            <div class="offcanvas offcanvas-start" tabindex="-1" id="dashboard-nav" aria-labelledby="dashboard-navLabel">
                <div class="offcanvas-header">
                    <a href="<?php echo URL;?>/dashboard" class="text-decoration-none text-dark"><h5 class="offcanvas-title" id="dashboard-navLabel"><?php echo $lang->load(['authorization','_dashboard']);?></h5></a>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <ul class="list-group list-group-flush">
                        <a data-bs-toggle="collapse" href="#tab-dashboard" role="button" aria-expanded="false" aria-controls="tab-dashboard" class="text-decoration-none"><li class="list-group-item list-group-item-action"><i class="fa-solid fa-gauge-high"></i> <?php echo $lang->load(['authorization','_dashboard']);?></li></a>
                        <div class="collapse" id="tab-dashboard">
                            <?php
                        if($users->isAdmin()){
                            ?>
                        <a href="<?php echo URL;?>/dashboard/analysis" class="text-decoration-none"><li class="list-group-item list-group-item-action"><i class="fa-solid fa-chart-mixed"></i> <?php echo $lang->load(['dashboard','analysis']);?></li></a>
                        <?php
                        }
                        ?>
                        <?php
                        if($users->isAdmin()){
                            ?>
                        <a href="<?php echo URL;?>/dashboard/config" class="text-decoration-none"><li class="list-group-item list-group-item-action"><i class="fa-solid fa-cogs"></i> <?php echo $lang->load(['dashboard','config','_label']);?></li></a>
                        <?php
                        }
                        ?>
                        <a href="<?php echo URL;?>/dashboard/support" class="text-decoration-none"><li class="list-group-item list-group-item-action"><i class="fa-solid fa-ticket"></i> <?php echo $lang->load(['support','_title']);?></li></a>
                        <?php
                            if($users->isAdmin()){
                        ?>
                        <a href="<?php echo URL;?>/dashboard/terminal" class="text-decoration-none"><li class="list-group-item list-group-item-action"><i class="fa-solid fa-square-terminal"></i> <?php echo $lang->load(['terminal','_label']);?></li></a>
                        <?php
                            }
                        ?>
                        </div>
                        <a data-bs-toggle="collapse" href="#tab-devices" role="button" aria-expanded="false" aria-controls="tab-device" class="text-decoration-none"><li class="list-group-item list-group-item-action"><i class="fa-solid fa-computer"></i> <?php echo $lang->load(['devices','_label']);?></li></a>
                        <div class="collapse" id="tab-devices">
                            <a href="<?php echo URL;?>/dashboard/devices" class="text-decoration-none"><li class="list-group-item list-group-item-action"><i class="fa-solid fa-floppy-disks"></i> <?php echo $lang->load(['devices','view_devices']);?></li></a>
                            <a href="<?php echo URL;?>/dashboard/devices/register" class="text-decoration-none"><li class="list-group-item list-group-item-action"><i class="fa-solid fa-display-medical"></i> <?php echo $lang->load(['devices','register_device']);?></li></a>
                            <?php
                                if($users->isAdmin()){
                            ?>
                            <a href="<?php echo URL;?>/dashboard/devices/tracking" class="text-decoration-none"><li class="list-group-item list-group-item-action"><i class="fa-solid fa-location-dot"></i> <?php echo $lang->load(['devices','tracking_device']);?></li></a>
                            <?php
                                }
                            ?>
                        </div>
                        <a href="<?php echo URL;?>/dashboard/profile" class="text-decoration-none"><li class="list-group-item list-group-item-action"><i class="fa-solid fa-user"></i> <?php echo $lang->load(['profile','_label']);?></li></a>
                    </ul>
                </div>
            </div>
            <?php
                if($uri->match('dashboard')){
            ?>
            <div class="row">
                <div class="col-4">
                    <div wo-clock="true"></div>
                </div>
                <div class="col">
                    <div wo-weather="true"></div>
                </div>
            </div>
            <?php
            }
            if($uri->match('dashboard/analysis')){
                if($users->isAdmin()){
                ?>
                <div class="analysis-charts overflow-auto">
                    <h2 class="text-center"><?php echo $lang->load('service');?></h2>
                    <div class="d-flex flex-wrap">
                        <div class="users-count-chart"></div>
                    </div>
                    <h2 class="text-center border-2 border-top border-secondary"><?php echo "{$lang->load(['hardware','_label'])}";?></h2>
                    <div class="d-flex flex-wrap">
                        <div class="cpu-chart"></div>
                        <div class="memory-chart"></div>
                    </div>
                    <div class="d-flex flex-wrap">
                        <div class="battery mb-1">
                            <h3 class="text-center"><?php echo $lang->load(['hardware','battery']);?></h3>
                            <p class="battery-percent text-center fw-bold"></p>
                            <div class="battery-head"></div>
                            <div class="battery-body">
                                <div class="charge">
                                    <i class="fa-solid fa-bolt-lightning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <h2 class="text-center border-2 border-top border-secondary"><?php echo $lang->load(['internet','_label']);?></h2>
                    <div class="d-flex flex-wrap">
                        <div class="internet-information d-flex flex-wrap justify-content-center align-items-center">
                            <table class="table table-striped w-100 table-hover">
                                <thead>
                                    <tr>
                                        <th><?php echo $lang->load(['information']);?></th>
                                        <th><?php echo $lang->load(['results']);?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        foreach($network->checkInternetConnection() as $label=>$result){
                                            if($label==='isWireless'||$label==='connected'||$label==='connection_secured')
                                                $result = (int)$result==0 ? '<i class="fa-solid fa-x" style="color: #ff0000;"></i>' : '<i class="fa-solid fa-check" style="color: #00ff00;"></i>';
                                            if($label==='latency') 
                                                $result = round($result,2)."ms";
                                            if($label==='connection_name') 
                                                $result = "<span data-spoiler>$result</span>";
                                            echo "<tr>
                                            <td>{$lang->load(['internet',$label])}</td>
                                            <td>$result</td>
                                            </tr>";
                                        }
                                    ?>
                                </tbody>
                            </table>
                            <div class="downloadIndicator"></div>
                            <div class="uploadIndicator"></div>
                        </div>
                    </div>
                </div>
            <?php
                }
            }
            if($uri->match("dashboard/support")){
                if($users->isAdmin()||$users->isModerator()){
                ?>
            <div class="row supportDesk-container">
                <div class="col-md-6 border border-bottom-2 border-end-md pb-1 pe-md-1 border-top-0">
                    <div class="row gap-0">
                        <div class="col-6">
                            <img class="mt-1" width="39" src="<?php echo ASSETS_URL;?>/images/tickets/hold-tickets.png" alt="hold-tickets">
                            <h2 class="mt-2 mb-1 fw-normal desk-onHold-label">0 <span class="avg-status">0%</span></h2>
                            <h6 class="mb-0"><?php echo $lang->load(['support','desk','on_hold']);?></h6>
                        </div>
                        <div class="col-6 d-flex align-items-center px-0">
                            <div class="desk-chart on_hold-chart"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 ps-md-1 pb-1 pt-1 pt-md-0 border border-bottom-1 border-bottom-md-0 border-start-md-0 border-top-0">
                    <div class="row gap-0">
                        <div class="col-6">
                            <img class="mt-1" width="39" src="<?php echo ASSETS_URL;?>/images/tickets/open-tickets.png" alt="open-tickets">
                            <h2 class="mt-2 mb-1 fw-normal desk-openTickets-label">0 <span class="avg-status">0%</span></h2>
                            <h6 class="mb-0"><?php echo $lang->load(['support','desk','open_tickets']);?></h6>
                        </div>
                        <div class="col-6 d-flex align-items-center px-0">
                            <div class="desk-chart open_tickets"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 border border-bottom-0 border-bottom border-md-0 border-end-md pt-1 pe-md-1 pb-1 pb-md-0">
                    <div class="row gap-0">
                        <div class="col-6">
                            <img class="mt-1" width="39" src="<?php echo ASSETS_URL;?>/images/tickets/due-tickets.png" alt="due-tickets">
                            <h2 class="mt-2 mb-1 fw-normal desk-dtt-label">0 <span class="avg-status">0%</span></h2>
                            <h6 class="mb-0"><?php echo $lang->load(['support','desk','due_tickets_today']);?></h6>
                        </div>
                        <div class="col-6 d-flex align-items-center px-0">
                            <div class="desk-chart due_tickets_today"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 ps-md-1 pt-1 border border-top-1 border-bottom-0">
                    <div class="row gap-0">
                        <div class="col-6">
                            <img class="mt-1" width="39" src="<?php echo ASSETS_URL;?>/images/tickets/unassigned.png" alt="unassigned">
                            <h2 class="mt-2 mb-1 fw-normal desk-unassigned-label">0 <span class="avg-status">0%</span></h2>
                            <h6 class="mb-0"><?php echo $lang->load(['support','desk','unassigned']);?></h6>
                        </div>
                        <div class="col-6 d-flex align-items-center px-0">
                            <div class="desk-chart unassigned"></div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="unassigned-priorities"></div>
                </div>
            </div>
            <?php
                }
            echo "<table id=\"supportTickets-table\">
                <thead>
                    <tr>
                        <th>{$lang->load(['support','ticket_id'])}</th>
                        <th>{$lang->load(['support','subject'])}</th>
                        <th>{$lang->load(['support','description'])}</th>
                        <th>{$lang->load(['support','status','_label'])}</th>
                        <th>{$lang->load(['support','issue_category'])}</th>
                        <th>{$lang->load(['support','priority','_label'])}</th>
                        <th>{$lang->load(['support','created_at'])}</th>
                        <th>{$lang->load(['support','assigned_to'])}</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>";?>
                <a href="./support/create"><button class="btn btn-success mt-3 w-100"><i class="fa-solid fa-ticket"></i> <?php echo $lang->load(['support','create_ticket']);?></button></a>
            <?php
            }
            if($uri->match('dashboard/support/create')){
            ?>
            <form method="post" novalidate class="createTicketForm" enctype="multipart/form-data">
                <div class="alert alert-danger error-msg"><?php echo $lang->load(['errors','emptyInput']);?></div>
                <input type="hidden" name="main" value="<?php echo base64_encode(URL)?>"/>
                <div class="row">
                    <div class="col">
                        <label for="ticket-subject"><?php echo $lang->load(['support','subject']);?></label>
                        <input type="text" class="form-control" id="ticket-subject" name="ticket-subject"/>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <label for="ticket-description"><?php echo $lang->load(['support','description']);?></label>
                        <textarea class="form-control" id="ticket-description" name="ticket-description"></textarea>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <label for="ticket-attachments"><?php echo $lang->load('attachments',false);?></label>
                        <div class="attachment-manager">
                            <input type="file" multiple class="form-control" accept="image/*" name="ticket-attachments[]" id="ticket-attachments">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <label for="ticket-category"><?php echo $lang->load(['support','issue_category'],false);?></label>
                        <select class="form-select" id="ticket-category" name="ticket-category">
                            <?php
                                $categories = $data->toArray('tickets_categories');
                                foreach($categories['Category'] as $idx=>$category){
                                    $name = $category['@attributes']['name'];
                                    $title = $category['@attributes']['title'];
                                    echo "<option value='".strtolower($name)."'>$title</option>";
                                }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <button name="createTicketSubmit" class="btn btn-success w-100 mt-2"><?php echo $lang->load(['support','create_ticket']);?></button>
                    </div>
                </div>
            </form>
            <?php
            }
            if($uri->match('dashboard/terminal')){
                if($users->isAdmin()){
            ?> 
            <div class="terminal container-fluid" terminal-theme="gnome">
                <select class="form-select terminal-theme-select" name="terminal-theme">
                    <option value="gnome">GNOME</option>
                    <option value="powershell">Powershell</option>
                    <option value="cmdPrompt">Command Prompt</option>
                    <option value="macOS">macOS</option>
                </select>
                <div class="terminal-history">
                    <div class="terminal-input" data-terminal-user="<?php echo get_current_user()?>" data-terminal-host="<?php echo php_uname('n')?>" data-terminal-cwd="<?php echo getcwd();?>">
                        <span class="currentInfo"></span>
                        <span class="cmd"></span>
                    </div>
                </div>
            </div>
            <?php
                }
            }
            if($uri->match('dashboard/devices/tracking')){
                if($users->isAdmin()){
            ?>
            <div id="device-map"></div>
            <?php
                }
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