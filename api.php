<?php

//include_once 'settings.php';
include_once 'osf.php';
//$shownotes_options = get_option('shownotes_options');

$fdl        = $_POST['fdl'];
$emode     = $_POST['emode'];
$shownotes = urldecode($_POST['shownotes']);

if (isset($fdl)) {
  header("Content-Disposition: attachment; filename=\"shownotes.txt\"");
}


$amazon = 'shownot.es-21';
$thomann = '93439';
$tradedoubler = '16248286';
$fullmode = 'false';
$fullmode = 'true';
$fullint = 2;
$tags = explode(' ', 'chapter section spoiler topic embed video audio image shopping glossary source app title quote link podcast news');
$data = array(
  'amazon'       => $amazon,
  'thomann'      => $thomann,
  'tradedoubler' => $tradedoubler,
  'fullmode'     => $fullmode,
  'tagsmode'     => $tags_mode,
  'tags'         => $tags
);

$shownotesArray = osf_parser($shownotes, $data);

//echo json_encode($shownotesArray);

$mode = 'block';

if ($mode == 'block') {
  $mode = 'block style';
}
if ($mode == 'list') {
  $mode = 'list style';
}
if ($mode == 'osf') {
  $mode = 'clean osf';
}

if ($emode == 'html') {
  if (($mode == 'block style') || ($mode == 'button style')) {
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
  }
} else {
  $export = osf_export_chapterlist($shownotesArray['export'], $fullint);
}

echo $export;

?>
