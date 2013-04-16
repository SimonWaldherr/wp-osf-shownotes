<?php

function osf_shownotes_shortcode($atts, $content = "") {
  $export = 'test-lorem-ipsum';
  $post_id = get_the_ID();
  $shownotes = get_post_meta($post_id, 'shownotes', true);
  if (($content !== "")||($shownotes)) {
    $data = array(
      'amazon' => 'shownot.es-21',
      'thomann' => '93439',
      'tradedoubler' => '16248286',
      'fullmode' => 'true',
      'tags' => explode(' ', 'chapter section spoiler topic embed video audio image shopping glossary source app title quote')
    );

    //undo fucking wordpress shortcode cripple shit
    if($content !== "") {
      $shownotesString = htmlspecialchars_decode(str_replace('<br />', '', str_replace('<p>', '', str_replace('</p>', '', $content))));
    } else {
      $shownotesString = "\n".$shownotes."\n";
    }

    //parse shortcode as osf string to html
    $shownotesArray = osf_parser($shownotesString, $data);
    $export     = osf_export_anycast($shownotesArray['export'], 1);
  }
  return $export;
}

add_shortcode('osf-shownotes', 'osf_shownotes_shortcode');

function shownotesshortcode_add_styles() {
  wp_enqueue_style('shownotesstyle', 'http://cdn.shownot.es/include-shownotes/shownotes.css', array(), '0.0.1');
}
add_action('wp_print_styles', 'shownotesshortcode_add_styles');

function osf_specialtags($needles, $haystack)
{
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
  $amazon     = $data['amazon'];
  $thomann    = $data['thomann'];
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
    $purl     = 'http://www.thomann.de/index.html?partner_id=' . $thomann . '&page=/' . $thomannurl[1];
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
    $pause     = explode(':', $utimestamp);
    $osf_starttime = $osf_starttime + $pause[1] - $pause[0];
  }
  $duration = $utimestamp - $osf_starttime;
  $sec    = $duration % 60;
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
  $osf_starttime = $unixtimestamps[0][0];
  $regexTS     = array(
    '/\n[0-9:]{9,23}/e',
    'osf_time_from_timestamp(\'\\0\')'
  );
  return preg_replace($regexTS[0], $regexTS[1], $shownotes);
}

