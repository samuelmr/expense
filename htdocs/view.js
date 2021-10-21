
var SELECTLISTID = 'type';

var selectedCat;
var selectedType;

var initPlot;
var h1, h2;

function inArray(arr, key) {
  var i;
  for (i=0; i<arr.length; i++) {
    if (arr[i] == key) {
      return i;
    }
  }
  return -1;
}

function eventTarget(e) {
  var targ;
  var e = e || window.event;
  if (e.target) {
    targ = e.target;
  }
  else if (e.srcElement) {
    targ = e.srcElement;
  }
  if (targ.nodeType == 3) {
    // defeat Safari bug
    targ = targ.parentNode;
  }
  return targ;
}

function submitMe(e) {
  // var targ = eventTarget(e);
  var targ = this;
  if (targ && targ.form && targ.form.submit) {
    targ.form.submit();
  }
}

var winOpts = new Array();
winOpts['toolbar'] = 'yes';
winOpts['scrollbars'] = 'yes';
winOpts['location'] = 'yes';
winOpts['status'] = 'yes';
winOpts['menubar'] = 'yes';
winOpts['resizable'] = 'yes';
winOpts['directories'] = 'yes';

function newWin (url, name, winAttrs) {
  var attrs = winAttrs ? winAttrs : new Array();
  var o = new String();
  if (attrs['toolbar'] && (attrs['toolbar'] != 'undefined')) {
    o += 'toolbar=' + attrs['toolbar'];
  }
  else if (winOpts['toolbar'] && (winOpts['toolbar'] != 'undefined')) {
    o += 'toolbar=' + winOpts['toolbar'];
  }
  else {
    o += 'toolbar=no';
  }
  if (attrs['scrollbars'] && (attrs['scrollbars'] != 'undefined')) {
    o += ', scrollbars=' + attrs['scrollbars'];
  }
  else if (winOpts['scrollbars'] && (winOpts['scrollbars'] != 'undefined')) {
    o += ', scrollbars=' + winOpts['scrollbars'];
  }
  if (attrs['location'] && (attrs['location'] != 'undefined')) {
    o += ', location=' + attrs['location'];
  }
  else if (winOpts['location'] && (winOpts['location'] != 'undefined')) {
    o += ', location=' +winOpts['location'];
  }
  if (attrs['status'] && (attrs['status'] != 'undefined')) {
    o += ', status=' + attrs['status'];
  }
  else if (winOpts['status'] && (winOpts['status'] != 'undefined')) {
    o += ', status=' + winOpts['status'];
  }
  if (attrs['menubar'] && (attrs['menubar'] != 'undefined')) {
    o += ', menubar=' + attrs['menubar'];
  }
  else if (winOpts['menubar'] && (winOpts['menubar'] != 'undefined')) {
    o += ', menubar=' + winOpts['menubar'];
  }
  if (attrs['resizable'] && (attrs['resizable'] != 'undefined')) {
    o += ', resizable=' + attrs['resizable'];
  }
  else if (winOpts['resizable'] && (winOpts['resizable'] != 'undefined')) {
    o += ', resizable=' + winOpts['resizable'];
  }
  if (attrs['directories'] && (attrs['directories'] != 'undefined')) {
    o += ', directories=' + attrs['directories'];
  }
  else if (winOpts['directories'] && (winOpts['directories'] != 'undefined')) {
    o += ', directories=' + winOpts['directories'];
  }
  if (attrs['width'] && (attrs['width'] != 'undefined')) {
    o += ', width=' + attrs['width'];
  }
  if (attrs['height'] && (attrs['height'] != 'undefined')) {
    o += ', height=' + attrs['height'];
  }
  var w = window.open(url, name, o);
  if (w && w.focus) {
    w.focus();
  }
  return w;
}

function insertWindowOpen(e) {
  // var targ = eventTarget(e);
  // if (targ.nodeName == 'IMG') {
  //  targ = targ.parentNode;
  // }
  var targ = this;
  if (targ && targ.getAttribute && targ.getAttribute('href')) {
    var url = targ.getAttribute('href');
    if (newWin(url, 'insWin', {'width':755, 'height':775})) {
      return false;
    }
  }
  return true;
}

