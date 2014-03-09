<?php

/*
 * µmarkdown.php
 * translated from https://github.com/SimonWaldherr/micromarkdown.js
 *
 * Copyright 2014, Simon Waldherr - http://simon.waldherr.eu/
 * Released under the MIT Licence
 * http://simon.waldherr.eu/license/mit/
 *
 * Github:  https://github.com/SimonWaldherr/micromarkdown.php
 * Version: 0.2.0
 */

function mmd_CSSclass($string, $strict) {
  if ((strpos($string, '/') !== false) && ($strict !== true)) {
    $urlTemp = explode('/', $string);
    if (strlen($urlTemp[1]) === 0) {
      $urlTemp = explode('.', $urlTemp[2]);
    } else {
      $urlTemp = explode('.', $urlTemp[0]);
    }
    return 'class="mmd_' . preg_replace('/[^\w\d]/', '', $urlTemp[count($urlTemp) - 2]) . $urlTemp[count($urlTemp) - 1] . '" ';
  }
  return '';
}

function micromarkdown($string, $strict=false) {
  $regexobject = array(
    "headline"=>   '/^(\#{1,6})([^\#\n]+)$/m',
    "code"=>       '/\s\`\`\`\n?([^`]+)\`\`\`/',
    "hr"=>         '/\n(?:([\*\-_] ?)+)\1\1$/m',
    "lists"=>      '/^((\s*((\*|\-)|\d(\.|\))) [^\n]+)\n)+/m',
    "bolditalic"=> '/(?:([\*_~]{1,3}))([^\*_~\n]+[^\*_~\s])\1/',
    "links"=>      '/!?\[([^\]<>]+)\]\(([^ \)<>]+)( "[^\(\)\"]+")?\)/',
    "reflinks"=>   '/\[([^\]]+)\]\[([^\]]+)\]/',
    "smlinks"=>    '/\@([a-z0-9]{3,})\@(t|gh|fb|gp|adn)/i',
    "mail"=>       '/<(([a-z0-9_\-\.])+\@([a-z0-9_\-\.])+\.([a-z]{2,7}))>/mi',
    "tables"=>     '/\n(([^|\n]+ *\| *)+([^|\n]+\n))((:?\-+:?\|)+(:?\-+:?)*\n)((([^|\n]+ *\| *)+([^|\n]+)\n)+)/',
    "include"=>    '/[\[<]include (\S+) from (https?:\/\/[a-z0-9\.\-]+\.[a-z]{2,9}[a-z0-9\.\-\?\&\/]+)[\]>]/i',
    "url"=>        '/<([a-zA-Z0-9@:%_\+.~#?&\/\/=]{2,256}\.[a-z]{2,4}\b(\/[\-a-zA-Z0-9@:%_\+.~#?&\/\/=]*)?)>/');

  if ($strict !== true) {
    $regexobject['lists'] = '/^((\s*(\*|\d\.) [^\n]+)\n)+/m';
  }

  $string = "\n" . $string . "\n";

  /* code */
  while (preg_match($regexobject['code'], $string, $match)) {
    $string = str_replace($match[0], '<code>' . nl2br(str_replace(" ", "&nbsp;", htmlentities($match[1]))) . '</code>', $string);
  }

  /* headline */
  while (preg_match($regexobject['headline'], $string, $match)) {
    $count = strlen($match[1]);
    $string = str_replace($match[0], '<h' . $count . '>' . $match[2] . '</h' . $count . '>', $string);
  }

  /* lists */
  while (preg_match($regexobject['lists'], $string, $match)) {
    $casca = 0;
    if ((substr(trim($match[0]), 0, 1) === '*') || (substr(trim($match[0]), 0, 1) === '-')) {
      $repstr = '<ul>';
    } else {
      $repstr = '<ol>';
    }
    $helper = explode("\n", $match[0]);
    $helper1 = array();
    $status = 0;
    $indent = false;
    for ($i = 0; $i < count($helper); $i++) {
      if (preg_match('/^((\s*)((\*|\-)|\d(\.|\))) ([^\n]+))/', $helper[$i], $line)) {
        if ($line[2] === "") {
          $nstatus = 0;
        } else {
          if ($indent === false) {
            $indent = strlen(str_replace("\t", "    ", $line[2]));
          }
          $nstatus = round(strlen(str_replace("\t", "    ", $line[2]))/$indent);
        }
        while ($status > $nstatus) {
          $repstr .= array_pop($helper1);
          $status--;
          $casca--;
        }
        while ($status < $nstatus) {
          if ((substr(trim($line[0]), 0, 1) === '*') || (substr(trim($line[0]), 0, 1) === '-') ) {
            $repstr .= '<ul>';
            array_push($helper1, '</ul>');
          } else {
            $repstr .= '<ol>';
            array_push($helper1, '</ol>');
          }
          $status++;
          $casca++;
        }
        $repstr .= '<li>' . $line[6] . '</li>' . "\n";
      }
    }
    while ($casca > 0) {
      $repstr .= '</ul>';
      $casca--;
    }
    if ((substr(trim($match[0]), 0, 1) === '*') || (substr(trim($match[0]), 0, 1) === '-')) {
      $repstr .= '</ul>';
    } else {
      $repstr .= '</ol>';
    }
    $string = str_replace($match[0], $repstr."\n", $string);
  }

  /* tables */
  while (preg_match($regexobject['tables'], $string, $match)) {
    $repstr = '<table><tr>';
    $helper = explode('|', $match[1]);
    $calign = explode('|', $match[4]);
    for ($i = 0; $i < count($helper); $i++) {
      if (count($calign) <= $i) {
        array_push($calign, 0);
      } else if (substr(trim($calign[$i]), -1) == ':') {
        if ($calign[$i][0] == ':') {
          $calign[$i] = 3;
        } else {
          $calign[$i] = 2;
        }
      } else if ($strict !== true) {
        if ($calign[$i][0] == ':') {
          $calign[$i] = 1;
        } else {
          $calign[$i] = 0;
        }
      } else {
        $calign[$i] = 0;
      }
    }
    $cel = ['<th>', '<th align="left">', '<th align="right">', '<th align="center">'];
    for ($i = 0; $i < count($helper); $i++) {
      $repstr .= $cel[$calign[$i]] . trim($helper[$i]) . '</th>';
    }
    $repstr .= '</tr>';
    $cel = ['<td>', '<td align="left">', '<td align="right">', '<td align="center">'];
    $helper1 = explode("\n", trim($match[7]));
    for ($i = 0; $i < count($helper1); $i++) {
      $helper2 = explode('|', $helper1[$i]);
      if (count($helper2[0]) !== 0) {
        while (count($calign) < count($helper2)) {
          array_push($calign, 0);
        }
        $repstr .= '<tr>';
        for ($j = 0; $j < count($helper2); $j++) {
          $repstr .= $cel[$calign[$j]] . $helper2[$j] . '</td>';
        }
        $repstr .= '</tr>' . "\n";
      }
    }
    $repstr .= '</table>';
    $string = str_replace($match[0], $repstr, $string);
  }

  /* bold and italic */
    while (preg_match($regexobject['bolditalic'], $string, $match)) {
      if ($match[1] === '~~') {
        $string = str_replace($match[0], '<del>' . $match[2] . '</del>', $string);
      } else {
        switch (strlen($match[1])) {
        case 1:
          $repstr = array('<i>', '</i>');
          break;
        case 2:
          $repstr = array('<b>', '</b>');
          break;
        case 3:
          $repstr = array('<i><b>', '</b></i>');
          break;
        }
        $string = str_replace($match[0], $repstr[0] . $match[2] . $repstr[1], $string);
      }
    }

  /* links */
  while (preg_match($regexobject['links'], $string, $match)) {
    if (substr($match[0], 0, 1) === '!') {
      $string = str_replace($match[0], '<img src="' . $match[2] . '" alt="' . $match[1] . '" title="' . $match[1] . '" />' . "\n", $string);
    } else {
      $string = str_replace($match[0], '<a ' . mmd_CSSclass($match[2], $strict) . 'href="' . $match[2] . '">' . $match[1] . '</a>' . "\n", $string);
    }
  }
  while (preg_match($regexobject['mail'], $string, $match)) {
    $string = str_replace($match[0], '<a href="mailto:' . $match[1] . '">' . $match[1] . '</a>', $string);
  }
  while (preg_match($regexobject['url'], $string, $match)) {
    $repstr = $match[1];
    if (strpos($repstr, '://') === -1) {
      $repstr = 'http://' . $repstr;
    }
    $string = str_replace($match[0], '<a ' . mmd_CSSclass($repstr, $strict) . 'href="' . $repstr . '">' . str_replace(array('https://', 'http://', 'mailto:', 'ftp://'), array('', '', '', ''), $repstr) . '</a>', $string);
  }
  $trashgc = array();
  while (preg_match($regexobject['reflinks'], $string, $match)) {
    if (preg_match('/\[' . $match[2] . '\]: ?([^ ' . "\n" . ']+)/', $string, $helper)) {
      $string = str_replace($match[0], '<a ' . mmd_CSSclass($helper[1], $strict) . 'href="' . $helper[1] . '">' . $match[1] . '</a>', $string);
      array_push($trashgc, $helper[0]);
    }
  }
  for ($i = 0; $i < count($trashgc); $i++) {
    $string = str_replace($trashgc[$i], '', $string);
  }
  while (preg_match($regexobject['smlinks'], $string, $match)) {
    switch ($match[2]) {
    case 't':
      $repstr = 'https://twitter.com/' . $match[1];
      break;
    case 'gh':
      $repstr = 'https://github.com/' . $match[1];
      break;
    case 'fb':
      $repstr = 'https://www.facebook.com/' . $match[1];
      break;
    case 'gp':
      $repstr = 'https://plus.google.com/' . $match[1];
      break;
    case 'adn':
      $repstr = 'https://alpha.app.net/' . $match[1];
      break;
    }
    $string = str_replace($match[0], '<a ' . mmd_CSSclass($repstr, $strict) . 'href="' . $repstr . '">' . $match[1] . '</a>', $string);
  }

  /* horizontal line */
  while (preg_match($regexobject['hr'], $string, $match)) {
    $string = str_replace($match[0], "\n<hr/>\n", $string);
  }

  /* include */
  if ($strict !== true) {
    while (preg_match($regexobject['include'], $string, $match)) {
      $helper1 = trim(file_get_contents($match[2]));
      if ($match[1] === 'csv') {
        $helper2 = array(';'=> array(), '\t'=> array(), ','=> array(), '|'=> array());
        $helper2[0] = array(';', '\t', ',', '|');
        $helper1 = explode("\n", $helper1);
        for ($j = 0; $j < count($helper2[0]); $j++) {
          for ($i = 0; $i < count($helper1); $i++) {
            $helper2[$helper2[0][$j]][$i] = count(explode($helper2[0][$j], $helper1[$i]));
            if ($i > 0) {
              if ($helper2[$helper2[0][$j]] !== false) {
                if (($helper2[$helper2[0][$j]][$i] !== $helper2[$helper2[0][$j]][$i - 1]) || ($helper2[$helper2[0][$j]][$i] === 1)) {
                  $helper2[$helper2[0][$j]] = false;
                }
              }
            }
          }
        }
        if (($helper2[';'] !== false) || ($helper2['\t'] !== false) || ($helper2[','] !== false) || ($helper2['|'] !== false)) {
          if ($helper2[';'] !== false) {
            $helper2 = ';';
          } else if ($helper2['\t']) {
            $helper2 = '\t';
          } else if ($helper2[',']) {
            $helper2 = ',';
          } else if ($helper2['|']) {
            $helper2 = '|';
          }
          $repstr = '<table>';
          for ($i = 0; $i < count($helper1); $i++) {
            $helper = explode($helper2, $helper1[$i]);
            $repstr .= '<tr>';
            for ($j = 0; $j < count($helper); $j++) {
              $repstr .= '<td>' . htmlentities($helper[$j]) . '</td>';
            }
            $repstr .= '</tr>';
          }
          $repstr .= '</table>';
          $string = str_replace($match[0], $repstr, $string);
        } else {
          $string = str_replace($match[0], '<code>' . implode("\n", $helper1) . '</code>', $string);
        }
      } else {
        $string = str_replace($match[0], '', $string);
      }
    }
  }

  $string = preg_replace("/ {2,}[\n]{1,}/m", '<br/><br/>', $string);
  return $string;
}

?>