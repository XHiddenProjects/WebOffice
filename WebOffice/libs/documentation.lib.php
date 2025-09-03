<?php
namespace WebOffice;
include_once dirname(__DIR__).'/init.php';
use WebOffice\tools\Markdown, WebOffice\Security, WebOffice\Locales;
class Documentation{
    private string $title, $logo, $sectionID, $subSectionID;
    private array $sections;
    private Markdown $md;
    private Security $sec;
    private Locales $lang;
    public function __construct(string $title, string $logo='') {
        $this->title = $title;
        $this->logo = $logo;
        $this->md = new Markdown();
        $this->sec = new Security();
        $this->lang = new Locales(implode('-',LANGUAGE));
    }
    /**
     * Creates a section
     * @param string $id Section ID
     * @param string $label Label for the section
     * @return Documentation
     */
    public function addSection(string $id, string $label): static{
        $this->sections[$id] = ['label'=>$label];
        $this->sectionID = $id;
        return $this;
    }
    /**
     * Selects a section to modify
     * @param string $sectionID Section ID
     * @throws \ErrorException
     * @return Documentation
     */
    public function selectSection(string $sectionID): static{
        if(isset($this->sections[$sectionID]))
            $this->sectionID = $sectionID;
        else throw new \ErrorException("$sectionID doesn't exists");
        return $this;
    }
    /**
     * Selects a section to modify
     * @param string $sectionID Section ID
     * @throws \ErrorException
     * @return Documentation
     */
    public function selectSubsection(string $subSectionID): static{
        if(isset($this->sections[$this->sectionID]['subsections'][$subSectionID]))
            $this->subSectionID = $subSectionID;
        else throw new \ErrorException("$subSectionID doesn't exists");
        return $this;
    }
    /**
     * Adds a subsection to the documentation
     * @param string $id Subsection ID
     * @param string $label Label
     * @param string $icon Font awesome icon
     * @param string $collapseID Set the section in the collapse bar
     * @return Documentation
     */
    public function addSubsection(string $id, string $label, string $icon='', string $collapseID='', ): static {
        if ($collapseID) {
            if (!isset($this->sections[$this->sectionID]['subsections'][$collapseID])) throw new \ErrorException("{$collapseID} doesn't exists");
            // Add to the nested 'subsections' of the collapse
            $this->sections[$this->sectionID]['subsections'][$collapseID]['subsections'][$id] = [
                'type' => 'static',
                'label' => $label,
                'content' => '',
                'icon' => $icon
            ];
        } else {
            // Add directly under the main 'subsections'
            $this->sections[$this->sectionID]['subsections'][$id] = [
                'type' => 'static',
                'label' => $label,
                'content' => '',
                'icon' => $icon
            ];
        }
        $this->subSectionID = $id;
        return $this;
    }
    /**
     * Adds an collapse section
     * @param string $id Subsection ID
     * @param string $label Label
     * @param array $items Array of listed items to the collapse
     * @return Documentation
     */
    public function addCollapseSubsection(string $id, string $label): static{
        $this->sections[$this->sectionID]['subsections'][$id] = ['type'=>'collapse','label'=>$label,'subsections'=>[]];
        $this->subSectionID = $id;
        return $this;
    }
    /**
     * Returns the array structure of the documentation
     * @return array
     */
    public function structure(): array {
        // Recursive function to escape strings in the array
        $escapeArray = function(array $arr) use (&$escapeArray): array {
            $escaped = [];
            foreach ($arr as $key => $value) {
                if (is_array($value)) {
                    $escaped[$key] = $escapeArray($value);
                } elseif (is_string($value)) {
                    $escaped[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                } else {
                    $escaped[$key] = $value;
                }
            }
            return $escaped;
        };
        return $escapeArray($this->sections);
    }
    /**
     * Adds content to the documentation
     * @param string $content Content
     * @return Documentation
     */
    public function addContent(string $content): static {
        // Recursive function to find and update the subsection by ID
        $updateSubsectionContent = function(array &$subsections) use (&$updateSubsectionContent, $content) {
            foreach ($subsections as $id => &$subsection) {
                if ($subsection['type'] === 'static') {
                    // Check if current key matches the selected subSectionID
                    if ($id === $this->subSectionID) {
                        $subsection['content'] .= $this->md->parse($this->sec->preventXSS($content));
                    }
                } elseif ($subsection['type'] === 'collapse') {
                    // Recurse into nested 'subsections'
                    if (isset($subsection['subsections'])) {
                        $updateSubsectionContent($subsection['subsections']);
                    }
                }
            }
        };

        // Loop through all sections and their subsections
        foreach ($this->sections as &$section) {
            if (isset($section['subsections'])) {
                $updateSubsectionContent($section['subsections']);
            }
        }

        return $this;
    }
    /**
     * Import content from a Markdown file
     * @param string $file
     * @return Documentation
     */
    public function importContent(string $file):static{
        $file = preg_replace('/\.md$/','',$file).".md";
        if (!file_exists($file)) throw new \ErrorException("File {$file} does not exist.");    
        $this->addContent(file_get_contents($file));
        return $this;
    }
    /**
     * Parses string to ID
     * @param string $section Section ID
     * @param string $id Subsection ID
     * @return string Parsed ID
     */
    private function parseID(string $section, string $id): string{
        $section = preg_replace('/ /','-',strtolower($section));
        $id = preg_replace('/ /','-',strtolower($id));
        return "?page=$section|$id";
    }
    /**
     * Returns the command lines to the documentation
     * @param array<array:{label:string,cmd:string}> $cmds Associated command lines
     * @return string
     */
    public function addCodeBlock(array $docs): string {
        $out = '<div class="documentation-codeblock">';
        $out .= '<div class="tabs">';

        // Find the first language and its first command for default selection
        $firstLang = null;
        $firstCmdLabel = null;
        $firstCmd = null;
        foreach ($docs as $lang => $commands) {
            if ($firstLang === null) {
                $firstLang = $lang;
                if (is_array($commands)) {
                    foreach ($commands as $cmdLabel => $command) {
                        $firstCmdLabel = $cmdLabel;
                        $firstCmd = $command;
                        break;
                    }
                } else {
                    $firstCmdLabel = $lang;
                    $firstCmd = $commands;
                }
            }
        }
        $out.='<div class="tabs-label">';
        foreach ($docs as $lang => $commands) {
            // Normalize commands: handle if string or array
            if (is_array($commands)) {
                foreach ($commands as $cmdLabel => $command) {
                    $escapedCommand = htmlspecialchars($command);
                    $selected = ($lang === $firstLang && $cmdLabel === $firstCmdLabel) ? ' selected' : '';
                    $out .= '<button data-lang="' . htmlspecialchars($lang) . '" data-command="' . $escapedCommand . '"' . $selected . '>' . htmlspecialchars($cmdLabel) . '</button>';
                }
            } else {
                $escapedCommand = htmlspecialchars($commands);
                $selected = ($lang === $firstLang) ? ' selected' : '';
                $out .= '<button data-lang="' . htmlspecialchars($lang) . '" data-command="' . $escapedCommand . '"' . $selected . '>' . htmlspecialchars($lang) . '</button>';
            }
        }
        $out.='</div>
        <div class="docs-toolbars">
            <button type="button" data-copy title="Copy code"><i class="fa-solid fa-copy"></i></button>
        </div>';

        $out .= '</div>
        <div class="code-line">
            <code class="code-content language-' . htmlspecialchars($firstLang) . '">' . htmlspecialchars($firstCmd) . '</code>
        </div>
        </div>';

        return $out;
    }
    /**
     * Publishes the documentation
     * @return string
     */
    public function publish(): string{
        $out = "<div class='documentation'>
            <nav class='documentation-navbar'>
                <div class='container-fluid'>
                    <a class='brand' href='./'>
                        ".($this->logo ? "<img src='{$this->logo}' alt='Logo' width='30' height='24' class='d-inline-block align-text-top'>" : "")."
                        {$this->title}
                    </a>
                    <form class='documentation-search' role='search'>
                        <div class='documentation-search-icon'><i class='fa-solid fa-magnifying-glass'></i></div>
                        <input class='documentation-searchbar' name='doc-search' type='search' placeholder='Search...' aria-label='Search'/>
                    </form>
                    <div class='documentation-hidden'></div>
                </div>
            </nav>
            <div class='documentation-container'>
                <div class='documentation-sidenav'>
                    <ul class='documentation-section'>
                        ";
                    foreach($this->sections as $sectionID=>$sections){
                        $out.="<li class='documentation-section-item'><p class='documentation-section-label'>{$sections['label']}</p>";
                        if(!empty($sections['subsections'])){
                            $out.='<ul class="documentation-subsection">';
                            foreach($sections['subsections'] as $id => $subsections){
                                if(strtolower($subsections['type'])==='static')
                                    $out.= "<a class='documentation-subsection-item' href='".$this->parseID($sectionID,$id)."'><li>".(isset($subsections['icon']) && $subsections['icon'] !== '' ? "{$subsections['icon']} " : '<i class="documentation-icon-placeholder"></i>')."{$subsections['label']}</li></a>";
                                if(strtolower($subsections['type'])==='collapse'){
                                    $out.="<div class='collapse'>
                                        <p class='collapse-title'>{$subsections['label']}</p>
                                        <div class='collapse-body'>
                                            <ul class='documentation-subsection'>
                                                ";
                                        foreach($subsections['subsections'] as $subId => $subsections)
                                            $out.="<a class='documentation-subsection-item' href='".$this->parseID($sectionID,$subId)."'><li>".(isset($subsections['icon']) && $subsections['icon'] !== '' ? "{$subsections['icon']} " : '<i class="documentation-icon-placeholder"></i>')."{$subsections['label']}</li></a>";
                                        
                                    $out.="
                                            </ul>
                                        </div>
                                    </div>";
                                }
                            }
                            $out.='</ul>';
                        }
                        $out.='</li>';
                    }
                $out.="
                    </ul>
                </div>
                <div class='documentation-body'>
                ";
                    foreach($this->sections as $sectionID=>$sections){
                        if(!empty($sections['subsections'])){
                            foreach($sections['subsections'] as $id => $subsections){
                                if(strtolower($subsections['type'])==='static' && isset($subsections['content']) && $subsections['content'] !== '')
                                    $out.="<div class='documentation-content' tabindex='0' data-bs-spy='scroll' data-bs-target='.page-nav-link' data-bs-offset='0' data-bs-smooth-scroll='true' id='".preg_replace('/^\?page=/','',$this->parseID($sectionID,$id))."'>".$subsections['content']."</div>";
                                if(strtolower($subsections['type'])==='collapse' && isset($subsections['subsections'])){
                                    foreach($subsections['subsections'] as $subId => $nestedSubsection){
                                        if(strtolower($nestedSubsection['type'])==='static' && isset($nestedSubsection['content']) && $nestedSubsection['content'] !== '')
                                            $out.="<div class='documentation-content' tabindex='0' data-bs-spy='scroll' data-bs-target='.page-nav-link' data-bs-offset='0' data-bs-smooth-scroll='true' id='".preg_replace('/^\?page=/','',$this->parseID($sectionID,$subId))."'>".$nestedSubsection['content']."</div>";
                                    }
                                }
                            }
                        }
                    }
                    if(empty($this->sections)){
                        $out.="<div class='documentation-section'></div>
                            <h2 class='documentation-section-title'>No documentation available</h2>
                            <div class='documentation-content'>Please create a section to start documenting your application.</div>";
                    }
                $out.="
                </div>
                <div class='page-nav  flex-column'>
                    <p class='text-muted fw-bold'>{$this->lang->load(['documentation','on_this_page'],false)}</p>
                    <div class='d-flex'>
                        <div class='spyscroll'>
                            <span class='spyscroll-bar'></span>
                        </div>
                        <div class='page-nav-link'></div>
                    </div>
                </div>
            </div>
        </div>";
        return $out;
    }

}