var LabelDropdown = (function(){

  var treedata    = {};
  var currents    = {};
  var notselected = {};

  var fill = function(dd, list, first_id) {
    var count = 0;
    dd.options.length = 0;
    if (!first_id) first_id = '0';

    var ns = notselected[dd.id];
    dd.options[count++] = new Option(ns.name, first_id, false, false);
    list.each(function(elem) {
      dd.options[count++] = new Option(elem.name, elem.id, false, false);
    });
  };

  var init = function(dd, current) {
    // clear crumble. we're gonna update it anyhow
    $(dd.id + "_crumble").update('');

    if (current == '' || current == '0') {
      // take all the top levels. No crumble.
      fill(dd, treedata[dd.id], 0);
      select(dd,0);
      return;
    }

    // okay, there's something selected. First, make
    // the dropdown contain what's needed, select the actual
    // element, then fix up the crumble path.
    var active = find_in_tree(dd, current);
    var par = find_parent_of(dd, active.id);
    var crumbletarget = par;
    if (par) {
      // parent entry found. we're on some sub-level.
      // Fill with our siblings.
      if(active.children.length) {
        // we've got children. this means we go into the
        // crumble, and the children get listed. Select
        // the parent
        fill(dd, active.children, par.id);
        select(dd, par.id);
        crumbletarget = active;
      } else {
        // no children. we go into the list and should
        // get selected
        fill(dd, par.children, par.id);
        select(dd, active.id);
      }
    } else {
      // no parent entry found. we're selected on top level.
      fill(dd, treedata[dd.id], 0);

      if(active.children.length) {
        // we've got children. this means we go into the
        // crumble, and the children get listed. Select
        // the parent
        fill(dd, active.children, 0);
        crumbletarget = active;
      } else {
        // no children. we go into the list and should
        // get selected
        fill(dd, treedata[dd.id], 0);
        select(dd, active.id);
      }
    }

    // Now, fix the crumble tree.
    while (crumbletarget) {
      add_crumble(dd, crumbletarget);
      crumbletarget = find_parent_of(dd, crumbletarget.id);
    }
  };

  var find_parent_of = function(dd, elem_id) {
    var elem = find_in_tree(dd, elem_id);
    return find_in_tree(dd, elem.parentid);
  };

  var find_in_tree = function(dd, elem_id) {
    var td = treedata[dd.id];
    var found = null;
    var _set = function(elem) {
      found = elem;
    }

    // TODO: do this without ugly exception abuse
    var _find = function(elem) {
      if (elem.id == elem_id) {
        _set(elem);
        throw "e-found";
      }
      elem.children.each(_find);
    }
    // prototyp does the catching for us, but
    // it appears they could be changing this
    // in the future.
    try {
      td.each(_find);
    }
      catch (err) {
    }
    return found;
  };

  var crumble_id_gen = 0;
  var add_crumble = function(dd, elem, append) {
    var old_options = dd.options;

    var crumble_id = "dropdown_crumble_" + (crumble_id_gen++);
    var crumble_id_a = crumble_id + "_a";
    var crumble_html = '<span id="'+crumble_id+'"><a href="#" id="'+crumble_id+'_a">'+elem.name+'</a>: </span>';
    var crumble = $(dd.id + "_crumble");

    if (append) crumble.insert({'bottom': crumble_html});
    else        crumble.insert({'top':    crumble_html});

    var link_container = $(crumble_id);
    var link = $(crumble_id_a);

    link.observe('click', function() {
      // drop crumble and all right-hand siblings
      var right_siblings = link_container.nextSiblings();
      right_siblings.each(function(e){ e.remove(); });
      link_container.remove();
      // restore options
      var par = find_parent_of(dd, elem.id);
      if (!par) fill(dd, treedata[dd.id]);
      else      fill(dd, par.children, par.id);
      select(dd, elem.id);
    });
  };
  var select = function(dd, elem_id) {
    for (var i = 0; i < dd.options.length; i++) {
      if (dd.options[i].value == elem_id) {
        dd.selectedIndex =i;
        return;
      }
    }
  };

  var currently_selected_elem = function(dd) {
    var elem_id = dd.options[dd.selectedIndex].value;
    return find_in_tree(dd, elem_id);
  };

  var push_current = function(dd) {
    var curr = currently_selected_elem(dd);
    if (curr.children.size() > 0) {
      fill(dd, curr.children, curr.id);
      add_crumble(dd, curr, true);
    }
  }

  var changed = function(dd) {
    var elem_id = dd.options[dd.selectedIndex].value;
    //init(dd, elem_id);
    push_current(dd);
  };


  var register = function(id, current, tree) {
    treedata[id]    = tree.slice(1,tree.length);
    currents[id]    = current;
    notselected[id] = tree[0];
    var dd = $(id);
    init(dd, current);
    dd.observe('change', function() {
      changed(dd);
    });
  };


  return {
    'register': register
  }

})();
