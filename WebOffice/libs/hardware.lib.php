<?php
namespace WebOffice;

use WebOffice\Device;
use WebOffice\Utils;

class Hardware {
    private Device $device;
    private Utils $utils;
    private string $cmd;
    /**
     * Summary of __construct
     * @param string|null $pythonCompiler Python compiler path. If NULL, the default path will be used (./.venv/bin/python)
     */
    public function __construct(?string $pythonCompiler=null) {
        $this->device = new Device();
        $this->utils = new Utils();
        $this->cmd = $pythonCompiler??dirname(__DIR__)."/.venv/bin/python ".dirname(__DIR__)."/scripts/hardware.py";
    }
    /**
     * Returns the CPU output
     * @return array
     */
    public function CPU(): array{
        return json_decode($this->utils->executeCommand("$this->cmd --cpu")??'{}',true);
    }
    /**
     * Returns battery information
     * @return array
     */
    public function battery():array{
        return json_decode($this->utils->executeCommand("$this->cmd --battery")??'{}',true);
    }
    /**
     * Returns fans information
     * @return array
     */
    public function fans(): array{
        return json_decode($this->utils->executeCommand("$this->cmd --fans")??'{}',true);
    }
    /**
     * Returns the temperature of the device
     * @param bool $Fahrenheit Use Fahrenheit format (default **true**)
     * @return array
     */
    public function temperature(bool $Fahrenheit=True):array{
        return json_decode($this->utils->executeCommand("$this->cmd --temperature".(!$Fahrenheit ? ' --celsius' : ''))??'{}',true);
    }
    /**
     * Returns the GPU _(Graphic Processing Unit)_ information
     * @return array GPU Information
     */
    public function  GPU(): array{
        return json_decode($this->utils->executeCommand("$this->cmd --gpu")??'{}',true);
    }
    /**
     * Returns the Memory information
     * @return array Memory Information
     */
    public function  Memory(): array{
        return json_decode($this->utils->executeCommand("$this->cmd --memory")??'{}',true);
    }
    /**
     * Returns the Processors information
     * @return array Processors Information
     */
    public function  Processor(): array{
        return json_decode($this->utils->executeCommand("$this->cmd --processor")??'{}',true);
    }
}