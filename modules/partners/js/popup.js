var Popup = {
  show_addlabel: function(label_id, partner_id) {
    Popup.get_popup();
    Popup.put_into('popuptarget_' + label_id);
    Popup.load('index.php?cmd=partners&act=assignentry_show',
      { 'partner_id': partner_id, 'label_id': label_id },
        function() {}
      );
    return false;
  },
  show_edit: function(entry_id) {
  Popup.put_into('popuptarget_' + entry_id);
    Popup.load('index.php?cmd=partners&act=editentry_popup',
      { entry_id: entry_id },
      function() {
        $('entry_save').observe('click', function(event) {
          var f = $('modalpopup_form');
          f.action = 'index.php?cmd=partners&act=editentry_save';
          f.request({
            onSuccess: function(resp){
              $('label_entries').update(resp.responseText);
            }
          });
          Popup.hide();
        });
        $('entry_cancel').observe('click', function(event) {
          Popup.hide();
        });
      }
    );
    return false;
  },
  show_newentry: function(label_id, parent_id) {
  Popup.put_into('popuptarget_' + parent_id);
    Popup.load('index.php?cmd=partners&act=newentry_popup',
      {
         id:        label_id,
         parent_id: parent_id
      },
      function() {
        $('entry_save').observe('click', function(event) {
          var f = $('modalpopup_form');
          f.action = 'index.php?cmd=partners&act=newentry_save';
          f.request({
            onSuccess: function(resp){
              $('label_entries').update(resp.responseText);
            }
          });

          Popup.hide();
        });
        $('entry_cancel').observe('click', function(event) {
          Popup.hide();
        });
      }
    );
    return false;
  },
  load: function(url, params, onsuccess) {
    Popup.get_popup();
    new Ajax.Updater('modalpopup_text',
      url,
      {
        parameters: params,
        evalScripts: true,
        onComplete:  function(msg) {
          Popup.show();
          return onsuccess(msg);
        }
      }
    );
    return false;
  },
  show: function() {
    Popup.get_popup().show();
  },
  hide: function() {
    Popup.get_popup().hide();
  },

  put_into: function(target) {
    var elem = Popup.get_popup().remove();
    $(target).insert({'bottom': elem});
  },

  get_popup: function() {
    if (!$('modalpopup')) {
      $(document.body).insert({'bottom': ' \
        <style language="text/css"> \
          div#modalpopup { \
            margin-top: 20px; \
            position: relative; \
          } \
          div#modalpopup div#modalpopup_text { \
            padding: 5px; \
          } \
        </style> \
        <div id="modalpopup" style="display: none;"> \
          <div> \
            <form name="modalpopup" id="modalpopup_form" method="POST"> \
              <div id="modalpopup_text">&nbsp;</div> \
            </form> \
          </div> \
        </div> \
      '});
    }
    return $('modalpopup');
  }
};

Popup.get_popup();
