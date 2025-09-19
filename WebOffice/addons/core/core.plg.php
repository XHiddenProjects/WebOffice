<?php
namespace WebOffice\Addons\Office;
use WebOffice\Addons, WebOffice\Config, WebOffice\Locales, WebOffice\URI;
class Core extends addons{
    private Config $config;
    private string $name = 'core';
    private URI $uri;
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
            'disabled'=>true,
            'permissions'=>['fileSystem','activeTab']
        ]);
        $this->uri = new URI();
    }
    public function head(): string{
        $c = new Config();
        $langs = new Locales(implode('-',LANGUAGE));
        if($this->isEnabled()){
            $paths = $this->uri->arrPath($_SERVER['REQUEST_URI']);
            $extra = "";
            if(isset($paths[1])){
                switch(strtolower($paths[1])){
                    case 'auth':
                        $extra=" - {$langs->load()['authorization']['_tab']}";
                    break;
                    default:break;
                }
            }
            return "<title>{$langs->load()['name']}{$extra}</title>
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
            $out = "<link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css\" rel=\"stylesheet\" integrity=\"sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr\" crossorigin=\"anonymous\">
            <link rel=\"stylesheet\" href=\"".ASSETS_URL.DS."css".DS."prism.min.css\" type=\"text/css\"/>
            ";
            foreach(array_diff(scandir(THEMES_PATH),['.','..']) as $themes){
                $c = new Config();
                if(strcasecmp($themes,$c->read('settings','theme'))==0){
                    $out.="<link rel=\"stylesheet\" href=\"".THEMES_URL.DS.$themes.DS."main.css\"/>
                    <link rel=\"stylesheet\" href=\"".THEMES_URL.DS.$themes.DS."documentation.css\"/>
                    <link rel=\"stylesheet\" href=\"".THEMES_URL.DS.$themes.DS."weboffice.min.css\"/>";
                }
            }
            $out.="
            <link rel=\"stylesheet\" href=\"".ASSETS_URL.DS."css".DS."all.min.css\" type=\"text/css\"/>
            <link rel=\"stylesheet\" href=\"".ASSETS_URL.DS."css".DS."animation.css\" type=\"text/css\"/>
            <link rel=\"stylesheet\" href=\"".ASSETS_URL.DS."css".DS."passwordTools.min.css\" type=\"text/css\"/>";
            return $out;
        } 
        else return '';
    }

    public function scripts(): string{
        if($this->isEnabled()){
            return '<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script src="'.ASSETS_URL.DS.'js'.DS.'definitions.js?base='.urlencode(URL).'" type="text/javascript"></script>
            <script src="'.ASSETS_URL.DS.'js'.DS.'animate.js"></script>
            <script src="'.ASSETS_URL.DS.'js'.DS.'scanner.js" type="text/javascript"></script>
            <script src="'.ASSETS_URL.DS.'js'.DS.'md5.min.js" type="text/javascript"></script>
            <script src="'.ASSETS_URL.DS.'js'.DS.'generators.min.js" type="text/javascript"></script>
            <script src="'.ASSETS_URL.DS.'js'.DS.'main.js" type="text/javascript"></script>
            <script src="'.ASSETS_URL.DS.'js'.DS.'requests.js" type="text/javascript"></script>
            <script src="'.ASSETS_URL.DS.'js'.DS.'prism.min.js" type="text/javascript"></script>
            <script src="'.ADDONS_URL.DS.$this->name.DS.'js'.DS.$this->name.'.js" type="text/javascript"></script>
            <script src="'.ASSETS_URL.DS.'js'.DS.'formvalidate.js" type="text/javascript"></script>
            <script src="'.ASSETS_URL.DS.'js'.DS.'passwordTools.min.js" type="text/javascript"></script>
            <script src="'.ASSETS_URL.DS.'js'.DS.'timeformat.min.js" type="text/javascript"></script>
            <script src="'.ASSETS_URL.DS.'js'.DS.'weboffice.min.js" type="text/javascript"></script>';
        }else return '';
    }

    public function footer(): string{
        if($this->isEnabled()){
            $l = new Locales(implode('-',LANGUAGE));
            return "<div class='w-100 d-flex justify-content-between'>
                <div class='footer-tab'>
                    <h3>{$l->load(['footer','quick_links'])}</h3>
                    <div class='d-flex justify-content-around flex-column'>
                        <a href='".URL."' class='link-dark link-underline-opacity-0'>{$l->load(['home'])}</a>
                        <a href='".URL.DS."documentation' class='link-dark link-underline-opacity-0'>{$l->load(['documentation','_tab'])}</a>
                    </div>
                </div>
                <div class='footer-tab'>
                    <h3>{$l->load(['footer','support'])}</h3>
                    <div class='d-flex justify-content-around flex-column'>
                        <a href='".URL.DS."policies".DS."terms-and-conditions' class='link-dark link-underline-opacity-0'>{$l->load(['terms_and_conditions'])}</a>
                        <a href='".URL.DS."policies".DS."privacy-policy' class='link-dark link-underline-opacity-0'>{$l->load(['privacy_policy'])}</a>
                        <a href='".URL.DS."policies".DS."legal' class='link-dark link-underline-opacity-0'>{$l->load(['legal'])}</a>
                    </div>
                </div>
                <div class='footer-tab'>
                    <h3>{$l->load(['footer','follow_us'])}</h3>
                    <div class='d-flex flex-wrap'>
                        <a data-bs-toggle='tooltip' target='_blank' data-bs-placement='top' data-bs-title='{$l->load(['socials','github'])}' href='https://github.com/XHiddenProjects/WebOffice' class='me-2 link-dark link-underline-opacity-0 social-bubble social-github'><i class='fa-brands fa-github'></i></a>
                        <a data-bs-toggle='tooltip' target='_blank' data-bs-placement='top' data-bs-title='{$l->load(['socials','discord'])}' href='https://discord.com/users/948025245787373608' class='me-2 link-dark link-underline-opacity-0 social-bubble social-discord'><i class='fa-brands fa-discord'></i></a>
                    </div>
                </div>
            </div><hr class='border-2 border-dark rounded'/><p class='text-center'>&copy2025".(date('Y')==2025 ? '' : " - ".date('Y'))." XHiddenProjects</p>";
        }else return "";
    }

}