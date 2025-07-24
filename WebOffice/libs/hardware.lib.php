<?php
namespace WebOffice;

use WebOffice\Device;
use WebOffice\Utils;

class Hardware {
    private Device $device;
    private Utils $utils;

    public function __construct() {
        $this->device = new Device();
        $this->utils = new Utils();
    }

    
}