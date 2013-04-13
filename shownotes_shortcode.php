<?php

namespace Podlove\Modules\ShownotesShortcode;
use \Podlove\Model;

class Shownotes_Shortcode extends \Podlove\Modules\Base {
  protected $module_name = 'OSF Shownotes Shortcode';
  protected $module_description = 'Adds Shownotes to episodes.';
  public $osf_starttime = 0;

  public function load() {
    
    $osf_starttime = 0;
    include_once 'osf.php';
  }
}

?>