function replaceSelectList(id) {
  var i;
  var sl = document.getElementById(id);
  if (!sl) {
    return false;
  }
  var sl1 = document.createElement('select');
  sl1.setAttribute('name', 'cat');
  var sl2 = document.createElement('select');
  sl2.setAttribute('name', sl.getAttribute('name'));
  sl2.setAttribute('id', id);
  var first = sl.getElementsByTagName('option');
  if (!first || !first[0]) return false;
  sl2.appendChild(first[0]);
  if (first[0].value.indexOf('.') < 0) {
    var oc = document.createElement('option');
    oc.setAttribute('value', '');
    var t = first[0].childNodes[0].nodeValue;
    oc.appendChild(document.createTextNode(t));
    sl1.appendChild(oc);
  }
  optg = sl.getElementsByTagName('optgroup');
  if (!optg) return false;
  for (i=0; i<optg.length; i++) {
    var l = optg[i].getAttribute('label');
    var c = optg[i].getElementsByTagName('option');
    var o = document.createElement('option');
    var id = new String(i + 1);
    if (id.length < 2) {
      id = '0' + id + '';
    }
    o.setAttribute('value', id);
    if (id == selectedCat) {
      o.selected = true;
    }
    o.appendChild(document.createTextNode(l));
    sl1.appendChild(o);
    // sl2.appendChild removes items from c, keep reading from c[0]!
    while(c.length > 0) {
      var j = 0;
      sl2.appendChild(c[j]);
    }
  }
  var sls = sl2.cloneNode(true);
  sl1.onchange = function() { updateLists(sls, sl1, sl2); };
  sl.parentNode.insertBefore(sl1, sl);
  sl.parentNode.replaceChild(sl2, sl);
  updateLists(sls, sl1, sl2);
}

function updateLists(sls, sl1, sl2) {
  var i;
  var s = 0;
  selectedCat = sl1[sl1.selectedIndex].value;
  for (i=sl2.length; i>0; i--) {
    sl2.removeChild(sl2[i-1]);
  }
  for (i=0; i<sls.length; i++) {
    if ((!selectedCat && ((sls[i].value.indexOf('.') > -1) || (i == 0))) ||
        (selectedCat && (sls[i].value.indexOf(selectedCat) == 0))) {
      sl2.appendChild(sls[i].cloneNode(true));
      var n = sl2.childNodes.length - 1;
      if ((selectedCat && (n == 0)) ||
          (sl2[n].value == selectedType) ||
          ((s == 0) && (sl2[n].value.indexOf('.') < 0))) {
        s = n;
      }
    }
  }
  sl2.selectedIndex = s;
}

function trimValue(str) {
  if (str.replace) {
    str = str.replace(/s+/g, '');
    str = str.replace(/,/g, '.');
  }
  return str;
}

function round(num, n) {
  if (isNaN(num)) {
    return num;
  }
  else {
    num = Math.round(num*Math.pow(10,n))/Math.pow(10,n);
    return num;
  }
}

function rcalculate(e) {
  var targ = eventTarget(e);
  var num = round(eval(trimValue(targ.value)), 2);
  if (! isNaN(num)) {
    targ.value = num;
  }
}

function convert(orig, rate, cost) {
  if (rate.value && orig.value) {
    cost.value = parseFloat(rate.value) * parseFloat(orig.value);
  }
}

function toggleDisplay(id) {
  if (document.getElementById) {
    var o = document.getElementById(id);
    if (o.style) {
      if (o.style.display == 'block') {
        o.style.display = 'none';
      }
      else {
        o.style.display = 'block';
      }
    }
  }
}

function tdHighlight(t, td) {
  var ids = new String(td.getAttribute('headers')).split(' ');
  var i;
  var thisYear, thisMonth;
  for (i=0; i<ids.length; i++) {
    var th = document.getElementById(ids[i]);
    th.className += ' hilite';
    if (chart) {
      if (ids[i].slice(0, 1) == 'y') {
        thisYear = ids[i].slice(1);
      }
      else if ((ids[i].slice(0, 1) == 'm') || (ids[i] == 'total')) {
        thisMonth = ids[i].slice(1);
      }
    }
  }
  var tds = t.getElementsByTagName('td');
  for (i=0; i<tds.length; i++) {
    var headers = new String(tds[i].getAttribute('headers')).split(' ');
    // column header always comes second, rows are hilited with pure CSS
    if (inArray(headers, ids[1]) >= 0) {
      tds[i].className += ' hilite';
    }
  }
  if (chart && thisYear && thisMonth) {
    var startMonth, endMonth;
    var endYear = new Number(thisYear);
    if (thisMonth == 'otal') {
      startMonth = 0;
      endMonth = 0;
      endYear += 1;
    }
    else {
      startMonth = thisMonth - 1;
      endMonth = thisMonth;
    }
    while (endMonth > 11) {
      endYear += 1;
      endMonth -= 12;
    }
    var startDate = new Date(thisYear, startMonth, 1, 0, 0, 0);
    var endDate = new Date(endYear, endMonth, 1, 0, 0, 0);
    chart.setVisibleChartRange(startDate, endDate);
  }
}

