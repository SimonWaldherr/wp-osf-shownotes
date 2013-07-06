/*
 * shownotes
 *
 * Copyright 2013, Simon Waldherr - http://simon.waldherr.eu/
 * Released under the MIT Licence
 * http://opensource.org/licenses/MIT
 *
 * Github:  https://github.com/SimonWaldherr/wp-osf-shownotes
 * Wordpress: http://wordpress.org/plugins/shownotes/
 * Version: 0.3.3
 */

function importShownotes(textarea, importid, baseurl) {
  var requrl;
  requrl = baseurl.replace("$$$", importid);
  majaX({url: requrl}, function (resp) {
    textarea.value = resp;
  });
}

function getPadList(select, podcastname) {
  var requrl, padslist, returnstring = '';
  if(podcastname.trim() == "*") {
    requrl = 'http://cdn.simon.waldherr.eu/projects/showpad-api/getList/';
  } else {
    requrl = 'http://cdn.simon.waldherr.eu/projects/showpad-api/getList/?search='+podcastname.trim();
  }
  
  majaX({url: requrl, type: 'json'}, function (resp) {
    padslist = resp;
    for(var i = 0; i < padslist.length; i++) {
      returnstring += '<option>'+padslist[i].docname+'</option>';
    }
    select.innerHTML = returnstring;
  })
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

function previewPopup (shownotesElement, emode, forceDL, apiurl) {
  var shownotes = '', action;
  //if((mode === 'html')||(mode === 'source')) {
  //  shownotes = osfExport(osfParser(shownotesElement.value),osfExportModules['html']);
  //} else if(mode === 'md') {
  //  shownotes = osfExport(osfParser(shownotesElement.value),osfExportModules[mode]);
  //} else if(mode === 'wikigeeks') {
  //  shownotes = osfExport(osfParser(shownotesElement.value),osfExportModules[mode]);
  //} else if(mode === 'chapter') {
  //  shownotes = '<code style="white-space: pre;">'+osfExport(osfParser(shownotesElement.value),osfExportModules[mode])+'</code>';
  //} else if(mode === 'glossary') {
  //  shownotes = osfExport(osfParser(shownotesElement.value),osfExportModules[mode]);
  //}

  if (forceDL === true) {
    forceDL = 'true';
  }
  
  action = document.forms["post"].action;
  console.log(action);
  return false;
  document.forms["post"].action = apiurl;
  
  majaX({url:apiurl,method:'POST',data:{fdl:forceDL,mode:emode,shownotes:encodeURIComponent(shownotesElement.value)}}, function (resp) {
    if (forceDL !== 'true') {
      shownotesPopup = window.open('', "Shownotes Preview", "width=400,height=300,resizable=yes");
      shownotesPopup.document.write(resp);
      shownotesPopup.document.title = 'Shownotes Preview';
      shownotesPopup.focus();
    }
  });
  document.forms["post"].action = action;
  //return false;
}
