<?php

/**
 * @package Shownotes
 * @version 0.2.3
 */

/*
Plugin Name: Shownotes
Plugin URI: http://shownot.es/wp-plugin/
Description: Convert OSF-Shownotes to HTML for your Podcast
Author: Simon Waldherr
Version: 0.2.3
Author URI: http://waldherr.eu
License: MIT License
*/

include_once('settings.php');
$shownotes_options = get_option('shownotes_options');

function shownotesshortcode_add_styles() {
    global $shownotes_options;
    if(!isset($shownotes_options['css_id'])) {return false;}
    if($shownotes_options['css_id'] == '0') {return false;}
    
    $css_styles = array(''
                       ,'style_one'
                       ,'style_two');
    
    wp_enqueue_style( 'shownotesstyle', plugins_url('static/'.$css_styles[$shownotes_options['css_id']].'.css', __FILE__), array(), '0.2.3' );
}
add_action( 'wp_print_styles', 'shownotesshortcode_add_styles' );

function add_shownotes_textarea($post) {
    global $shownotes_options;
    $post_id = @get_the_ID();
    if ($post_id == '') {
        return;
    }
    if (isset($post_id)) {
        $shownotes = get_post_meta($post_id, '_shownotes', true);
        if($shownotes == "") {
            $shownotes = get_post_meta($post_id, 'shownotes', true);
        }
    } else {
        $shownotes = '';
    }
    $baseurl = 'http://tools.shownot.es/showpadapi/?id=$$$';
    $baseurlstring = '';

    $import_podcastname = false;
    if(isset($shownotes_options['import_podcastname'])) {
        if(trim($shownotes_options['import_podcastname']) != "") {
            $import_podcastname = trim($shownotes_options['import_podcastname']);
        }
    }
    
    if($import_podcastname == false) {
        $baseurlstring = '<input type="text" id="importId" name="" class="form-input-tip" size="16" autocomplete="off" value=""> <input type="button" class="button" onclick="importShownotes(document.getElementById(\'shownotes\'), document.getElementById(\'importId\').value, \'' . $baseurl . '\')" value="Import">';
    } else {
        $baseurlstring = '<select id="importId" size="1"></select> <input type="button" class="button" onclick="importShownotes(document.getElementById(\'shownotes\'), document.getElementById(\'importId\').value, \'' . $baseurl . '\')" value="Import"><script>getPadList(document.getElementById(\'importId\'),\''.$import_podcastname.'\')</script>';
    }

    echo '<div id="add_shownotes" class="shownotesdiv"><p><textarea id="shownotes" name="shownotes" style="height:280px" class="large-text">' . $shownotes . '</textarea></p> <p>ShowPad Import: ' . $baseurlstring . ' &#124; Preview: <input type="button" class="button" onclick="previewPopup(document.getElementById(\'shownotes\'), \'html\')" value="HTML"> <input type="button" class="button" onclick="previewPopup(document.getElementById(\'shownotes\'), \'chapter\')" value="Chapter"> </p></div>';
}

function save_shownotes() {
    $post_id = @get_the_ID();
    if ($post_id == '') {
        return;
    }
    $old = get_post_meta($post_id, '_shownotes', true);
    if (isset($_POST['shownotes'])) {
        $new = $_POST['shownotes'];
    } else {
        $new = '';
    }

    $shownotes = $old;
    if ($new && $new != $old) {
        update_post_meta($post_id, '_shownotes', $new);
        delete_post_meta($post_id, 'shownotes');
        $shownotes = $new;
    } elseif ('' == $new && $old) {
        delete_post_meta($post_id, '_shownotes', $old);
    }
}

add_action('add_meta_boxes', function() {
    $screens = array(
        'post',
        'page',
        'podcast'
    );
    foreach ($screens as $screen) {
        add_meta_box('shownotesdiv-', __('Shownotes', 'podlove'), 'add_shownotes_textarea', $screen, 'advanced', 'default');
    }
});

add_action('save_post', 'save_shownotes');


