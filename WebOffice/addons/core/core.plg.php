<?php
namespace WebOffice\Addons\Office;
use WebOffice\Addons, WebOffice\Config, WebOffice\Language;
class Core extends addons{
    private Config $config;
    private string $name = 'core';
    public function __construct() {
        parent::__construct();
        error_reporting(E_ALL);
        $this->config = new Config($this->name);
        $lang = new Language(implode('-',LANGUAGE), dirname(__FILE__).DS.'languages');
        $this->config->create([
            'name'=>$lang->load()['name'] ?? '',
            'description'=>$lang->load()['description'] ?? '',
            'version'=>$lang->load()['version'] ?? '1.0.0',
            'author'=>$lang->load()['author'] ?? '',
            'enabled'=>true,
            'disabled'=>true
        ]);
    }
    public function head(): string{
        $c = new Config();
        $langs = new Language(implode('-',LANGUAGE));
        if($this->isEnabled()){
            return "<title>{$langs->load()['name']}</title>
            <meta name='description' content='{$langs->load()['description']}' />
            <meta name='author' content='{$langs->load()['author']}' />
            <meta name='version' content='{$langs->load()['version']}' />
            <meta name='charset' content='{$c->read('settings','charset')}'/>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <meta name='keywords' content='".implode(',',$langs->load()['keywords'])."'/>";
        } 
        else return '';
    }

    public function css(): string{
        if($this->isEnabled()){
            return '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
            <link rel="stylesheet" href="'.ASSETS_URL.DS.'css'.DS.'main.css" type="text/css"/>';
        } 
        else return '';
    }

    public function scripts(): string{
        if($this->isEnabled()){
            return '<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
            <script src="'.ASSETS_URL.DS.'js'.DS.'definitions.js?base='.urlencode(URL).'" type="text/javascript"></script>
            <script src="'.ASSETS_URL.DS.'js'.DS.'scanner.js" type="text/javascript"></script>
            <script src="'.ASSETS_URL.DS.'js'.DS.'main.js" type="text/javascript"></script>';
        }else return '';
    }
}