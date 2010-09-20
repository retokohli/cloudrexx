var LabelFrontend = (function() {
  var partner_id = null;

  var _add = function(target_id, value) {
    new Ajax.Updater(target_id, 'index.php?section=partners&ajax=assignentry', {
      'parameters': {
        'partner_id': partner_id,
        'entry_id':   value
      }
    });
  };

  var _register = function(input_id, clicky_id, target_id) {
    $(clicky_id).observe(
      'click',
      function(){
        var inp = $(input_id);
        var val = inp.value;
        if(val)
          _add(target_id, val);
        return false;
      }
    );
  };

  return {
    register: _register,
    set_partner: function(id){partner_id = id;}
  };
})();