function osf_shownotes_shortcode($atts, $content = "") {
    global $shownotes_options;
    $export    = '';
    $post_id   = get_the_ID();
    $shownotes = get_post_meta($post_id, '_shownotes', true);

    if(isset($shownotes_options['main_tags'])) {
        $default_tags = trim($shownotes_options['main_tags']);
    } else {
        $default_tags = '';
    }

    extract(shortcode_atts(array(
       'template' => $shownotes_options['main_mode'],
       'mode' => $shownotes_options['main_mode'],
       'tags' => $default_tags
    ), $atts));

    if (($content !== "") || ($shownotes)) {
        if (isset($shownotes_options['affiliate_amazon']) && $shownotes_options['affiliate_amazon'] != '') {
            $amazon = $shownotes_options['affiliate_amazon'];
        } else {
            $amazon = 'shownot.es-21';
        }
        if (isset($shownotes_options['affiliate_thomann']) && $shownotes_options['affiliate_thomann'] != '') {
            $thomann = $shownotes_options['affiliate_thomann'];
        } else {
            $thomann = '93439';
        }
        if (isset($shownotes_options['affiliate_tradedoubler']) && $shownotes_options['affiliate_tradedoubler'] != '') {
            $tradedoubler = $shownotes_options['affiliate_tradedoubler'];
        } else {
            $tradedoubler = '16248286';
        }

        $fullmode = 'false';

        if($tags == '') {
            $fullmode = 'true';
            $fullint  = 2;
            $tags = explode(' ', 'chapter section spoiler topic embed video audio image shopping glossary source app title quote link podcast news');
        } else {
            $fullint  = 1;
            $tags = explode(' ', $tags);
        }

        $data = array(
            'amazon' => $amazon,
            'thomann' => $thomann,
            'tradedoubler' => $tradedoubler,
            'fullmode' => $fullmode,
            'tags' => $tags
        );

        //undo fucking wordpress shortcode cripple shit
        if ($content !== "") {
            $shownotesString = htmlspecialchars_decode(str_replace('<br />', '', str_replace('<p>', '', str_replace('</p>', '', $content))));
        } else {
            $shownotesString = "\n" . $shownotes . "\n";
        }

        //parse shortcode as osf string to html
        if($template !== $shownotes_options['main_mode']) {
            $mode = $template;
        }
        $shownotesArray = osf_parser($shownotesString, $data);
        if($mode == 'block style') {
            $export     = osf_export_anycast($shownotesArray['export'], $fullint);
        } elseif($mode == 'list style') {
            $export     = osf_export_wikigeeks($shownotesArray['export'], $fullint);
        } elseif($mode == 'glossary') {
            $export     = osf_export_glossary($shownotesArray['export'], $fullint);
        } elseif(($mode == 'shownoter')||($mode == 'podcaster')) {
            if(isset($shownotesArray['header'])) {
                if($mode == 'shownoter') {
                    $export = osf_get_persons('shownoter', $shownotesArray['header']);
                } elseif($mode == 'podcaster') {
                    $export = osf_get_persons('podcaster', $shownotesArray['header']);
                }
                
            }
        }
        if(isset($_GET['debug'])) {
            $export .= '<textarea>'.print_r($shownotes_options, true).htmlspecialchars($shownotesString).'</textarea>';
        }
    }
    return $export;
}

function md_shownotes_shortcode($atts, $content = "") {
    $post_id   = get_the_ID();
    $shownotes = get_post_meta($post_id, '_shownotes', true);
    if($shownotes == "") {
        $shownotes = get_post_meta($post_id, 'shownotes', true);
    }
    if ($content !== "") {
        $shownotesString = htmlspecialchars_decode(str_replace('<br />', '', str_replace('<p>', '', str_replace('</p>', '', $content))));
    } else {
        $shownotesString = "\n" . $shownotes . "\n";
    }
    return markdown($shownotesString);
}

if(!isset($shownotes_options['main_osf_shortcode'])) {
    $osf_shortcode = 'shownotes';
} else {
    $osf_shortcode = $shownotes_options['main_osf_shortcode'];
}

if(!isset($shownotes_options['main_md_shortcode'])) {
    $md_shortcode = 'md-shownotes';
} else {
    $md_shortcode = $shownotes_options['main_md_shortcode'];
}

add_shortcode($md_shortcode, 'md_shownotes_shortcode');
add_shortcode($osf_shortcode, 'osf_shownotes_shortcode');
if($osf_shortcode != 'osf-shownotes') {
    add_shortcode('osf-shownotes', 'osf_shownotes_shortcode');
}

function shownotesshortcode_add_scripts() {
    wp_enqueue_script( 
        'importPad', 
        plugins_url('static/shownotes.js', __FILE__), 
        array(), '0.2.3', false
    );
    wp_enqueue_script( 
        'tinyosf', 
        plugins_url('static/tinyOSF/tinyosf.js', __FILE__), 
        array(), '0.2.3', false
    );
    wp_enqueue_script( 
        'tinyosf_exportmodules', 
        plugins_url('static/tinyOSF/tinyosf_exportmodules.js', __FILE__), 
        array(), '0.2.3', false
    );
}
if (is_admin()) {
    add_action('wp_print_scripts', 'shownotesshortcode_add_scripts');
}

function osf_specialtags($needles, $haystack) {
    // Eine Funktion um Tags zu filtern
    $return = false;
    if (is_array($needles)) {
        foreach ($needles as $needle) {
            if (array_search($needle, $haystack) !== false) {
                $return = true;
            }
        }
    }
    return $return;
}