function tdDownlight(t, td) {
  var ids = new String(td.getAttribute('headers')).split(' ');
  var i;
  for (i=0; i<ids.length; i++) {
    var th = document.getElementById(ids[i]);
    th.className = th.className.replace(/ hilite/, '');
  }
  var tds = t.getElementsByTagName('td');
  for (i=0; i<tds.length; i++) {
    var headers = new String(tds[i].getAttribute('headers')).split(' ');
    // column header always comes second, rows are hilited with pure CSS
    if (inArray(headers, ids[1]) >= 0) {
      tds[i].className = tds[i].className.replace(/ hilite/, '');
    }
  }
}

function thHighlight(t, th) {
  var id = th.id;
  var tds = t.getElementsByTagName('td');
  var i;
  for (i=0; i<tds.length; i++) {
    var headers = new String(tds[i].getAttribute('headers')).split(' ');
    if (inArray(headers, id) >= 0) {
      tds[i].className += ' hilite';
    }
  }
}

function thDownlight(t, th) {
  var id = th.id;
  var tds = t.getElementsByTagName('td');
  var i;
  for (i=0; i<tds.length; i++) {
    var headers = new String(tds[i].getAttribute('headers')).split(' ');
    if (inArray(headers, id) >= 0) {
      tds[i].className = tds[i].className.replace(/ hilite/, '');
    }
  }
}

function tableHighlight(id) {
  if (!document.getElementById) {
    return false;
  }
  var t = document.getElementById(id);
  if (!t || !t.getElementsByTagName) {
    return false;
  }
  var tds = t.getElementsByTagName('td');
  var i;
  for (i=0; i<tds.length; i++) {
    if (!tds[i] || !tds[i].getAttribute) {
      break;
    }
    tds[i].onmouseover = function() { tdHighlight(t, this); };
    tds[i].onmouseout = function() { tdDownlight(t, this); };
  }
  var ths = t.getElementsByTagName('th');
  for (i=0; i<ths.length; i++) {
    if (!ths[i] || !ths[i].getAttribute) {
      break;
    }
    ths[i].onmouseover = function() { thHighlight(t, this); };
    ths[i].onmouseout = function() { thDownlight(t, this); };
  }
}

function init() {
  if (!document ||
      !document.getElementById || 
      !document.getElementsByTagName || 
      !document.createElement ||
      !document.appendChild) {
    return false;
  }
  var insertLink = document.getElementById('insert');
  if (insertLink) {
    insertLink.onclick = insertWindowOpen;
  }
  var tab;
  var p;
  if (document.cookie) {
    var cook = new String(document.cookie);
    var tabPos = cook.indexOf('tab=');
    if (tabPos >= 0) {
      tab = cook.substr(tabPos+4);
      if ((p = tab.indexOf(';')) && (p > 0)) {
        tab = tab.substr(0, p);
      }
    }
  }
  if (!tab) {
    tab = 'summary';
  }

  h1 = document.getElementById('history');
  h2 = document.getElementById('benchmarkhistory');
  show(tab);
  var tabul = document.getElementById('tabs');
  if (tabul) {
   var tabs = tabul.getElementsByTagName('a');
   var i;
   for (i=0; i<tabs.length; i++) {
    // tabs[i].onmouseover = showMe;
    tabs[i].onclick = showMe;
   }
  }
  if (document.getElementById('insertfs')) {
    var b;
    var c;
    var inputs = new Array();
    var labels = new Array();
    var fs;
    fs = document.getElementById('costfs');
    c = fs.childNodes;
    for (i=0; i<c.length; i++) {
      if (new String(c[i].nodeName).toUpperCase() == 'INPUT') {
        inputs[c[i].name] = c[i];
        break;
      }
    }
    var cost = inputs['cost'];
    cost.onchange = rcalculate;
    fs = document.getElementById('otherfs');
    c = fs.childNodes;
    for (i=0; i<c.length; i++) {
      if (new String(c[i].nodeName).toUpperCase() == 'INPUT') {
        inputs[c[i].name] = c[i];
      }
      if (new String(c[i].nodeName).toUpperCase() == 'LABEL') {
        labels[labels.length] = c[i];
      }
    }
    var orig = inputs['other'];
    var curr = inputs['currency'];
    var rate = inputs['rate'];
    orig.onchange = rcalculate;
    rate.onchange = function() { rate.value = rate.value ? eval(trimValue(rate.value)) : ''; };
    rate.onblur = function() { convert(orig, rate, cost); };
//      if (fs.parentNode && fs.appendChild) {
//        b = document.createElement('input');
//        b.type = 'button';
//        b.value = '=>';
//        b.onclick = function() { convert(orig, rate, cost); return false; };
//        b.setAttribute('tabindex', 1);
//        fs.appendChild(b);
//      }
    var l = labels[0];
    if (l && l.childNodes && l.childNodes[0]) {
      var val = l.childNodes[0].nodeValue;
      b = document.createElement('input');
      b.type = 'button';
      b.value = val;
      b.onclick = function() { toggleDisplay('otherfs'); return false; };
      // b.setAttribute('tabindex', 100);
      if (fs.parentNode && fs.parentNode.insertBefore) {
        fs.parentNode.insertBefore(b, fs);
        l.style.display = 'none';
        if (!rate.value && !orig.value && !curr.value) {
          fs.style.display = 'none';
        }
      }
    }
    return false;
  }
  // XXX not yet finished!
  tableHighlight('history');
  tableHighlight('benchmarkhistory');
  var loc = new String(window.location);
  if (loc.match) {
    var m = loc.match(/cat=(\d+)/);
    if (typeof(m) == typeof(new Array())) { 
      selectedCat = (m ? m[1] : null);
    }
    m = loc.match(/type=([.\d]+)/);
    if (typeof(m) == typeof(new Array())) { 
      selectedType = (m ? m[1] : null);
    }
  }
  replaceSelectList(SELECTLISTID);
  var links = document.getElementsByTagName('a');
  for (i=0; i<links.length; i++) {
    if (links[i].className && links[i].className.indexOf && 
        links[i].className.indexOf('modify') >= 0) {
      links[i].onclick = insertWindowOpen;
    }
  }
  var labels = document.getElementsByTagName('label');
  for (i=0; i<labels.length; i++) {
    if (labels[i].className && labels[i].className.indexOf('submit') >= 0) {
      labels[i].ondblclick = submitMe;
      if (labels[i].getAttribute && labels[i].form.elements['order']) {
        if (labels[i].getAttribute('for') == 'view__details') {
          labels[i].onclick = function() { this.form.order.value = 'date desc'; };
        }
        else if (labels[i].getAttribute('for') == 'view__summary') {
          labels[i].onclick = function() { this.form.order.value = 'cost desc'; };
        }
      }
    }
  }
}

