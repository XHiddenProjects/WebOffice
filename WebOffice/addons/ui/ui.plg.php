<?php
namespace WebOffice\Addons\Office;
use WebOffice\Addons, WebOffice\Config, WebOffice\Locales;
class UI extends addons{
    private Config $config;
    private string $name = 'ui';
    public function __construct() {
        parent::__construct();
        ini_set('display_errors','1');
        error_reporting(E_ALL);
        $this->config = new Config($this->name);
        $lang = new Locales(implode('-',LANGUAGE), dirname(__FILE__).DS.'languages');
        $this->config->create([
            'name'=>$lang->load()['name'] ?? '',
            'description'=>$lang->load()['description'] ?? '',
            'version'=>$lang->load()['version'] ?? '1.0.0',
            'author'=>$lang->load()['author'] ?? '',
            'enabled'=>true,
            'disabled'=>true
        ]);
    }

    public function css(): string{
        if($this->isEnabled()){
            return "<link rel=\"stylesheet\" href=\"".ADDONS_PATH.DS.$this->name.DS."css".DS.$this->name.".min.css\" type=\"text/css\"/>";
        } 
        else return '';
    }

    public function scripts(): string{
        if($this->isEnabled()){
            return '<script src="'.ADDONS_URL.DS.$this->name.DS.'js'.DS.$this->name.'.min.js" type="text/javascript"></script>';
        }else return '';
    }
}