function osf_affiliate_generator($url, $data) {
    // Diese Funktion wandelt Links zu Amazon, Thomann und iTunes in Affiliate Links um
    $amazon       = $data['amazon'];
    $thomann      = $data['thomann'];
    $tradedoubler = $data['tradedoubler'];

    if ((strstr($url, 'www.amazon.de/') && strstr($url, 'p/')) && ($amazon != '')) {
        if (strstr($url, "dp/")) {
            $pid = substr(strstr($url, "dp/"), 3, 10);
        } elseif (strstr($url, "gp/product/")) {
            $pid = substr(strstr($url, "gp/product/"), 11, 10);
        } else {
            $pid = '';
        }
        $aid  = '?ie=UTF8&linkCode=as2&tag=' . $amazon;
        $purl = 'http://www.amazon.de/gp/product/' . $pid . '/' . $aid;
    } elseif ((strstr($url, 'www.amazon.com/') && strstr($url, 'p/')) && ($amazon != '')) {
        if (strstr($url, "dp/")) {
            $pid = substr(strstr($url, "dp/"), 3, 10);
        } elseif (strstr($url, "gp/product/")) {
            $pid = substr(strstr($url, "gp/product/"), 11, 10);
        } else {
            $pid = '';
        }
        $aid  = '?ie=UTF8&linkCode=as2&tag=' . $amazon;
        $purl = 'http://www.amazon.com/gp/product/' . $pid . '/' . $aid;
    } elseif ((strstr($url, 'thomann.de/de/')) && ($thomann != '')) {
        $thomannurl = explode('.de/', $url);
        $purl       = 'http://www.thomann.de/index.html?partner_id=' . $thomann . '&page=/' . $thomannurl[1];
    } elseif ((strstr($url, 'itunes.apple.com/de')) && ($tradedoubler != '')) {
        if (strstr($url, '?')) {
            $purl = 'http://clkde.Tradedoubler.com/click?p=23761&a=' . $tradedoubler . '&url=' . urlencode($url . '&partnerId=2003');
        } else {
            $purl = 'http://clkde.Tradedoubler.com/click?p=23761&a=' . $tradedoubler . '&url=' . urlencode($url . '?partnerId=2003');
        }
    } else {
        $purl = $url;
    }

    return $purl;
}

function osf_convert_time($string) {
    // Diese Funktion wandelt Zeitangaben vom Format 01:23:45 (H:i:s) in Sekundenangaben um
    $strarray = explode(':', $string);
    if (count($strarray) == 3) {
        return (($strarray[0] * 3600) + ($strarray[1] * 60) + $strarray[2]);
    } elseif (count($strarray) == 2) {
        return (($strarray[1] * 60) + $strarray[2]);
    }
}

function osf_time_from_timestamp($utimestamp) {
    // Diese Funktion wandelt Zeitangaben im UNIX-Timestamp Format in relative Zeitangaben im Format 01:23:45 um
    global $osf_starttime;
    if (strpos($utimestamp, ':') != false) {
        $pause         = explode(':', $utimestamp);
        $osf_starttime = $osf_starttime + $pause[1] - $pause[0];
    }
    $duration = $utimestamp - $osf_starttime;
    $sec      = $duration % 60;
    if ($sec < 10) {
        $sec = '0' . $sec;
    }
    $min = $duration / 60 % 60;
    if ($min < 10) {
        $min = '0' . $min;
    }
    $hour = $duration / 3600 % 24;
    if ($hour < 10) {
        $hour = '0' . $hour;
    }
    return "\n" . $hour . ':' . $min . ':' . $sec;
}

function osf_replace_timestamps($shownotes) {
    // Durchsucht die Shownotes nach Zeitangaben (UNIX-Timestamp) und übergibt sie an die Funktion osf_time_from_timestamp()
    global $osf_starttime;
    preg_match_all('/\n[0-9]{9,15}/', $shownotes, $unixtimestamps);
    $osf_starttime = @$unixtimestamps[0][0];
    $regexTS       = array(
        '/\n[0-9:]{9,23}/e',
        'osf_time_from_timestamp(\'\\0\')'
    );
    return preg_replace($regexTS[0], $regexTS[1], $shownotes);
}

function osf_parse_person($string) {
    $profileurl  = false;
    $name        = '';
    $urlmatch   = preg_match_all('/\<(http[\S]+)\>/', $string, $url);
    if($urlmatch != 0 && $urlmatch != false) {
        $profileurl = $url[1][0];
        $name = trim(preg_replace('/\<(http[\S]+)\>/', '', $string));
    } else {
        if(strpos($string, '@:adn') != false) {
            preg_match_all('/@(\(?[\S]+\)?)@:adn/', $string, $url);
            $profileurl = 'https://alpha.app.net/'.$url[1][0];
            $name = trim($url[1][0]);
        } elseif(strpos($string, '@') != false) {
            preg_match_all('/@(\(?[\S]+\)?)/', $string, $url);
            $profileurl = 'https://twitter.com/'.$url[1][0];
            $name = trim($url[1][0]);
        } else {
            $name = trim($string);
        }
    }
    $return['url']  = $profileurl;
    $return['name'] = trim($name,' \s:;()"');
    return $return;
}

function osf_get_persons($persons, $header) {
    $regex['shownoter'] = '/(Shownoter|Zusammengetragen)[^:]*:([ \S]*)/';
    $regex['podcaster'] = '/(Podcaster|Zusammengetragen)[^:]*:([ \S]*)/';
    preg_match_all($regex[$persons], $header, $persons);
    $persons = preg_split('/(,|und)/', $persons[2][0]);
    $personsArray = array();
    $i = 0;
    foreach($persons as $person) {
        $personArray = osf_parse_person($person);
        if($personArray['url'] == false) {
            $personsArray[$i]  = '<span>'.$personArray['name'].'</span>';
        } else {
            $personsArray[$i]  = '<a target="_blank" href="'.$personArray['url'].'">'.$personArray['name'].'</a>';
        }
        $i++;
    }
    return implode(', ', $personsArray);
}

