12.03.2008 Livecam Anpassungen
---------------------------------------
In der Contentseite fürs Modul Livecam muss der Link für das Anzeigebild und die Archivbilder folgendermassen angepasst werden:

Anzeigebild:
<a href="[[LIVECAM_IMAGE_LINK]]" title="[[LIVECAM_IMAGE_TEXT]]" [[LIVECAM_IMAGE_LIGHTBOX]]><img width="[[LIVECAM_IMAGE_SIZE]]" border="0" alt="[[LIVECAM_IMAGE_TEXT]]" src="[[LIVECAM_CURRENT_IMAGE]]" /></a>

Archivbilder:
<a href="[[LIVECAM_PICTURE_URL]]" title="[[LIVECAM_PICTURE_TIME]]" [[LIVECAM_IMAGE_LIGHTBOX]]><img src="[[LIVECAM_THUMBNAIL_URL]]" width="[[LIVECAM_THUMBNAIL_SIZE]]" border="0" alt="[[LIVECAM_PICTURE_TIME]]" /></a>

Zudem muss im Form-Tag die URL angepasst und &amp;cmd=[[CMD]] angehängt werden:
<form action="index.php?section=livecam&amp;cmd=[[CMD]]" method="post" name="form">

Das gleiche gilt für den Aktualisieren-Link:
<a href="index.php?section=livecam&amp;cmd=[[CMD]]" onclick="javascript:document.location.reload();">Aktualisieren</a>


12.03.2008 Market Anpassungen
---------------------------------------
Die Contentseite (?section=market&cmd=confirm) wird neu in zwei Blöcke unterteilt:

Block 1: <!-- BEGIN codeForm --><!-- END codeForm --> Dieser Block beinhaltet die bisherige Eingabeform für den Freischaltcode.

Block 1: <!-- BEGIN infoText --><!-- END infoText --> Dieser Block beinhaltet lediglich Text zur Information, dass der Block eingetragen wurde und nach Prüfung aufgeschaltet werde.