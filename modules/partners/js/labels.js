var LabelTools = (function() {
    var assign_url;
    var drop_url;

    var setup = function(data) {
        assign_url = data['assign_url'];
        drop_url   = data['drop_url'];
    };

    var send_entry_url = function(target_url, partner_id, entry_id, label_id) {
        new Ajax.Updater(
            label_id, target_url,
            {
                parameters: {
                    'partner_id': partner_id,
                    'entry_id':   entry_id
                },
                method: 'POST'
            }
        );
        return false;
    };

    return {
        'setup': setup,
        'drop_entry':   function(pid, eid, lid) {send_entry_url(drop_url,   pid, eid, lid);return false;},
        'assign_entry': function(pid, eid, lid) {send_entry_url(assign_url, pid, eid, lid);return false;}
    }
})();