function osf_parser($shownotes, $data) {
    // Diese Funktion ist das Herzstück des OSF-Parsers
    $specialtags = $data['tags'];
    $exportall   = $data['fullmode'];

    // entferne alle Angaben vorm und im Header
    $splitAt = false;
    if(strpos($shownotes, '/HEADER')) {
        $splitAt = '/HEADER';
    } elseif(strpos($shownotes, '/HEAD')) {
        $splitAt = '/HEAD';
    }

    if($splitAt != false) {
        $shownotes = explode($splitAt, $shownotes, 2);
    } else {
        $shownotes = preg_split("/(\n\s*\n)/", $shownotes,2);
    }
    if(count($shownotes)!=1) {
        $header    = $shownotes[0];
        $shownotes = $shownotes[1];
    } else {
        $shownotes = $shownotes[0];
    }

    // wandle Zeitangaben im UNIX-Timestamp Format in relative Zeitangaben im Format 01:23:45 um
    $shownotes = "\n" . osf_replace_timestamps("\n" . $shownotes);

    // zuerst werden die regex-Definitionen zum erkennen von Zeilen, Tags, URLs und subitems definiert
    $pattern['zeilen']  = '/(((\d\d:)?\d\d:\d\d)(\\.\d\d\d)?)*(.+)/';
    $pattern['tags']    = '((\s#)(\S*))';
    $pattern['urls']    = '(\s+((http(|s)://\S{0,256})\s))';
    $pattern['urls2']   = '(\<((http(|s)://\S{0,256})>))';
    $pattern['kaskade'] = '/^([\t ]*-+ )/';

    // danach werden mittels des zeilen-Patterns die Shownotes in Zeilen/items geteilt
    preg_match_all($pattern['zeilen'], $shownotes, $zeilen, PREG_SET_ORDER);

    // Zählvariablen definieren
    // i = item, lastroot = Nummer des letzten Hauptitems, kaskadei = Verschachtelungstiefe
    $i                             = 0;
    $lastroot                      = 0;
    $kaskadei                      = 0;
    $returnarray['info']['zeilen'] = 0;

    // Zeile für Zeile durch die Shownotes gehen
    foreach ($zeilen as $zeile) {
        // Alle Daten der letzten Zeile verwerfen
        unset($newarray);

        // Text der Zeile in Variable abspeichern und abschließendes Leerzeichen anhängen
        $text = $zeile[5] . ' ';

        // Mittels regex tags und urls extrahieren
        preg_match_all($pattern['tags'], $text, $tags, PREG_PATTERN_ORDER);
        preg_match_all($pattern['urls'], $text, $urls, PREG_PATTERN_ORDER);
        preg_match_all($pattern['urls2'], $text, $urls2, PREG_PATTERN_ORDER);

        // array mit URLs im format <url> mit array mit URLs im format  url  zusammenführen
        $urls = array_merge($urls[2], $urls2[2]);

        // Zeit und Text in Array zur weitergabe speichern
        $newarray['time'] = $zeile[1];
        $regex['search'] = array('/\s&quot;/', '/&quot;\s/', '/ - /');
        $regex['replace'] = array(' &#8222;', '&#8221; ', ' &#8209; ');
        $newarray['text'] = trim(preg_replace($regex['search'], $regex['replace'], ' '.htmlentities(preg_replace(array(
            $pattern['tags'],
            $pattern['urls'],
            $pattern['urls2']
        ), array(
            '',
            '',
            ''
        ), $zeile[5]), ENT_QUOTES, 'UTF-8').' '));
        $newarray['orig'] = trim(preg_replace(array(
            $pattern['tags'],
            $pattern['urls'],
            $pattern['urls2']
        ), array(
            '',
            '',
            ''
        ), $zeile[5]));

        // Wenn Tags vorhanden sind, diese ebenfalls im Array speichern
        $newarray['chapter'] = false;
        if (count($tags[2]) > 0) {
            foreach ($tags[2] as $tag) {
                if (strlen($tag) === 1) {
                    switch ($tag) {
                        case 'c':
                            $newarray['tags'][] = 'chapter';
                            break;
                        case 'g':
                            $newarray['tags'][] = 'glossary';
                            break;
                        case 'l':
                            $newarray['tags'][] = 'link';
                            break;
                        case 's':
                            $newarray['tags'][] = 'section';
                            break;
                        case 't':
                            $newarray['tags'][] = 'topic';
                            break;
                        case 'v':
                            $newarray['tags'][] = 'video';
                            break;
                        case 'a':
                            $newarray['tags'][] = 'audio';
                            break;
                        case 'i':
                            $newarray['tags'][] = 'image';
                            break;
                    }
                } else {
                    $newarray['tags'] = $tags[2];
                }
            }
            if (((@in_array("Chapter", $newarray['tags'])) || (@in_array("chapter", $newarray['tags']))) && ($newarray['time'] != '')) {
                $newarray['chapter'] = true;
            }
        }

        // Wenn URLs vorhanden sind, auch diese im Array speichern
        if (count($urls) > 0) {
            $purls = array();
            foreach ($urls as $url) {
                $purls[] = osf_affiliate_generator($url, $data);
            }
            $newarray['urls']   = $purls;
        }

        // Wenn Zeile mit "- " beginnt im Ausgabe-Array verschachteln
        if ((preg_match($pattern['kaskade'], $zeile[0])) || (!preg_match('/(\d\d:\d\d:\d\d)/', $zeile[0])) || (!$newarray['chapter'])) {
            if (isset($newarray['tags'])) {
                if ((osf_specialtags($newarray['tags'], $specialtags)) || ($exportall == 'true')) {
                    if (preg_match($pattern['kaskade'], $zeile[0])) {
                        $newarray['subtext']                                     = true;
                        $returnarray['export'][$lastroot]['subitems'][$kaskadei] = $newarray;
                    } else {
                        $returnarray['export'][$lastroot]['subitems'][$kaskadei] = $newarray;
                    }
                } else {
                    unset($newarray);
                }
            } elseif ($exportall == 'true') {
                if (preg_match($pattern['kaskade'], $zeile[0])) {
                    $newarray['subtext']                                     = true;
                    $returnarray['export'][$lastroot]['subitems'][$kaskadei] = $newarray;
                } else {
                    $returnarray['export'][$lastroot]['subitems'][$kaskadei] = $newarray;
                }
            }
            // Verschachtelungstiefe hochzählen
            ++$kaskadei;
        }

        // Wenn die Zeile keine Verschachtelung darstellt
        else {
            if ((osf_specialtags($newarray['tags'], $specialtags)) || ($exportall == 'true')) {
                // Daten auf oberster ebene einfügen
                $returnarray['export'][$i] = $newarray;

                // Nummer des letzten Objekts auf oberster ebene auf akutelle Item Nummer setzen
                $lastroot = $i;

                // Verschachtelungstiefe auf 0 setzen
                $kaskadei = 0;
            } else {
                unset($newarray);
            }
        }
        // Item Nummer hochzählen
        ++$i;
    }

    // Zusatzinformationen im Array abspeichern (Zeilenzahl, Zeichenlänge und Hash der Shownotes)
    $returnarray['info']['zeilen']  = $i;
    $returnarray['info']['zeichen'] = strlen($shownotes);
    $returnarray['info']['hash']    = md5($shownotes);
    if(isset($header)) {
        $returnarray['header']      = $header;
    }
    // Rückgabe der geparsten Daten
    return $returnarray;
}

