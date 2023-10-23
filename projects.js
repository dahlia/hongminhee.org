const projects = document.getElementsByClassName('etc')[0],
      etc = projects.getElementsByTagName('ul')[0],
      toggler = document.createElement('span');

function showEtc() {
  const label = projects.getAttribute('data-hide');
  toggler.firstChild.nodeValue = label;
  etc.className = etc.className.replace(/\bhidden\b/g, ' ');
  toggler.onclick = hideEtc;
  return false;
}

function hideEtc() {
  while (toggler.childNodes.length) {
    toggler.removeChild(toggler.firstChild);
  }
  const label = projects.getAttribute('data-show');
  toggler.appendChild(document.createTextNode(label));
  etc.className += ' hidden';
  toggler.onclick = showEtc;
  return false;
}

toggler.className = 'toggler';
hideEtc();
projects.insertBefore(toggler, etc);
