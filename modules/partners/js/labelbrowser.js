var LabelBrowser = (function(){

    var settings = $H({
        'select_size': 10
    });

    var treedata = {};
    var selects  = {};

    var __elem_id_gen = 1; 
    var elem_id = function() {
        return __elem_id_gen++;
    };

    var config = function(data) {
        settings.update(data);
    };

    var find_parent_of = function(id, elem_id) {
        var elem = find_in_tree(id, elem_id);
        return find_in_tree(id, elem.parentid);
    };

    var find_in_tree = function(id, elem_id) {
        var td = treedata[id];
        
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

    var changed = function(id, current) {
        var children = find_in_tree(id, current).children;

        var target = $(id);
        target.value = current;

        clear(id);
        draw(id, current);
        if (children.length) {
            add(id, children, 0);
        }
    };

    var add = function(id, tree, sel_id) {
        var c = $(id + "_container");

        var s_size = settings.get('select_size');

        var dd_id = elem_id();
        c.insert({'bottom': '<select size="'+s_size+'" id="sel_'+dd_id+'"></select>'});
        var s = $('sel_'+dd_id);

        var index = -1;
        var count = 0;
        tree.each(function(elem) {
            s.options[count++] = new Option(elem.name, elem.id, false, false);
            if (elem.id == sel_id) {
                index = count-1;
            }
        });
        s.selectedIndex = index;

        s.observe('change', function(){
            var clicked = s.options[s.selectedIndex].value;
            changed(id, clicked);
        });
    };

    var clear = function(id) {
        var c = $(id + "_container");
        c.update('');
    };

    var draw = function(id, current) {
        var treestructure = [];
        var curr;
        if (current > 0) {
            curr = find_in_tree(id, current);
            while (true) {
                if (curr.parentid) {
                    var par = find_in_tree(id, curr.parentid);
                    treestructure.push({'list': par.children, 'selected': curr.id});
                    curr = par;
                }
                else {
                    break;
                }
            }
        }
        treestructure.push({'list': treedata[id], 'selected': curr ? curr.id : 0});

        treestructure.reverse();

        treestructure.each(function(elem){
                add(id, elem.list, elem.selected);
        });
    };

    var register = function(id, current, tree) {
        var target = $(id);
        target.value = current;

        target.insert({'after': '<div style="clear: both;" id="'+id+'_container"> </div>'});


        treedata[id]  = tree.slice(1,tree.length);
        draw(id, current);
    };

    return {
        'register': register,
        'config':   config
    }

})();