function osf_checktags($needles, $haystack) {
    $return = false;
    if (is_array($haystack)) {
        foreach ($needles as $needle) {
            if (array_search($needle, $haystack) !== false) {
                $return = true;
            }
        }
    }
    return $return;
}

function osf_metacast_textgen($subitem, $tagtext, $text) {
    global $shownotes_options;
    if(isset($shownotes_options['main_delimiter'])) {
        $delimiter = $shownotes_options['main_delimiter'];
    } else {
        $delimiter = ' &nbsp;';
    }
    if(trim($text) == "") {
        return '';
    }
    $subtext = '';
    if (isset($subitem['urls'][0])) {
        $tagtext .= ' osf_link';
        $url = parse_url($subitem['urls'][0]);
        $url = explode('.', $url['host']);
        $tagtext .= ' osf_' . $url[count($url) - 2] . $url[count($url) - 1];
        $subtext .= '<a target="_blank" href="' . $subitem['urls'][0] . '"';
        if (strstr($subitem['urls'][0], 'wikipedia.org/wiki/')) {
            $subtext .= ' class="osf_wiki ' . $tagtext . '"';
        } elseif (strstr($subitem['urls'][0], 'www.amazon.')) {
            $subtext .= ' class="osf_amazon ' . $tagtext . '"';
        } elseif (strstr($subitem['urls'][0], 'www.youtube.com/') || ($subitem['chapter'] == 'video')) {
            $subtext .= ' class="osf_youtube ' . $tagtext . '"';
        } elseif (strstr($subitem['urls'][0], 'flattr.com/')) {
            $subtext .= ' class="osf_flattr ' . $tagtext . '"';
        } elseif (strstr($subitem['urls'][0], 'twitter.com/')) {
            $subtext .= ' class="osf_twitter ' . $tagtext . '"';
        } else {
            $subtext .= ' class="' . $tagtext . '"';
        }

        if ((isset($subitem['time'])) && (trim($subitem['time']) != '')) {
            $subtext .= ' data-tooltip="' . $subitem['time'] . '"';
        }
        $subtext .= '>' . trim($text) . '</a>';
    } else {
        $subtext .= '<span';
        if ($tagtext != '') {
            $subtext .= ' class="' . $tagtext . '"';
        }
        if ((isset($subitem['time'])) && (trim($subitem['time']) != '')) {
            $subtext .= ' data-tooltip="' . $subitem['time'] . '"';
        }
        $subtext .= '>' . trim($text) . '</span>';
    }
    $subtext .= $delimiter;
    return $subtext;
}