function osf_parser($shownotes, $data) {
  // Diese Funktion ist das Herzstück des OSF-Parsers
  $specialtags = $data['tags'];
  $exportall   = $data['fullmode'];

  // entferne alle Angaben vorm und im Header
  $shownotes = explode('/HEADER', $shownotes);
  if (count($shownotes) != 1) {
    if (strlen($shownotes[1]) > 42) {
      $shownotes = $shownotes[1];
    } else {
      $shownotes = $shownotes[0];
    }
  } else {
    $shownotes = $shownotes[0];
  }

  $shownotes = explode('/HEAD', $shownotes);
  if (count($shownotes) != 1) {
    if (strlen($shownotes[1]) > 42) {
      $shownotes = $shownotes[1];
    } else {
      $shownotes = $shownotes[0];
    }
  } else {
    $shownotes = $shownotes[0];
  }

  // wandle Zeitangaben im UNIX-Timestamp Format in relative Zeitangaben im Format 01:23:45 um
  $shownotes = "\n" . osf_replace_timestamps("\n" . $shownotes);

  // zuerst werden die regex-Definitionen zum erkennen von Zeilen, Tags, URLs und subitems definiert
  $pattern['zeilen']  = '/(((\d\d:)?\d\d:\d\d)(\\.\d\d\d)?)*(.+)/';
  $pattern['tags']  = '((\s#)(\S*))';
  $pattern['urls']  = '(\s+((http(|s)://\S{0,256})\s))';
  $pattern['urls2']   = '(\<((http(|s)://\S{0,256})>))';
  $pattern['kaskade'] = '/^([\t ]*-+ )/';

  // danach werden mittels des zeilen-Patterns die Shownotes in Zeilen/items geteilt
  preg_match_all($pattern['zeilen'], $shownotes, $zeilen, PREG_SET_ORDER);

  // Zählvariablen definieren
  // i = item, lastroot = Nummer des letzten Hauptitems, kaskadei = Verschachtelungstiefe
  $i               = 0;
  $lastroot            = 0;
  $kaskadei            = 0;
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
    $newarray['text'] = trim(htmlentities(preg_replace(array(
      $pattern['tags'],
      $pattern['urls'],
      $pattern['urls2']
    ), array(
      '',
      '',
      ''
    ), $zeile[5]), ENT_QUOTES, 'UTF-8'));
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
      if (((in_array("Chapter", $newarray['tags'])) || (in_array("chapter", $newarray['tags']))) && ($newarray['time'] != '')) {
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
      $newarray['tags'][] = 'link';
    }

    // Wenn Zeile mit "- " beginnt im Ausgabe-Array verschachteln
    if ((preg_match($pattern['kaskade'], $zeile[0])) || (!preg_match('/(\d\d:\d\d:\d\d)/', $zeile[0])) || (!$newarray['chapter'])) {
      if (isset($newarray['tags'])) {
        if ((osf_specialtags($newarray['tags'], $specialtags)) || ($exportall == 'true')) {
          if (preg_match($pattern['kaskade'], $zeile[0])) {
            $newarray['subtext']                   = true;
            $returnarray['export'][$lastroot]['subitems'][$kaskadei] = $newarray;
          } else {
            //$newarray['subtext'] = true;
            $returnarray['export'][$lastroot]['subitems'][$kaskadei] = $newarray;
          }
        } else {
          unset($newarray);
        }
      }


      // Verschachtelungstiefe hochzählen
      ++$kaskadei;
    }

    // Wenn die Zeile keine Verschachtelung darstellt
    else {
      if ((osf_specialtags($newarray['tags'], $specialtags)) || ($exportall == 'true'))
        {
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
  $returnarray['info']['hash']  = md5($shownotes);

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
  $subtext = '';
  if (isset($subitem['urls'][0])) {
    $tagtext .= ' osf_link';
    $url = parse_url($subitem['urls'][0]);
    $url = explode('.', $url['host']);
    $tagtext .= ' osf_' . $url[count($url) - 2] . $url[count($url) - 1];
    $subtext .= '<a href="' . $subitem['urls'][0] . '"';
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
    $subtext .= '>' . trim($text) . '</a>' . " ";
  } else {
    $subtext .= '<span';
    if ($tagtext != '') {
      $subtext .= ' class="' . $tagtext . '"';
    }
    if ((isset($subitem['time'])) && (trim($subitem['time']) != '')) {
      $subtext .= ' data-tooltip="' . $subitem['time'] . '"';
    }
    $subtext .= '>' . trim($text) . '</span> ';
  }
  return $subtext;
}

//HTML export im anyca.st style

function osf_export_anycast($array, $full = false, $filtertags = array(0 => 'spoiler')) {
  $returnstring  = '<dl>';
  $filterpattern = array(
    '(\s(#)(\S*))',
    '(\<((http(|s)://[\S#?-]{0,128})>))',
    '(\s+((http(|s)://[\S#?-]{0,128})\s))',
    '(^ *-*)'
  );
  $arraykeys   = array_keys($array);
  for ($i = 0; $i <= count($array); $i++) {
    if (isset($array[$arraykeys[0]])) {
      if(isset($arraykeys[$i])) {
        if (isset($array[$arraykeys[$i]])) {
          if (($array[$arraykeys[$i]]['chapter']) || (($full != false) && ($array[$arraykeys[$i]]['time'] != ''))) {
            $text = preg_replace($filterpattern, '', $array[$arraykeys[$i]]['text']);
            if (strpos($array[$arraykeys[$i]]['time'], '.')) {
              $time = explode('.', $array[$arraykeys[$i]]['time']);
              $time = $time[0];
            } else {
              $time = $array[$arraykeys[$i]]['time'];
            }

            if (($array[$arraykeys[$i]]['chapter']) && ($full != false) && ($time != '') && ($time != '00:00:00')) {
              $returnstring .= ''; //add code, which should inserted between chapters
            }

            $returnstring .= '<dt data-time="' . osf_convert_time($time) . '">' . $time . '</dt>' . "\n" . '<dd>';
            if (isset($array[$arraykeys[$i]]['urls'][0])) {
              $returnstring .= '<strong';
              if (($array[$arraykeys[$i]]['chapter']) && ($time != '')) {
                $returnstring .= ' class="osf_chapter"';
              }
              $returnstring .= '><a href="' . $array[$arraykeys[$i]]['urls'][0] . '">' . $text . '</a></strong><div class="osf_items"> ' . "\n";
            } else {
              $returnstring .= '<strong';
              if (($array[$arraykeys[$i]]['chapter']) && ($time != '')) {
                $returnstring .= ' class="osf_chapter"';
              }
              $returnstring .= '>' . $text . '</strong><div class="osf_items"> ' . "\n";
            }
            if (isset($array[$arraykeys[$i]]['subitems'])) {
              for ($ii = 0; $ii <= count($array[$arraykeys[$i]]['subitems']); $ii++) {
                if (isset($array[$arraykeys[$i]]['subitems'][$ii])) {
                  if (((($full != false) || (!$array[$arraykeys[$i]]['subitems'][$ii]['subtext'])) && ((($full == 1) && (!osf_checktags($filtertags, $array[$arraykeys[$i]]['subitems'][$ii]['tags']))) || ($full == 2))) && (strlen(trim($array[$arraykeys[$i]]['subitems'][$ii]['text'])) > 2)) {
                    if (($full == 2) && (osf_checktags($filtertags, $array[$arraykeys[$i]]['subitems'][$ii]['tags']))) {
                      $tagtext = ' osf_spoiler';
                    } else {
                      $tagtext = '';
                    }
                    if (isset($array[$arraykeys[$i]]['subitems'][$ii]['subtext'])) {
                      if ($array[$arraykeys[$i]]['subitems'][$ii]['subtext']) {
                        if (!$array[$arraykeys[$i]]['subitems'][$ii - 1]['subtext']) {
                          $tagtext .= ' osf_substart';
                        }
                        if (!$array[$arraykeys[$i]]['subitems'][$ii + 1]['subtext']) {
                          $tagtext .= ' osf_subend';
                        }
                      }
                      if (is_array($array[$arraykeys[$i]]['subitems'][$ii]['tags'])) {
                        foreach ($array[$arraykeys[$i]]['subitems'][$ii]['tags'] as $tag) {
                          $tagtext .= ' osf_' . $tag;
                        }
                      }
                    }
                    $text  = preg_replace($filterpattern, '', $array[$arraykeys[$i]]['subitems'][$ii]['text']);
                    $subtext = osf_metacast_textgen($array[$arraykeys[$i]]['subitems'][$ii], $tagtext, $text);
                    $subtext = trim($subtext);
                    $returnstring .= $subtext;
                  }
                }
              }
            }
            $returnstring .= '</div></dd>';
          }
        }
      }
    }
  }

  $returnstring .= '</dl>' . "\n";
  return str_replace(',</dd>', '</dd>', $returnstring);
}

?>