function hide(id) {
  var obj = document.getElementById(id);
  if (obj && obj.style) {
    obj.style.display = 'none';
  }
  var link = document.getElementById(id + 'link');
  if (link) {
    link.className = '';
  }
}

function show(id) {
  var ds = document.getElementById('dynamicstyle');
  if (!ds) {
    return false;
  }
  var styles = '';
  var link;
  var divs = ['summary', 'details', 'benchmarkimages', 'plot'];
  for (var i=0; i<divs.length; i++) {
    var dis = (divs[i] == id) ? 'block' : 'none';
    styles += '#' + divs[i] + '{display:' + dis + '}';
    link = document.getElementById(divs[i] + 'link');
    if (link) {
      link.className = '';
    }
  }
  link = document.getElementById(id + 'link');
  if (link) {
    link.className = 'active';
  }
  if (id != 'benchmarkimages') {
    styles += '#benchmarkhistory{display:none}#history{display:block}';
  }
  else {
    styles += '#benchmarkhistory{display:block}#history{display:none}';
  }
  if (ds.styleSheet) {
    ds.styleSheet.cssText = styles;
  }
  else {
    ds.innerHTML = styles;
  }
  if ((id == 'plot') && (initPlot)) {
    initPlot();
  }
  document.cookie = 'tab=' + escape(id) + ';path=/'; 
  return false;
}
function showMe(e) {
  var a = eventTarget(e);
  var id = a.href.substr(a.href.indexOf('#')+1);
  show(id);
  return false;
}
function initJumpList() {
  if (window.external && window.external.msIsSiteMode()) {
    var i;
    var ico = '/favicon.ico';
    window.external.msSiteModeCreateJumplist(document.title);
    window.external.msSiteModeAddJumpListItem(document.title, document.URL, ico);
    var il = document.getElementById('insertlink');
    if (il) {
      window.external.msSiteModeAddJumpListItem(il.text, il.href, ico);
    }
    var links = document.getElementsByTagName('link');
    var want = new Array('home', 'prev', 'next', 'first', 'last');
    if (links) {
      for (i=0; i<links.length; i++) {
        if (want.indexOf(links[i].rel) >= 0) {
          window.external.msSiteModeAddJumpListItem(links[i].title, links[i].href, ico);
        }
      }
    }
    var tabul = document.getElementById('tabs');
    if (tabul) {
      var tabs = tabul.getElementsByTagName('a');
      for (i=0; i<tabs.length; i++) {
        window.external.msSiteModeAddJumpListItem(tabs[i].text, tabs[i].href, ico);
      }
    }
    window.external.msSiteModeShowJumplist();
  }
}

// window.onload = init;

init();