//HTML export im anyca.st style
function osf_export_anycast($array, $full = false, $filtertags = array(0 => 'spoiler')) {
    global $shownotes_options;
    if(isset($shownotes_options['main_delimiter'])) {
        $delimiter = $shownotes_options['main_delimiter'];
    } else {
        $delimiter = ' &nbsp;';
    }
    if(isset($shownotes_options['main_last_delimiter'])) {
        $lastdelimiter = $shownotes_options['main_last_delimiter'];
    } else {
        $lastdelimiter = '. ';
    }

    $returnstring  = '<div>';
    $filterpattern = array(
        '(\s(#)(\S*))',
        '(\<((http(|s)://[\S#?-]{0,128})>))',
        '(\s+((http(|s)://[\S#?-]{0,128})\s))',
        '(^ *-*)'
    );
    $arraykeys     = array_keys($array);
    for ($i = 0; $i <= count($array); $i++) {
        if (isset($array[$arraykeys[0]])) {
            if (isset($arraykeys[$i])) {
                if (isset($array[$arraykeys[$i]])) {
                    if ((@$array[$arraykeys[$i]]['chapter']) || (($full != false) && (@$array[$arraykeys[$i]]['time'] != ''))) {
                        $text = preg_replace($filterpattern, '', $array[$arraykeys[$i]]['text']);
                        if (strpos($array[$arraykeys[$i]]['time'], '.')) {
                            $time = explode('.', $array[$arraykeys[$i]]['time']);
                            $time = $time[0];
                        } else {
                            $time = $array[$arraykeys[$i]]['time'];
                        }

                        if (($array[$arraykeys[$i]]['chapter']) && ($full != false) && ($time != '') && ($time != '00:00:00')) {
                            //$returnstring .= ''; //add code, which should inserted between chapters
                            if(isset($shownotes_options['main_chapter_delimiter'])) {
                                $returnstring .= $shownotes_options['main_chapter_delimiter'];
                            }
                        }

                        $returnstring .= "\n" . '<div class="osf_chapterbox"><span class="osf_chaptertime" data-time="' . osf_convert_time($time) . '">' . $time . '</span> ';
                        if (isset($array[$arraykeys[$i]]['urls'][0])) {
                            $returnstring .= ' <strong';
                            if (($array[$arraykeys[$i]]['chapter']) && ($time != '')) {
                                $returnstring .= ' class="osf_chapter"';
                            }
                            $returnstring .= '><a target="_blank" href="' . $array[$arraykeys[$i]]['urls'][0] . '">' . $text . '</a></strong><div class="osf_items"> ' . "\n";
                        } else {
                            $returnstring .= ' <strong';
                            if (($array[$arraykeys[$i]]['chapter']) && ($time != '')) {
                                $returnstring .= ' class="osf_chapter"';
                            }
                            $returnstring .= '>' . $text . '</strong><div class="osf_items"> ' . "\n";
                        }
                        if (isset($array[$arraykeys[$i]]['subitems'])) {
                            for ($ii = 0; $ii <= count($array[$arraykeys[$i]]['subitems'], COUNT_RECURSIVE); $ii++) {
                                if (isset($array[$arraykeys[$i]]['subitems'][$ii])) {
                                    if ((((($full != false) || (!$array[$arraykeys[$i]]['subitems'][$ii]['subtext'])) && ((($full == 1) && (!osf_checktags($filtertags, $array[$arraykeys[$i]]['subitems'][$ii]['tags']))) || ($full == 2))) && (strlen(trim($array[$arraykeys[$i]]['subitems'][$ii]['text'])) > 2))||($full == 2)) {
                                        if (($full == 2) && (@osf_checktags($filtertags, @$array[$arraykeys[$i]]['subitems'][$ii]['tags']))) {
                                            $tagtext = ' osf_spoiler';
                                        } else {
                                            $tagtext = '';
                                        }
                                        $substart = '';
                                        $subend   = '';
                                        if (isset($array[$arraykeys[$i]]['subitems'][$ii]['subtext'])) {
                                            if ($array[$arraykeys[$i]]['subitems'][$ii]['subtext']) {
                                                if (!@$array[$arraykeys[$i]]['subitems'][$ii - 1]['subtext']) {
                                                    //$tagtext .= ' osf_substart';
                                                    $substart = '(';
                                                }
                                                if (!@$array[$arraykeys[$i]]['subitems'][$ii + 1]['subtext']) {
                                                    //$tagtext .= ' osf_subend';
                                                    $subend = ')'.$delimiter;
                                                }
                                            }
                                            if (is_array(@$array[$arraykeys[$i]]['subitems'][$ii]['tags'])) {
                                                foreach ($array[$arraykeys[$i]]['subitems'][$ii]['tags'] as $tag) {
                                                    $tagtext .= ' osf_' . $tag;
                                                }
                                            }
                                        }
                                        $text    = preg_replace($filterpattern, '', $array[$arraykeys[$i]]['subitems'][$ii]['text']);
                                        $subtext = osf_metacast_textgen($array[$arraykeys[$i]]['subitems'][$ii], $tagtext, $text);
                                        $subtext = trim($subtext);
                                        $returnstring .= $substart.$subtext.$subend;
                                    }
                                }
                            }
                        }
                        $returnstring .= '</div></div>';
                    }
                }
            }
        }
    }

    $returnstring .= '</div>' . "\n";
    $cleanupsearch = array($delimiter.'</div>'
                          ,',</div>'
                          ,$delimiter.')'
                          ,$delimiter.'(');

    $cleanupreplace = array($lastdelimiter.'</div>'
                           ,'</div>'
                           ,') '
                           ,' (');

    $returnstring = str_replace($cleanupsearch, $cleanupreplace, $returnstring);
    return $returnstring;
}

