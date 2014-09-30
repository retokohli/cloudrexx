/*
 Copyright (c) 2003-2014, CKSource - Frederico Knabben. All rights reserved.
 For licensing, see LICENSE.md or http://ckeditor.com/license
 */
CKEDITOR.addTemplates("default", {
    imagesPath: CKEDITOR.getUrl(CKEDITOR.plugins.getPath("templates") + "templates/images/"),
    templates: [{
        //title: "Image and Title",
        title: "Bild und Titel",
        image: "template1.gif",
        //description: "One main image with a title and text that surround the image.",
        description: "Bild mit einem Titel und Text, der das Bild umfliesst.",
        //html: '<h3><img src=" " alt="" style="margin-right: 10px" height="100" width="100" align="left" />Type the title here</h3><p>Type the text here</p>'
        html: '<h3><img src=" " alt="" style="margin-right: 10px" height="100" width="100" align="left" />Hier den Titel einfügen</h3><p>Hier den Text einfügen</p>'
    }, {
        //title: "Two columns layout",
        title: "Zwei Spalten mit Titel",
        image: "template2.gif",
        //description: "A template that defines two columns, each one with a title, and some text.",
        description: "Zwei Spalten, die beide einen Titel und Text beinhalten.",
        //html: '<div class="row"><div class="col-md-6"><h2>Title</h2>Text goes here <br/> </div><div class="col-md-6"><h2 >Title</h2>Text goes here <br/></div></div>Text goes here'
        html: '<div class="row"><div class="col-md-6"><h2>Hier den Titel einfügen</h2>Hier den Text einfügen <br/> </div><div class="col-md-6"><h2 >Title</h2>Hier den Text einfügen <br/></div></div>Text goes here'
    }, {
        //title: "Text and Table",
        title: "Text und Tabelle",
        image: "template3.gif",
        //description: "A title with some text and a table.",
        description: "Ein Titel mit Text und einer Tabelle.",
        //html: '<div style="width: 80%"><h3>Title goes here</h3><table style="width:150px;float: right" cellspacing="0" cellpadding="0" border="1"><caption style="border:solid 1px black"><strong>Table title</strong></caption><tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr></table><p>Type the text here</p></div>'
        html: '<div style="width: 80%"><h3>Hier den Titel einfügen</h3><table style="width:150px;float: right" cellspacing="0" cellpadding="0" border="1"><caption style="border:solid 1px black"><strong> Hier den Tabellentitel einfügen</strong></caption><tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr></table><p>Type the text here</p></div>'
    }]
});

