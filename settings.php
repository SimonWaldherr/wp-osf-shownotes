<?php

if (is_admin()) {
    add_action('admin_menu', 'shownotes_create_menu');
    add_action('admin_init', 'shownotes_register_settings');
}

function shownotes_settings_page() {
?>
 <div class="wrap">
    <h2> Shownotes Options</h2>
    <form method="post" action="options.php">
      <?php
    settings_fields('shownotes_options');
?>
     <?php
    do_settings_sections('shownotes');
?>
     <p class="submit">
       <input name="Submit" type="submit" class="button button-primary" value="<?php
    esc_attr_e('Save Changes');
?>" />
      </p>
    </form>
  </div>
<?php
}


function shownotes_create_menu() {
    add_options_page(' Shownotes Options', ' Shownotes', 'manage_options', 'shownotes', 'shownotes_settings_page');
}

function shownotes_register_settings() {
    $ps = 'shownotes';

    $settings = array(
        'main' => array(
            'title'  => 'General Settings',
            'fields' => array(
                'mode'          => 'Select template',
                'tags'          => 'Only include items with certain tags',
                'delimiter'     => 'Choose string between items in block',
                'css_id'        => 'Select a CSS-File',
                'osf_shortcode' => 'Choose your osf shortcode',
                'md_shortcode'  => 'Choose your md shortcode'
            )
        ),
        'affiliate' => array(
            'title'  => 'Affiliate',
            'fields' => array(
                'amazon'       => 'Amazon.de',
                'thomann'      => 'Thomann.de',
                'tradedoubler' => 'Tradedoubler'
            )
        ),
        'info' => array(
            'title'    => 'Information',
            'function' => true
        )
    );

    register_setting('shownotes_options', 'shownotes_options');

    foreach ($settings as $sectionname => $section) {
        $function = false;
        if (isset($section['function'])) {
            $function = $ps . '_' . $sectionname;
        }
        add_settings_section($ps . '_' . $sectionname, $section['title'], $function, $ps);
        if (isset($section['fields'])) {
            $i = 0;
            foreach ($section['fields'] as $fieldname => $description) {
                $i += 1;
                add_settings_field($ps . '_' . $sectionname . '_' . $fieldname, $description, $ps . '_' . $sectionname . '_' . $fieldname, $ps, $ps . '_' . $sectionname, array(
                    'label_for' => 'ps' . $sectionname . $i
                ));
            }
        }
    }
}


function shownotes_affiliate_amazon() {
    $options = get_option('shownotes_options');
    if (!isset($options['affiliate_amazon'])) {
        $options['affiliate_amazon'] = "";
    }
    print '<input id="affiliate_amazon" name="shownotes_options[affiliate_amazon]" value="' . $options['affiliate_amazon'] . '" style="width:8em;" /> <i> e.g.: shownot.es-21</i>';
}

function shownotes_affiliate_thomann() {
    $options = get_option('shownotes_options');
    if (!isset($options['affiliate_thomann'])) {
        $options['affiliate_thomann'] = "";
    }
    print '<input id="affiliate_thomann" name="shownotes_options[affiliate_thomann]" value="' . $options['affiliate_thomann'] . '" style="width:8em;" /> <i> e.g.: 93439</i>';
}

function shownotes_affiliate_tradedoubler() {
    $options = get_option('shownotes_options');
    if (!isset($options['affiliate_tradedoubler'])) {
        $options['affiliate_tradedoubler'] = "";
    }
    print '<input id="affiliate_tradedoubler" name="shownotes_options[affiliate_tradedoubler]" value="' . $options['affiliate_tradedoubler'] . '" style="width:8em;" /> <i> e.g.: 16248286</i>';
}

function shownotes_completeness_fullmode() {
    $options = get_option('shownotes_options');
    $checked = "";
    if (isset($options['completeness_fullmode']))
        $checked = "checked ";
    print '<input id="completeness_fullmode" name="shownotes_options[completeness_fullmode]" ' . $checked . ' type="checkbox" value="1" />';
}