function osf_export_wikigeeks($array, $full = false, $filtertags = array(0 => 'spoiler')) {
    $filtertags    = array(
        'spoiler',
        'trash'
    );
    $returnstring  = '<div class="osf_wikigeeks">';
    $filterpattern = array(
        '(\s(#)(\S*))',
        '(\<((http(|s)://[\S#?-]{0,128})>))',
        '(\s+((http(|s)://[\S#?-]{0,128})\s))',
        '(^ *-*)'
    );
    foreach ($array as $item) {
        if ((@$item['chapter']) || (($full != false) && (@$item['time'] != ''))) {
            $text = preg_replace($filterpattern, '', $item['text']);
            if (strpos($item['time'], '.')) {
                $time = explode('.', $item['time']);
                $time = $time[0];
            } else {
                $time = $item['time'];
            }
            if (($item['chapter']) && ($full != false) && ($time != '') && ($time != '00:00:00')) {
                $returnstring .= ''; //add code, which should inserted between chapters
            }

            if (isset($item['urls'][0])) {
                $returnstring .= '<div><h1><a target="_blank" href="' . $item['urls'][0] . '">' . $text . '</a> [' . $time . ']</h1></div><ul>';
            } else {
                $returnstring .= '<div><h1>' . $text . ' [' . $time . ']</h1></div><ul>';
            }
            if (isset($item['subitems'])) {
                $subitemi = 0;
                foreach ($item['subitems'] as $subitem) {
                    if (((($full != false) || (!$subitem['subtext'])) && ((($full == 1) && (!osf_checktags($filtertags, $subitem['tags']))) || ($full == 2))) && (strlen(trim($subitem['text'])) > 2)) {
                        if (($full == 2) && (osf_checktags($filtertags, @$subitem['tags']))) {
                            $hide = ' osf_spoiler';
                        } else {
                            $hide = '';
                        }

                        $text = preg_replace($filterpattern, '', $subitem['text']);
                        if ($subitemi) {
                            $subtext = '';
                        } else {
                            $subtext = '';
                        }
                        if (isset($subitem['urls'][0])) {
                            $subtext .= '<li><a target="_blank" href="' . $subitem['urls'][0] . '"';
                            if (strstr($subitem['urls'][0], 'wikipedia.org/wiki/')) {
                                $subtext .= ' class="osf_wiki ' . $hide . '"';
                            } elseif (strstr($subitem['urls'][0], 'www.amazon.')) {
                                $subtext .= ' class="osf_amazon ' . $hide . '"';
                            } elseif (strstr($subitem['urls'][0], 'www.youtube.com/') || ($subitem['chapter'] == 'video')) {
                                $subtext .= ' class="osf_youtube ' . $hide . '"';
                            } elseif (strstr($subitem['urls'][0], 'flattr.com/')) {
                                $subtext .= ' class="osf_flattr ' . $hide . '"';
                            } elseif (strstr($subitem['urls'][0], 'twitter.com/')) {
                                $subtext .= ' class="osf_twitter ' . $hide . '"';
                            }

                            if ((isset($subitem['time'])) && (trim($subitem['time']) != '')) {
                                $subtext .= ' data-tooltip="' . $subitem['time'] . '"';
                            }
                            $subtext .= '>' . trim($text) . '</a></li>' . " ";
                        } else {
                            $subtext .= '<li><span';
                            if ($hide != '') {
                                $subtext .= ' class="' . $hide . '"';
                            }
                            if ((isset($subitem['time'])) && (trim($subitem['time']) != '')) {
                                $subtext .= ' data-tooltip="' . $subitem['time'] . '"';
                            }
                            $subtext .= '>' . trim($text) . '</span></li> ';
                        }
                        $subtext = trim($subtext);
                        $returnstring .= $subtext;
                        ++$subitemi;
                    }
                }
            }
            $returnstring .= '</ul>';
        }
    }

    $returnstring .= '</div>' . "\n";
    return $returnstring;
}

function osf_glossarysort($a, $b) {
    $ax = str_split(strtolower(trim($a['text'])));
    $bx = str_split(strtolower(trim($b['text'])));

    if (count($ax) < count($bx)) {
        for ($i = 0; $i <= count($bx); $i++) {
            if (ord($ax[$i]) != ord($bx[$i])) {
                return (ord($ax[$i]) < ord($bx[$i])) ? -1 : 1;
            }
        }
    } else {
        for ($i = 0; $i <= count($ax); $i++) {
            if (ord($ax[$i]) != ord($bx[$i])) {
                return (ord($ax[$i]) < ord($bx[$i])) ? -1 : 1;
            }
        }
    }
    return 0;
}

