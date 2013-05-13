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
