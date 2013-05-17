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

function templateAssociated () {
  document.getElementById('main_md_shortcode').parentNode.parentNode.style.display = 'none';
  if(document.getElementById('main_mode').value == 'block style') {
    document.getElementById('main_delimiter').parentNode.parentNode.style.display = 'table-row';
    document.getElementById('main_last_delimiter').parentNode.parentNode.style.display = 'table-row';
    document.getElementById('main_chapter_delimiter').parentNode.parentNode.style.display = 'table-row';
  } else {
    document.getElementById('main_delimiter').parentNode.parentNode.style.display = 'none';
    document.getElementById('main_last_delimiter').parentNode.parentNode.style.display = 'none';
    document.getElementById('main_chapter_delimiter').parentNode.parentNode.style.display = 'none';
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
