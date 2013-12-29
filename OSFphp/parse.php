<?php

function is_feed() {
  return false;
}
function get_the_ID() {
  return '1';
}

function parserWrapper($pad) {
  $encodedData = str_replace(' ','+',$pad);
  $shownotesString = str_replace("\n", " \n", "\n" . base64_decode($encodedData) . "\n");
  $shownotesString = $pad;

  $mode = 'shownot.es';

  $shownotes_options['main_delimiter'] = '';
  $shownotes_options['main_last_delimiter'] = '';
  $osf_starttime = 0;

  $fullmode             = 'true';
  $fullint              = 2;
  $tags                 = explode(' ', 'chapter section spoiler topic embed video audio image shopping glossary source app title quote link podcast news');
  $data['tags']         = $tags;
  $data['fullmode']     = $fullmode;
  $data['amazon']       = 'shownot.es-21';
  $data['thomann']      = '93439';
  $data['tradedoubler'] = '16248286';

  $shownotesArray = osf_parser($shownotesString, $data);

  $return['podcast'] = osf_get_podcastname($shownotesArray['header']);
  $return['episode'] = str_replace($return['podcast'], '', osf_get_episodenumber($shownotesArray['header']));
  $return['shownoter'] = osf_get_persons('shownoter', $shownotesArray['header']);
  $return['podcaster'] = osf_get_persons('podcaster', $shownotesArray['header']);
  $return['episodetime'] = osf_get_episodetime($shownotesArray['header']);
  $return['subject'] = osf_get_episodename($shownotesArray['header']);

  $return['json'] = json_encode($shownotesArray['export']);
  $return['chapter'] = osf_export_chapterlist($shownotesArray['export']);
  $return['osf'] = osf_export_osf($shownotesArray['export']);
  return $return;
}

function parserWrapperAPI($postdata) {
  if (isset($postdata['amazon']) && $postdata['amazon'] != '') {
    $amazon = $postdata['amazon'];
  } else {
    $amazon = 'shownot.es-21';
  }
  if (isset($postdata['thomann']) && $postdata['thomann'] != '') {
    $thomann = $postdata['thomann'];
  } else {
    $thomann = '93439';
  }
  if (isset($postdata['tradedoubler']) && $postdata['tradedoubler'] != '') {
    $tradedoubler = $postdata['tradedoubler'];
  } else {
    $tradedoubler = '16248286';
  }

  $fullmode = 'false';
  $tags = $postdata['tags'];

  if ($tags == '') {
    $fullmode = 'true';
    $fullint = 2;
    $tags = explode(' ', 'chapter section spoiler topic embed video audio image shopping glossary source app title quote link podcast news');
  } else {
    $fullint = 1;
    if (!is_array($tags)) {
      $tags = explode(' ', $tags);
    }
  }

  $data = array(
    'amazon'       => $amazon,
    'thomann'      => $thomann,
    'tradedoubler' => $tradedoubler,
    'fullmode'     => $fullmode,
    'tagsmode'     => 'include',
    'tags'         => $tags
  );

  $encodedData = str_replace(' ','+',$postdata['pad']);
  $shownotesString = str_replace("\n", " \n", "\n" . base64_decode($encodedData) . "\n");

  $mode = $postdata['mainmode'];

  if ($mode == 'block') {
    $mode = 'block style';
  }
  if ($mode == 'list') {
    $mode = 'list style';
  }
  if ($mode == 'osf') {
    $mode = 'clean osf';
  }

  $shownotes_options['main_delimiter'] = '';
  $shownotes_options['main_last_delimiter'] = '';
  $osf_starttime = 0;

  if ($mode == 'shownot.es') {
    $fullmode             = 'true';
    $fullint              = 2;
    $tags                 = explode(' ', 'chapter section spoiler topic embed video audio image shopping glossary source app title quote link podcast news');
    $data['tags']         = $tags;
    $data['fullmode']     = $fullmode;
    $data['amazon']       = 'shownot.es-21';
    $data['thomann']      = '93439';
    $data['tradedoubler'] = '16248286';
  }

  $shownotesArray = osf_parser($shownotesString, $data);

  if ($mode == 'shownot.es') {
    $fullmode             = 'true';
    $fullint              = 2;
    $tags                 = explode(' ', 'chapter section spoiler topic embed video audio image shopping glossary source app title quote link podcast news');
    $data['tags']         = $tags;
    $data['fullmode']     = $fullmode;
    $data['amazon']       = 'shownot.es-21';
    $data['thomann']      = '93439';
    $data['tradedoubler'] = '16248286';
    $export = '<div class="info">  <div class="thispodcast">  <div class="podcastimg">  <img src="" alt="Logo">  </div> <?php  include "./../episodeselector.php"; insertselector();  ?>  </div>  <div class="episodeinfo">  <table>  <tr>  <td>Podcast</td><td><a href="#"></a></td>  </tr>  <tr>  <td>Episode</td><td><a href="#"></a></td>  </tr>  <tr>  <td>Sendung vom</td><td>'.date("j. M Y").'</td>  </tr>  <tr>  <td>Podcaster</td><td>'.osf_get_persons('podcaster', $shownotesArray['header']).'</td>  </tr>  <tr>  <td>Shownoter</td>  <td>'.osf_get_persons('shownoter', $shownotesArray['header']).'</td>  </tr>  </table>  </div> </div><br/><br/>'."\n\n";

    $export .= osf_export_block($shownotesArray['export'], 2, 'block style');
  } elseif (($mode == 'block style') || ($mode == 'button style')) {
    $export = osf_export_block($shownotesArray['export'], $fullint, $mode);
  } elseif ($mode == 'list style') {
    $export = osf_export_list($shownotesArray['export'], $fullint, $mode);
  } elseif ($mode == 'clean osf') {
    $export = osf_export_osf($shownotesArray['export'], $fullint, $mode);
  } elseif ($mode == 'glossary') {
    $export = osf_export_glossary($shownotesArray['export'], $fullint);
  } elseif (($mode == 'shownoter') || ($mode == 'podcaster')) {
    if (isset($shownotesArray['header'])) {
      if ($mode == 'shownoter') {
        $export = osf_get_persons('shownoter', $shownotesArray['header']);
      } elseif ($mode == 'podcaster') {
        $export = osf_get_persons('podcaster', $shownotesArray['header']);
      }
    }
  } elseif ($mode == 'JSON') {
    $export = json_encode($shownotesArray['export']);
  } elseif ($mode == 'Chapter') {
    $export = osf_export_chapterlist($shownotesArray['export']);
  } elseif ($mode == 'PSC') {
    $export = osf_export_psc($shownotesArray['export']);
  }

  return $export;
}

?>