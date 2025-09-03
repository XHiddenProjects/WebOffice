<?php
namespace WebOffice\tools;
class BBCode{
    private array $bbcode=[];
    public function __construct() {
        $this->bbcode = [
            [
                'pattern'=>'/\[b\](.*?)\[\/b\]/',
                'callback'=>fn($matches): string=>"<strong>$matches[1]</strong>"
            ],
            [
                'pattern'=>'/\[i\](.*)\[\/i\]/',
                'callback'=>fn($matches):string=>"<em>$matches[1]</em>"
            ],
            [
                'pattern'=>'/\[u\](.*?)\[\/u\]/',
                'callback'=>fn($matches):string=>"<u>$matches[1]</u>"
            ],
            [
                'pattern'=>'/\[s\](.*?)\[\/s\]/',
                'callback'=>fn($matches):string=>"<s>$matches[1]</s>"
            ],
            [
                'pattern'=>'/\[sub\](.*?)\[\/sub\]/',
                'callback'=>fn($matches):string=>"<sub>$matches[1]</sub>"
            ],
            [
                'pattern'=>'/\[sup\](.*?)\[\/sup\]/',
                'callback'=>fn($matches):string=>"<sup>$matches[1]</sup>"
            ],
            [
                'pattern'=>'/\[size(=(.*?))?\](.*?)\[\/size\]/',
                'callback'=>function($matches): string{
                        $matches[1] = trim(preg_replace('/^=/','',$matches[1]));
                        if(preg_match('/[0-9]+/',$matches[2])) $matches[2] = trim("$matches[2]%");
                        return "<span style=\"font-size:".trim($matches[2]!=='' ? $matches[2] : 'large')."\">$matches[3]</span>";
                }
            ],
            [
                'pattern'=>'/\[color(=(.*?))?\](.*?)\[\/color\]/',
                'callback'=>fn($matches): string=>"<span style=\"color:".trim($matches[2]!=='' ? $matches[2] : '#000')."\">$matches[3]</span>"
                
            ],
            [
                'pattern'=>'/\[blur(=(.*?))?\](.*?)\[\/blur\]/',
                'callback'=>fn($matches): string=>"<span style=\"color:".trim($matches[2]!=='' ? $matches[2] : '#000')."\">$matches[3]</span>"
                
            ],
            [
                'pattern'=>'/\[url(=(.*?))?\](.*?)\[\/url\]/',
                'callback'=>fn($matches): string=>"<a href=\"".(trim($matches[2]!=='' ? $matches[2] : $matches[3]))."\">".trim($matches[3]!=='' ? $matches[3] : $matches[2])."</a>"
                
            ],
            [
                'pattern'=>'/\[email(=(.*?))?\](.*?)\[\/email\]/',
                'callback'=>fn($matches): string=>"<a href=\"mailto:".(trim($matches[2]!=='' ? $matches[2] : $matches[3]))."\">".trim($matches[3]!=='' ? $matches[3] : $matches[2])."</a>"
            ],
            [
                'pattern'=>'/\[img(=([\d]*)(x([\d]*))?)?\](.*?)\[\/img\]/',
                'callback'=>function($matches): string{
                    $width = $matches[2]?"$matches[2]px" : 'auto';
                    $height = $matches[4]?"$matches[4]px":'auto';
                    return "<img src=\"$matches[5]\" style=\"width:$width;height:$height\"/>";
                }
            ],
            [
                'pattern'=>'/\[bbvideo\](.*?)\[\/bbvideo\]/',
                'callback'=>function($matches): string{
                        preg_match('/\?v=(.+)/',$matches[1],$videoID);
                        return "<iframe src=\"https://www.youtube.com/embed/$videoID[1]\" frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share\" referrerpolicy=\"strict-origin-when-cross-origin\" allowfullscreen></iframe>";
                    }
            ],
            [
                'pattern'=>'/\[quote(=(.*?))?\](.*?)\[\/quote\]/',
                'callback'=>function($matches): string{
                    if($matches[2]==='')
                        return "<blockquote class=\"blockquote\"><p>$matches[3]</p></blockquote>";
                    else
                        return "<figure>
                        <blockquote class=\"blockquote\">
                            <p>$matches[3]</p>
                        </blockquote>
                        <figcaption class=\"blockquote-footer\">
                            <cite title=\"$matches[2]\">$matches[2]</cite>
                        </figcaption>
                    </figure>";
                    
                }
            ],
            [
                'pattern'=>'/\[code(=(.*?))?\]((.|\n)*?)\[\/code\]/',
                'callback'=>function($matches): string{
                    if($matches[2]==='')
                        return "<code class=\"language-none\">$matches[3]</code>";
                    else
                        return "<pre><code class=\"language-$matches[2] line-numbers\">".htmlspecialchars($matches[3])."</code></pre>";
                }
            ],
            [
                'pattern'=>'/\[list(=(.*?))?\]((.|\n)+?)\[\/list\]/',
                'callback'=>function($matches): string{
                    $list='';
                    $items = array_map(fn($i): string=>trim($i),array_values(array_filter(preg_split('/\[\*\](.*?)/',$matches[3]),fn($i):string=>trim($i)!=='')));
                    $i=0;
                    if($matches[2]!==''){
                        if($matches[2]==='1'){
                            $list = "<ol class=\"list-group list-group-numbered\">";
                            do{
                                $list.="<li class=\"list-group-item\">$items[$i]</li>";
                                $i++;
                            }while($i<count($items));
                            $list.="</ol>";
                        }else{
                            $listStyles = [
                                'a'=>'lower-alpha',
                                'A'=>'upper-alpha',
                                'i'=>'lower-roman',
                                'I'=>'upper-roman'
                            ];
                            $list = "<ul class=\"list-group\" style=\"list-style:".trim($listStyles[$matches[2]??$matches[2]])."\">";
                            do{
                                $list.="<li class=\"list-group-item\">$items[$i]</li>";
                                $i++;
                            }while($i<count($items));
                            $list.="</ul>";
                        }
                    }else{
                        $list = "<ul class=\"list-group\">";
                        do{
                            $list.="<li class=\"list-group-item\">$items[$i]</li>";
                            $i++;
                        }while($i<count($items));
                        $list.="</ul>";
                    }

                    return $list;
                }
            ],
            [
                'pattern'=>'/\[br\]/',
                'callback'=>fn(): string=>"<br/>"
            ],
            [
                'pattern'=>'/\[align=(.*?)\](.*?)\[\/align\]/',
                'callback'=>fn($matches): string=>"<p style=\"text-align:".trim($matches[1]??'left').";\">$matches[2]</p>"
            ],
            [
                'pattern'=>'/\[h([\d]{1})\](.*?)\[\/h([\d]{1})\]/',
                'callback'=>fn($matches): string=>"<h$matches[1]>$matches[2]</h$matches[1]>"
            ],
            [
                'pattern'=>'/\[table\]((.|\n)*?)\[\/table\]/',
                'callback'=>function($matches):string{
                    $table = '<table class="table table-striped">';
                    preg_match_all('/\[row\]((.|\n)*?)\[\/row\]/',$matches[1],$rows);
                    $rows = $rows[1];
                    $i=0;
                    foreach($rows as $row){
                        preg_match_all('/\[cell\]((.|\n)*?)\[\/cell\]/',$row,$cells);
                        $cells = $cells[1];
                        switch ($i) {
                            case 0:
                                $table .= "<thead>
                                            <tr>";
                                foreach ($cells as $cell) $table .= "<th>$cell</th>";
                                $table .= "</tr></thead>";
                            break;
                            case 1:
                                // Start tbody only once
                                $table .= '<tbody>';
                                // fall through to add row
                            default:
                                $table .= '<tr>';
                                foreach ($cells as $cell) $table .= "<td>$cell</td>";
                                $table .= '</tr>';
                                break;
                        }
                        $i++;
                    }
                    $table.='</tbody></table>';
                    return $table;
                }
            ]
        ];
    }
    /**
     * Parse BBCode to HTML string
     * @param string $text BBCode
     * @return string HTML String
     */
    public function parse(string $text): string{
        $text = htmlspecialchars($text,ENT_QUOTES);
        // First, run all BBCode replacements
        foreach($this->bbcode as $bbcode) 
            $text = preg_replace_callback($bbcode['pattern'],$bbcode['callback'],$text);
        return $text;
    }
}