//HTML export as glossary
function osf_export_glossary($array, $showtags = array(0 => '')) {
    $linksbytag = array();

    $filterpattern = array(
        '(\s(#)(\S*))',
        '(\<((http(|s)://[\S#?-]{0,128})>))',
        '(\s+((http(|s)://[\S#?-]{0,128})\s))',
        '(^ *-*)'
    );
    $arraykeys     = array_keys($array);
    for ($i = 0; $i <= count($array); $i++) {
        if ((@$array[$arraykeys[$i]]['chapter']) || ((@$full != false) && (@$array[$arraykeys[$i]]['time'] != ''))) {
            if (isset($array[$arraykeys[$i]]['subitems'])) {
                for ($ii = 0; $ii <= count($array[$arraykeys[$i]]['subitems']); $ii++) {
                    if ((@$array[$arraykeys[$i]]['subitems'][$ii]['urls'][0] != '') && (@$array[$arraykeys[$i]]['subitems'][$ii]['text'] != '')) {
                        foreach ($array[$arraykeys[$i]]['subitems'][$ii]['tags'] as $tag) {
                            if (($showtags[0] == '') || (array_search($tag, $showtags) !== false)) {
                                $linksbytag[$tag][$ii]['url']  = $array[$arraykeys[$i]]['subitems'][$ii]['urls'][0];
                                $linksbytag[$tag][$ii]['text'] = $array[$arraykeys[$i]]['subitems'][$ii]['text'];
                            }
                        }
                    }
                }
            }
        }
    }

    $return = '';

    foreach ($linksbytag as $tagname => $content) {
        $return .= '<h1>' . $tagname . '</h1>' . "\n";
        $return .= '<ol>' . "\n";
        usort($content, "osf_glossarysort");
        foreach ($content as $item) {
            $return .= '<li><a target="_blank" href="' . $item['url'] . '">' . $item['text'] . '</a></li>' . "\n";
        }
        $return .= '</ol>' . "\n";
    }

    return $return;
}

function markdown($string) {
    $rules['sm'] = array(
        '/\n(#+)(.*)/e' => 'md_header(\'\\1\', \'\\2\')',               // headers
        '/\[([^\[]+)\]\(([^\)]+)\)/' => '<a target="_blank" href=\'\2\'>\1</a>',        // links
        '/(\*\*\*|___)(.*?)\1/' => '<em><strong>\2</strong></em>',      // bold emphasis
        '/(\*\*|__)(.*?)\1/' => '<strong>\2</strong>',                  // bold
        '/(\*|_)([\w| ]+)\1/' => '<em>\2</em>',                         // emphasis
        '/\~\~(.*?)\~\~/' => '<del>\1</del>',                           // del
        '/\:\"(.*?)\"\:/' => '<q>\1</q>',                               // quote
        '/\n([*]+)\s([[:print:]]*)/e' => 'md_ulist(\'\\1\', \'\\2\')',  // unorderd lists
        '/\n[0-9]+\.(.*)/e' => 'md_olist(\'\\1\')',                     // orderd lists
        '/\n&gt;(.*)/e' => 'md_blockquote(\'\\1\')',                    // blockquotes
        '/\n([^\n]+)\n/e' => 'md_paragraph(\'\\1\')',                   // add paragraphs
        '/<\/ul>(\s*)<ul>/' => '',                                      // fix extra ul
        '/(<\/li><\/ul><\/li><li><ul><li>)/' => '</li><li>',            // fix extra ul li
        '/(<\/ul><\/li><li><ul>)/' => '',                               // fix extra ul li
        '/<\/ol><ol>/' => '',                                           // fix extra ol
        '/<\/blockquote><blockquote>/' => "\n"                          // fix extra blockquote
    );

    $rules['html'] = array(
        '(\s+((http(|s)://\S{0,64})\s))' => ' <a target="_blank" href="\2">\2</a> ',                                 // url
        '(\s+(([a-zA-Z0-9.,+_-]{1,63}[@][a-zA-Z0-9.,-]{0,254})))' => ' <a target="_blank" href="mailto:\2">\2</a> ', // mail
        '(\s+((\+)[0-9]{5,63}))' => ' <a target="_blank" href="tel:\1">call \1</a>'                                  // phone
    );

    $rules['tweet'] = array(
        '((@)(\S*))' => ' <a target="_blank" href=\'https://twitter.com/\2\'>\1\2</a> ',                         // user
        '((#)(\S*))' => ' <a target="_blank" href=\'https://twitter.com/#!/search/?src=hash&q=%23\2\'>\1\2</a> ' // hashtag
    );


    $string = "\n" . $string . "\n";

    foreach ($rules as $rule) {
        foreach ($rule as $regex => $replace) {
            $string = preg_replace($regex, $replace, $string);
        }
    }

    return trim($string);
}

function md_header($chars, $header) {
    $level = strlen($chars);
    return sprintf('<h%d>%s</h%d>', $level, trim($header), $level);
}

function md_ulist($count, $string) {
    $return = trim($string);
    $count  = strlen($count);
    $i      = 0;
    while ($i != $count) {
        $return = '<ul><li>' . $return . '</li></ul>';
        ++$i;
    }
    return $return;
}

function md_olist($item) {
    return sprintf("\n<ol>\n\t<li>%s</li>\n</ol>", trim($item));
}

function md_blockquote($item) {
    return sprintf("\n<blockquote>%s</blockquote>", trim($item));
}

function md_paragraph($line) {
    $trimmed = trim($line);
    if (strpos($trimmed, '<') === 0) {
        return $line;
    }
    return sprintf("\n<p>%s</p>\n", $trimmed);
}

?>
