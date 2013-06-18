<?php

/**
 * @package Shownotes
 * @version 0.3.3
 */

/*
Plugin Name: Shownotes
Plugin URI: http://shownot.es/wp-plugin/
Description: Convert OSF-Shownotes to HTML for your Podcast
Author: Simon Waldherr
Version: 0.3.3
Author URI: http://waldherr.eu
License: MIT License
*/

include_once 'settings.php';
include_once 'osf.php';
$shownotes_options = get_option('shownotes_options');

function shownotesshortcode_add_styles() {
  global $shownotes_options;
  if (!isset($shownotes_options['css_id'])) {
    return false;
  }
  if ($shownotes_options['css_id'] == '0') {
    return false;
  }
  $css_styles = array(
    '',
    'style_one',
    'style_two',
    'style_three',
    'style_four',
    'style_five'
  );

  wp_enqueue_style('shownotesstyle', plugins_url('static/' . $css_styles[$shownotes_options['css_id']] . '.css', __FILE__), array(), '0.3.3');
}
add_action('wp_print_styles', 'shownotesshortcode_add_styles');

function add_shownotes_textarea($post) {
  global $shownotes_options;
  $post_id = @get_the_ID();
  if ($post_id == '') {
    return;
  }
  if (isset($post_id)) {
    $shownotes = get_post_meta($post_id, '_shownotes', true);
    if ($shownotes == "") {
      $shownotes = get_post_meta($post_id, 'shownotes', true);
    }
  } else {
    $shownotes = '';
  }
  $baseurl = 'http://tools.shownot.es/showpadapi/?id=$$$';
  $baseurlstring = '';
  $import_podcastname = false;
  if (isset($shownotes_options['import_podcastname'])) {
    if (trim($shownotes_options['import_podcastname']) != "") {
      $import_podcastname = trim($shownotes_options['import_podcastname']);
    }
  }
  if ($import_podcastname == false) {
    $baseurlstring = '<input type="text" id="importId" name="" class="form-input-tip" size="16" autocomplete="off" value=""> <input type="button" class="button" onclick="importShownotes(document.getElementById(\'shownotes\'), document.getElementById(\'importId\').value, \'' . $baseurl . '\')" value="Import">';
  } else {
    $baseurlstring = '<select id="importId" size="1"></select> <input type="button" class="button" onclick="importShownotes(document.getElementById(\'shownotes\'), document.getElementById(\'importId\').value, \'' . $baseurl . '\')" value="Import"><script>getPadList(document.getElementById(\'importId\'),\'' . $import_podcastname . '\')</script>';
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
  $export = '';
  $post_id = get_the_ID();
  $shownotes = get_post_meta($post_id, '_shownotes', true);
  if ($shownotes == "") {
    $shownotes = get_post_meta($post_id, 'shownotes', true);
  }
  if (isset($shownotes_options['main_tags_mode'])) {
    $tags_mode = trim($shownotes_options['main_tags_mode']);
  } else {
    $tags_mode = 'include';
  }
  if (isset($shownotes_options['main_tags'])) {
    $default_tags = trim($shownotes_options['main_tags']);
  } else {
    $default_tags = '';
  }
  if (isset($shownotes_options['main_tags_feed'])) {
    $feed_tags = trim($shownotes_options['main_tags_feed']);
  } else {
    $feed_tags = '';
  }
  extract(shortcode_atts(array(
    'template'  => $shownotes_options['main_mode'],
    'mode'      => $shownotes_options['main_mode'],
    'tags_mode' => $tags_mode,
    'tags'      => $default_tags,
    'feedtags'  => $feed_tags
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
    if (is_feed()) {
      $tags = $feedtags;
    }
    if ($tags == '') {
      $fullmode = 'true';
      $fullint = 2;
      $tags = explode(' ', 'chapter section spoiler topic embed video audio image shopping glossary source app title quote link podcast news');
    } else {
      $fullint = 1;
      $tags = explode(' ', $tags);
    }
    $data = array(
      'amazon'       => $amazon,
      'thomann'      => $thomann,
      'tradedoubler' => $tradedoubler,
      'fullmode'     => $fullmode,
      'tagsmode'     => $tags_mode,
      'tags'         => $tags
    );
    //undo fucking wordpress shortcode cripple shit
    if ($content !== "") {
      $shownotesString = htmlspecialchars_decode(str_replace('<br />', '', str_replace('<p>', '', str_replace('</p>', '', $content))));
    } else {
      $shownotesString = "\n" . $shownotes . "\n";
    }
    //parse shortcode as osf string to html
    if ($template !== $shownotes_options['main_mode']) {
      $mode = $template;
    }

    if ($mode == 'block') {
      $mode = 'block style';
    }
    if ($mode == 'list') {
      $mode = 'list style';
    }
    if ($mode == 'osf') {
      $mode = 'clean osf';
    }

    $shownotesArray = osf_parser($shownotesString, $data);
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
    if (isset($_GET['debug']) && (!is_feed())) {
      $export .= '<textarea>' . json_encode($shownotesArray) . '</textarea><textarea>' . print_r($shownotes_options, true) . htmlspecialchars($shownotesString) . '</textarea>';
    }
  }

  return $export;
}

function md_shownotes_shortcode($atts, $content = "") {
  $post_id   = get_the_ID();
  $shownotes = get_post_meta($post_id, '_shownotes', true);
  if ($shownotes == "") {
    $shownotes = get_post_meta($post_id, 'shownotes', true);
  }
  if ($content !== "") {
    $shownotesString = htmlspecialchars_decode(str_replace('<br />', '', str_replace('<p>', '', str_replace('</p>', '', $content))));
  } else {
    $shownotesString = "\n" . $shownotes . "\n";
  }

  return markdown($shownotesString);
}

if (!isset($shownotes_options['main_osf_shortcode'])) {
  $osf_shortcode = 'shownotes';
} else {
  $osf_shortcode = $shownotes_options['main_osf_shortcode'];
}

if (!isset($shownotes_options['main_md_shortcode'])) {
  $md_shortcode = 'md-shownotes';
} else {
  $md_shortcode = $shownotes_options['main_md_shortcode'];
}

add_shortcode($md_shortcode, 'md_shownotes_shortcode');
add_shortcode($osf_shortcode, 'osf_shownotes_shortcode');
if ($osf_shortcode != 'osf-shownotes') {
  add_shortcode('osf-shownotes', 'osf_shownotes_shortcode');
}

function shownotesshortcode_add_admin_scripts() {
  wp_enqueue_script('majax', plugins_url('static/majaX/majax.js', __FILE__), array(), '0.3.3', false);
  wp_enqueue_script('importPad', plugins_url('static/shownotes_admin.js', __FILE__), array(), '0.3.3', false);
  wp_enqueue_script('tinyosf', plugins_url('static/tinyOSF/tinyosf.js', __FILE__), array(), '0.3.3', false);
  wp_enqueue_script('tinyosf_exportmodules', plugins_url('static/tinyOSF/tinyosf_exportmodules.js', __FILE__), array(), '0.3.3', false);
}
function shownotesshortcode_add_scripts() {
  wp_enqueue_script('importPad', plugins_url('static/shownotes.js', __FILE__), array(), '0.3.3', false);
}
if (is_admin()) {
  add_action('wp_print_scripts', 'shownotesshortcode_add_admin_scripts');
}
add_action('wp_print_scripts', 'shownotesshortcode_add_scripts');

?>
