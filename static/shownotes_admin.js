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

function importShownotes(textarea, importid, baseurl) {
  var ajax, ajaxTimeout, requrl;
  ajax = (window.ActiveXObject) ? new ActiveXObject("Microsoft.XMLHTTP") : (XMLHttpRequest && new XMLHttpRequest()) || null;
  
  ajaxTimeout = window.setTimeout(function () {
    ajax.abort();
  }, 6000);
  
  ajax.onreadystatechange = function () {
    var selectEle, itemsArray, itemsString, i;
    if (ajax.readyState === 4) {
      if (ajax.status === 200) {
        clearTimeout(ajaxTimeout);
        if (ajax.status !== 200) {
  
        } else {
          textarea.value = ajax.responseText;
        }
      }
    }
  };
  
  requrl = baseurl.replace("$$$", importid);
  ajax.open("GET", requrl, true);
  ajax.send();
}

function getPadList(select, podcastname) {
  var ajax, ajaxTimeout, requrl, padslist, returnstring = '';
  ajax = (window.ActiveXObject) ? new ActiveXObject("Microsoft.XMLHTTP") : (XMLHttpRequest && new XMLHttpRequest()) || null;
  
  ajaxTimeout = window.setTimeout(function () {
    ajax.abort();
  }, 6000);
  
  ajax.onreadystatechange = function () {
    var selectEle, itemsArray, itemsString, i;
    if (ajax.readyState === 4) {
      if (ajax.status === 200) {
        clearTimeout(ajaxTimeout);
        if (ajax.status !== 200) {
  
        } else {
          padslist = JSON.parse(ajax.responseText);
          for(var i = 0; i < padslist.length; i++) {
            returnstring += '<option>'+padslist[i].docname+'</option>';
          }
          select.innerHTML = returnstring;
        }
      }
    }
  };
  if(podcastname.trim() == "*") {
    requrl = 'http://cdn.simon.waldherr.eu/projects/showpad-api/getList/';
  } else {
    requrl = 'http://cdn.simon.waldherr.eu/projects/showpad-api/getList/?search='+podcastname.trim();
  }
  
  ajax.open("GET", requrl, true);
  ajax.send();
}

function templateAssociated (change) {
  var delimiterele, lastdelimiterele, chapterdelimiterele, cssele, i, j;
  delimiterele = document.getElementById('main_delimiter');
  lastdelimiterele = document.getElementById('main_last_delimiter');
  document.getElementById('main_md_shortcode').parentNode.parentNode.style.display = 'none';
  if(document.getElementById('main_mode').value == 'block style') {
    delimiterele.parentNode.parentNode.style.display = 'table-row';
    lastdelimiterele.parentNode.parentNode.style.display = 'table-row';
  } else if(document.getElementById('main_mode').value == 'button style') {
    delimiterele.parentNode.parentNode.style.display = 'none';
    lastdelimiterele.parentNode.parentNode.style.display = 'none';
  } else {
    delimiterele.parentNode.parentNode.style.display = 'none';
    lastdelimiterele.parentNode.parentNode.style.display = 'none';
  }
  if(change === 1) {
    if(document.getElementById('main_mode').value == 'button style') {
      document.getElementById('css_id').value = 3;
    }
  }
}

function previewPopup (shownotesElement, mode) {
  var shownotes = '';
  shownotesPopup = window.open('', "Shownotes Preview", "width=400,height=300,resizable=yes");
  if((mode === 'html')||(mode === 'source')) {
    shownotes = osfExport(osfParser(shownotesElement.value),osfExport_HTML);
  } else if(mode === 'md') {
    shownotes = osfExport(osfParser(shownotesElement.value),osfExport_Markdown);
  } else if(mode === 'wikigeeks') {
    shownotes = osfExport(osfParser(shownotesElement.value),osfExport_HTMLlist);
  } else if(mode === 'chapter') {
    shownotes = '<code style="white-space: pre;">'+osfExport(osfParser(shownotesElement.value),osfExport_Chapter)+'</code>';
  } else if(mode === 'glossary') {
    shownotes = osfExport(osfParser(shownotesElement.value),osfExport_Glossary);
  }
  shownotesPopup.document.write(shownotes);
  shownotesPopup.document.title = 'Shownotes Preview';
  shownotesPopup.focus();
  return false;
}
