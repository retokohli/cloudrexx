//  Disclaimer - Free Script only with this Disclaimer
//  getting accesskeys (0-9) without return
//  coded by Joern Hofer: jhATgrassi.de (2004-04-04)
//  as it seems, this doesn't work on IE5 on mac
//  Disclaimer ends here

var ie = document.all;
var altTaste = false;
var linksFertig = false;
var akLinks = new Array();

function tasteGedrueckt() {
//  erst was machen, wenn alle Links geparst wurden
    if(linksFertig) {
//  welches war die aktuelle Taste denn?
        aktuelleTaste = parseInt(window.event.keyCode);
//  18 = ALT-Taste
//  muss man sich zwischen speichern, sonst ist der Event weg
        if(aktuelleTaste == 18) {
            altTaste = true;
        } else {
//  war eine andere Taste als ALT
//  mal gucken, ob diese zu den Accesskeys gehoert
            for(i = 0; i < akLinks.length; i++) {
//  und direkt vorher muss ja die ALT-Taste gedrueckt gewesen sein
                if(akLinks[i][0] == aktuelleTaste && altTaste) {
//  wenn beides zutrifft nehmen wir den Link und fuehren ihn aus
                    document.location.href = akLinks[i][1];
//  gibt ja nur einen Accesskey, also koennen wir mit der Schleife aufhoeren, voher aber ALT zurück setzen (man weiss ja nie...)
                    altTaste = false;
                    break;
                }
            }
//  wenn es nicht ALT war aber auch keine Taste mit unseres Keys, dann die ALT-Taste wieder zurueck setzen
            altTaste = false;
        }
    }
}

//  Diese Variante funktioniert so nur fuer Accesskeys mit Zahlen
function alleAccesskeyLinks() {
//  erstmal alle Links holen
    links = document.getElementsByTagName("a");
//  nen Zaehler initialisieren
    zaehler = 0;
//  wenn es Links gibt, und der Browser das neue DOM kann
    if(links) {
//  alle Links durchgehen
        for(i = 0; i < links.length; i++) {
//  Accesskey-Attribut holen
            accesskey = links[i].getAttribute("accesskey");
//  und wenn wir schon dabei sind, auch gleich den Link dazu
            accessLink = links[i].getAttribute("href");
//  wenn der Accesskey vorhanden ist
            if(accesskey) {
//  machen wir in unserem akLinks (accesskeyLinks) ein 2 diemensionales Array
                akLinks[zaehler] = new Array(2);
//  in den ersten den Key reinpacken
                akLinks[zaehler][0] = accesskey*1 + 48;
//  in den zweiten den Link reinpacken
                akLinks[zaehler][1] = accessLink;
//  zaehler ein weiter, damit im akLinks-Array ein weiter gezaehlt wird
                zaehler++;
            }
        }
//  und sagen, dass wir fertig sind, mit den Links der Seite (besser, falls mal jemand zu frueh eine Taste drueckt, wir aber noch nicht zu ende geparst haben
        linksFertig = true;
    }
}

//  alle Links holen, wenn Seite fertig ist mit laden
var counter = 0;
    tempAccesskey = window.onload;
    window.onload = function() {
                        if(counter == 0) {
                            if(typeof tempAccesskey == "function") tempAccesskey();
                            alleAccesskeyLinks();
                            counter++;
                        }
                    }
//  wenn auf dem Dokument eine Taste gedrueckt wird (das .document ist wichtig, ohne das geht es nicht)
window.document.onkeydown = tasteGedrueckt;

