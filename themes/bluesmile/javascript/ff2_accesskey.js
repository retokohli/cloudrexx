/*
  Disclaimer - Kostenloses JavaScript nur mit diesem Disclaimer

  (c) 2bweb.de 2006-2008
  Jan Eric Hellbusch <hellbusch@2bweb.de>
  Stephan Heller <heller@2bweb.de>

  FIREFOX 2 ERWEITERUNG

  FF2 hat die Tastenkombination für Accesskey von

          ALT - acckey  auf  ALT + SHIFT - acckey

  verändert.

  Das Umschalten führt allerdings dazu,
  dass nicht mehr die Zahlen, sondern die Sonderzeichen
  verarbeitet werden, was das verwendete Accesskey-Pad hinfällig macht.

  Diese JS sorgt dafür, dass das Pad trotzdem wie gewünscht arbeitet */

function ffTastenKombi (evt) {
  if (evt != null && evt.type == 'keydown') {
    if (evt.altKey && evt.keyCode >= 48 && evt.keyCode <= 58) {
      links = document.getElementsByTagName('a');
      for(i = 0; i < links.length; i++) {
        var accesskey = links[i].getAttribute('accesskey');
        var href = links[i].getAttribute('href');
        if (accesskey && evt.keyCode == parseInt(accesskey)+48 && href) {
          document.location.href = href;
          break;
        }
      }
    }
  }
}

if(navigator.userAgent.indexOf('Firefox/2')!=-1) {
   window.document.onkeydown = ffTastenKombi;
}