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

    /**
     * Returns the element in the treedata with the given id-number, specified by elem_id.
     * The first parameter specifies the browser object, as with all other functions here.
     */
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

    /**
     * Returns a list of dropdowns that need to be drawn.
     * The first parameter, id, is the id of the browser itself (and DOM id of the wrapping div).
     * The second parameter, current, is the id uf the newly selected entry.
     *
     * The resulting data structure is a list, of objects of the following form:
     *     {list: <what to display>, selected: <id of the selected entry>}
     */
    var draw_data = function(id,current) {
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
        return treestructure;
    };

    /**
     * Draws all select boxes from the root node, up to the given "current" entry.
     */
    var draw = function(id, current) {
        var treestructure = draw_data(id, current);

        treestructure.each(function(elem){
                add(id, elem.list, elem.selected);
        });
    };

    /**
     * after a change-event, this function removes all the select boxes right of
     * the just-clicked one while keeping the clicked one and the ones left of it.
     * Then, it draws a new select box right of it if the currently selected entry
     * has children.
     * This is different from draw(), which just paints all the select boxes up from
     * the root node.
     */
    var redraw = function(id, prev, current) {

        var treestructure = draw_data(id, current);

        var keepers = [];
        var finders = [];

        var t = keepers;
        treestructure.each(function(e){
            t.push(e);
            if(e.selected == current)
                t = finders;
        });

        var dropdowns = $$('#'+id + "_container select");
        dropdowns.each(function(dd){
            if (!keepers.find(function(e) {return e.selected == dd.value})) {
                var p;
                try{
                    p = dd.parentNode;
                    p.removeChild(dd);
                }
                catch (e) {
                    console.dir(e);
                }
            }
        });

        finders.each(function(e){
            add(id, e.list, e.selected);
        });

    };

    var changed = function(id, current) {
        var children = find_in_tree(id, current).children;

        var target = $(id);

        var prev = target.value;

        target.value = current;


        redraw(id,prev, current);

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

