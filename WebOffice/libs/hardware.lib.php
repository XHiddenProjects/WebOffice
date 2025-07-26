<?php
namespace WebOffice;

use WebOffice\Device;
use WebOffice\Utils;

class Hardware {
    private Device $device;
    private Utils $utils;
    private string $cmd = "python3 ./scripts/hardware.py";

    public function __construct() {
        $this->device = new Device();
        $this->utils = new Utils();
    }
    /**
     * Returns the CPU output
     * @return array
     */
    public function CPU(): array{
        return json_decode($this->utils->executeCommand("$this->cmd --cpu"),true);
    }
    /**
     * Returns battery information
     * @return array
     */
    public function battery():array{
        return json_decode($this->utils->executeCommand("$this->cmd --battery"),true);
    }
    /**
     * Returns fans information
     * @return array
     */
    public function fans(): array{
        return json_decode($this->utils->executeCommand("$this->cmd --fans"),true);
    }
    /**
     * Returns the temperature of the device
     * @param bool $Fahrenheit Use Fahrenheit format (default **true**)
     * @return array
     */
    public function temperature(bool $Fahrenheit=True):array{
        return json_decode($this->utils->executeCommand("$this->cmd --temperature".(!$Fahrenheit ? ' --celsius' : '')),true);
    }

    
}