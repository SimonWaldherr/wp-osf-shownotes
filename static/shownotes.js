/*
 * shownotes
 *
 * Copyright 2013, Simon Waldherr - http://simon.waldherr.eu/
 * Released under the MIT Licence
 * http://opensource.org/licenses/MIT
 *
 * Github:  https://github.com/SimonWaldherr/wp-osf-shownotes
 * Wordpress: http://wordpress.org/plugins/shownotes/
 * Version: 0.3.1
 */

function hashTime() {
  document.location.hash = '#t='+this.innerHTML;
  return false;
}

function osf_init(divid, template) {
  if(template === 'button') {
    var i, timebuttons, div = document.getElementById('osf_usnid_'+divid);
    timebuttons = div.getElementsByClassName('osf_timebutton');
    for(i = 0; i < timebuttons.length; i++) {
      timebuttons[i].onclick = hashTime;
    }
  }
}

