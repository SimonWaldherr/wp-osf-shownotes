/*
 * shownotes
 *
 * Copyright 2013, Simon Waldherr - http://simon.waldherr.eu/
 * Released under the MIT Licence
 * http://opensource.org/licenses/MIT
 *
 * Github:  https://github.com/SimonWaldherr/wp-osf-shownotes
 * Wordpress: http://wordpress.org/plugins/shownotes/
 * Version: 0.3.5
 */

/*jslint browser: true, indent: 2 */
/*global majaX, shownotesname */

function importShownotes(textarea, importid, baseurl) {
  "use strict";
  var requrl;
  requrl = baseurl.replace("$$$", importid);
  majaX({url: requrl}, function (resp) {
    textarea.value = resp;
  });
}

function getPadList(select, podcastname) {
  "use strict";
  var requrl,
    padslist,
    returnstring = '',
    i;

  if (podcastname.trim() === "*") {
    requrl = 'http://cdn.simon.waldherr.eu/projects/showpad-api/getList/';
  } else {
    requrl = 'http://cdn.simon.waldherr.eu/projects/showpad-api/getList/?search=' + podcastname.trim();
  }

  majaX({url: requrl, type: 'json'}, function (resp) {
    padslist = resp;
    for (i = 0; i < padslist.length; i += 1) {
      if (shownotesname === padslist[i].docname) {
        returnstring += '<option selected>' + padslist[i].docname + '</option>';
      } else {
        returnstring += '<option>' + padslist[i].docname + '</option>';
      }
    }
    select.innerHTML = returnstring;
  });
}

function templateAssociated(change) {
  "use strict";
  var delimiterele, lastdelimiterele;
  delimiterele = document.getElementById('main_delimiter');
  lastdelimiterele = document.getElementById('main_last_delimiter');
  document.getElementById('main_md_shortcode').parentNode.parentNode.style.display = 'none';
  if (document.getElementById('main_mode').value === 'block style') {
    delimiterele.parentNode.parentNode.style.display = 'table-row';
    lastdelimiterele.parentNode.parentNode.style.display = 'table-row';
  } else if (document.getElementById('main_mode').value === 'button style') {
    delimiterele.parentNode.parentNode.style.display = 'none';
    lastdelimiterele.parentNode.parentNode.style.display = 'none';
  } else {
    delimiterele.parentNode.parentNode.style.display = 'none';
    lastdelimiterele.parentNode.parentNode.style.display = 'none';
  }
  if (change === 1) {
    if (document.getElementById('main_mode').value === 'button style') {
      document.getElementById('css_id').value = 3;
    }
  }
}

function previewPopup(shownotesElement, emode, forceDL, apiurl) {
  "use strict";
  var preview = 'true',
    shownotesPopup;

  if (forceDL === true) {
    forceDL = 'true';
    preview = 'false';
  }

  majaX({url: apiurl + '/api.php', method: 'POST', data: {'fdl': forceDL, 'mode': emode, 'preview': preview, 'shownotes': encodeURIComponent(shownotesElement.value)}}, function (resp) {
    if (forceDL !== 'true') {
      shownotesPopup = window.open('', "Shownotes Preview", "width=1024,height=768,resizable=yes");
      shownotesPopup.document.write(resp);
      shownotesPopup.document.title = 'Shownotes Preview';
      shownotesPopup.focus();
    } else {
      window.location = apiurl + '/api.php?fdlid=' + resp + '&fdname=' + document.getElementById('title').value;
    }
  });

  return false;
}
