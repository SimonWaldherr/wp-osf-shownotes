<?php

if (is_admin()) {
    add_action('admin_menu', 'podloveshownotes_create_menu');
    add_action('admin_init', 'podloveshownotes_register_settings');
}

function podloveshownotes_settings_page() {
?>
 <div class="wrap">
    <h2>Podlove Shownotes Options</h2>
    <form method="post" action="options.php">
      <?php
    settings_fields('podloveshownotes_options');
?>
     <?php
    do_settings_sections('podloveshownotes');
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


function podloveshownotes_create_menu() {
    add_options_page('Podlove Shownotes Options', 'Podlove Shownotes', 'manage_options', 'podloveshownotes', 'podloveshownotes_settings_page');
}

function podloveshownotes_register_settings() {
    $ps = 'podloveshownotes';

    $settings = array(
        'affiliate' => array(
            'title' => 'Affiliate',
            'fields' => array(
                'amazon' => 'Amazon',
                'thomann' => 'Thomann',
                'tradedoubler' => 'Tradedoubler'
            )
        ),
        'completeness' => array(
            'title' => 'Completeness',
            'fields' => array(
                'fullmode' => 'Export All'
            )
        ),
        'info' => array(
            'title' => 'Information',
            'function' => true
        )
    );

    register_setting('podloveshownotes_options', 'podloveshownotes_options');

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


function podloveshownotes_affiliate_amazon() {
    $options = get_option('podloveshownotes_options');
    if (!isset($options['affiliate_amazon'])) {
        $options['affiliate_amazon'] = "";
    }
    print "<input id='affiliate_amazon' name='podloveshownotes_options[affiliate_amazon]' 
    value='" . $options['affiliate_amazon'] . "' style='width:8em;' />";
}

function podloveshownotes_affiliate_thomann() {
    $options = get_option('podloveshownotes_options');
    if (!isset($options['affiliate_thomann'])) {
        $options['affiliate_thomann'] = "";
    }
    print "<input id='affiliate_thomann' name='podloveshownotes_options[affiliate_thomann]' 
    value='" . $options['affiliate_thomann'] . "' style='width:8em;' />";
}

function podloveshownotes_affiliate_tradedoubler() {
    $options = get_option('podloveshownotes_options');
    if (!isset($options['affiliate_tradedoubler'])) {
        $options['affiliate_tradedoubler'] = "";
    }
    print "<input id='affiliate_tradedoubler' name='podloveshownotes_options[affiliate_tradedoubler]' 
    value='" . $options['affiliate_tradedoubler'] . "' style='width:8em;' />";
}

function podloveshownotes_completeness_fullmode() {
    $options = get_option('podloveshownotes_options');
    $checked = "";
    if (isset($options['completeness_fullmode']))
        $checked = "checked ";
    print "<input id='completeness_fullmode' name='podloveshownotes_options[completeness_fullmode]' 
    $checked type='checkbox' value='1' />";
}

function podloveshownotes_info() {
    $scriptname = explode('/wp-admin', $_SERVER["SCRIPT_FILENAME"]);
    $dirname    = explode('/wp-content', dirname(__FILE__));
    print '<p>This is <strong>Version 0.0.2</strong> of the <strong>Podlove Shownotes</strong>.<br>
  The <strong>Including file</strong> is: <code>wp-admin' . $scriptname[1] . '</code><br>
  The <strong>ps-directory</strong> is: <code>wp-content' . $dirname[1] . '</code></p>
  <p>Want to contribute? Found a bug? Need some help? <br/>you can found our github repo/page at
  <a href="https://github.com/SimonWaldherr/podlove-shownotes">github.com/SimonWaldherr/podlove-shownotes</a></p>
  <p>If you found a bug, please tell us your WP- and ps- (and PPP- if you use PPP) Version. <br/>Also your 
  Browser version, your PHP version and the URL of your Podcast can help us, find the bug.</p>';
}

?>
