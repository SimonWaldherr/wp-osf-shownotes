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