function shownotes_main_mode() {
    $options = get_option('shownotes_options');
    $modes = array('block style', 'list style', 'glossary', 'shownoter');
    print '<select id="main_mode" name="shownotes_options[main_mode]">';
    foreach($modes as $mode) {
        if($mode == $options['main_mode']) {
            print '<option selected>'.$mode.'</option>';
        } else {
            print '<option>'.$mode.'</option>';
        }
    }
    print "<select/>";
}

function shownotes_main_css_id() {
    $options  = get_option('shownotes_options');
    $cssnames = array('none', 'icons after items', 'icons before items');
    $i = 0;
    print '<select id="css_id" name="shownotes_options[css_id]">';
    foreach($cssnames as $cssname) {
        if($i == $options['css_id']) {
            print '<option value="'.$i.'" selected>'.$cssname.'</option>';
        } else {
            print '<option value="'.$i.'">'.$cssname.'</option>';
        }
        ++$i;
    }
    print "<select/>";
}

function shownotes_import_baseurl() {
    $options = get_option('shownotes_options');
    if (!isset($options['import_baseurl'])) {
        $options['import_baseurl'] = "";
    }
    print '<input id="import_baseurl" name="shownotes_options[import_baseurl]" value="' . $options['import_baseurl'] . '" style="width:18em;" /> <i>&nbsp; enter \$\$\$ at Episode ID Position &nbsp;(e.g.: http://tools.shownot.es/showpadapi/?podcast=nsfw-&episode=\$\$\$)</i>';
}

function shownotes_main_tags() {
    $options = get_option('shownotes_options');
    if (!isset($options['main_tags'])) {
        $options['main_tags'] = "";
    }
    print '<input id="main_tags" name="shownotes_options[main_tags]" value="' . $options['main_tags'] . '" style="width:18em;" /> <i>&nbsp; split by space &nbsp;(leave empty to main all tags)</i>';
}

function shownotes_main_delimiter() {
    $options = get_option('shownotes_options');
    if (!isset($options['main_delimiter'])) {
        $options['main_delimiter'] = ' &nbsp;';
    }
    print '<input id="main_delimiter" name="shownotes_options[main_delimiter]" value="' . $options['main_delimiter'] . '" style="width:8em;" /> <i>&nbsp; e.g.: <code>'.htmlspecialchars('&nbsp;-&nbsp;').'</code> </i>';
}

function shownotes_main_css() {
    $options = get_option('shownotes_options');
    $checked = '';
    if (isset($options['main_css']))
        $checked = "checked ";
    print '<input id="main_css" name="shownotes_options[main_css]" ' . $checked . ' type="checkbox" value="1" /> <i>&nbsp; adds icons for tags</i>';
}

function shownotes_main_osf_shortcode() {
    $options = get_option('shownotes_options');
    if (!isset($options['main_osf_shortcode'])) {
        $options['main_osf_shortcode'] = 'shownotes';
    }
    print '<input id="main_osf_shortcode" name="shownotes_options[main_osf_shortcode]" value="' . $options['main_osf_shortcode'] . '" style="width:8em;" />';
}

function shownotes_main_md_shortcode() {
    $options = get_option('shownotes_options');
    if (!isset($options['main_md_shortcode'])) {
        $options['main_md_shortcode'] = 'md-shownotes';
    }
    print '<input id="main_md_shortcode" name="shownotes_options[main_md_shortcode]" value="' . $options['main_md_shortcode'] . '" style="width:8em;" />';
}

function shownotes_info() {
    $scriptname = explode('/wp-admin', $_SERVER["SCRIPT_FILENAME"]);
    $dirname    = explode('/wp-content', dirname(__FILE__));
    print '<p>This is <strong>Version 0.1.1</strong> of the <strong> Shownotes</strong>.<br>
  The <strong>Including file</strong> is: <code>wp-admin' . $scriptname[1] . '</code><br>
  The <strong>plugin-directory</strong> is: <code>wp-content' . $dirname[1] . '</code></p>
  <p>Want to contribute? Found a bug? Need some help? <br/>you can found our github repo/page at
  <a href="https://github.com/SimonWaldherr/wp-osf-shownotes">github.com/SimonWaldherr/wp-osf-shownotes</a></p>
  <p>If you found a bug, please tell us your WP- and ps- (and PPP- if you use PPP) Version. <br/>Also your 
  Browser version, your PHP version and the URL of your Podcast can help us, find the bug.</p>';
}

?>
