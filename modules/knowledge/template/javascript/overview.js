var lastDragged = 0;

var Articles = function() {
   // constructor 
}

/**
 * Get the categorie's articles
 */
Articles.prototype.getCategory = function(id, pos) {
    
    this.setLoading();
    new Ajax.Request('index.php', {
        method : "get",
        parameters : {
            cmd     : "knowledge",
            section : "articles",
            act     : "getArticles",
            id      : id,
            pos     : pos
        },
        onSuccess : function(transport) {
            response = transport.responseText.evalJSON();
            $('articles').update(response.list);
            pos = response.position;
            setRows();
            initSortableArea(pos);
            notIe();
        }
    })
}

/**
 * The success function for the getter methods
 *
 * It puts the content on the page and calls all
 * necessary functions to update the row colors
 * and width for non-ie browsers.
 */
Articles.prototype.getterSucess = function(transport) {
    response = transport.responseText.evalJSON();
    $('articles').update(response.list);
    setRows();
    notIe();
}

/**
 * Get the best rated articles
 */
Articles.prototype.getBestRated = function(pos) {
    this.setLoading();

    new Ajax.Request('index.php', {
        method : 'get',
        parameters : {
            cmd     : 'knowledge',
            section : 'articles',
            act     : 'getArticlesByRating',
            pos     : pos
        },
        onSuccess : this.getterSucess
    });
}

/*
 * Get the most read articles
 */
Articles.prototype.getMostRead = function(pos) {
    this.setLoading();

    new Ajax.Request('index.php', {
        method : 'get',
        parameters : {
            cmd     : 'knowledge',
            section : 'articles',
            act     : 'getArticlesByViews',
            pos     : pos
        },
        onSuccess : this.getterSucess
    });
}

/*
 * Get the glossary
 */
Articles.prototype.getGlossary = function(pos) {
    this.setLoading();

    new Ajax.Request('index.php', {
        method : 'get',
        parameters : {
            cmd     : 'knowledge',
            section : 'articles',
            act     : 'getArticlesGlossary',
            pos     : pos
        },
        onSuccess : this.getterSucess
    });
}

/*
 * Search the articles
 */
Articles.prototype.searchArticles = function(term, pos) {
    this.setLoading();

    new Ajax.Request('index.php', {
        method : 'get',
        parameters : {
            cmd     : 'knowledge',
            section : 'articles',
            act     : 'searchArticles',
            term    : term,
            pos     : pos
        },
        onSuccess : this.getterSucess
    });
}

/**
 * Set loading
 *
 * Show the loading image
 */
Articles.prototype.setLoading = function()
{
    if ($('inner_right')) {
        $('articles').removeChild($('inner_right'));
    }
    
    $('articles').update('<center><img src="../modules/knowledge/template/loading.gif" alt="" id="loading"  /></center>');
}

/**
 * Remove a row from the list (for when it's deleted)
 */
Articles.prototype.deleteRow = function(id)
{
    new Effect.Highlight('article_'+id, {
        startcolor  : '#ff9898', 
        endcolor    : '#ffffff',
        duration    : 1
    });
    
    var obj = $('article_'+id);
    window.setTimeout(function() {
        obj.parentNode.removeChild(obj);
    }, 1000);
    
    delete obj;
}

/**
 * The instance of the object (what for actually??)
 */
var articles = new Articles();

/**
 * Set the row classes
 *
 * Each alternating row has either a row1 or row2 class.
 * Update these classes
 */
var setRows = function()
{
    rows =$A($('articlelist').getElementsByTagName("li"));
    nr = 1;
    rows.each(function(row) {
            row.style.backgroundColor = "";
            if (row.removeClassName == undefined) {
                Element.extend(row);
            }
            row.removeClassName("li_row1");
            row.removeClassName("li_row2");
            row.addClassName("li_row"+((nr % 2) + 1));
            nr++;
    });
}

/**
 * Make the ul list sortable
 *
 * Uses the scriptaculous sortable feature
 */
var initSortableArea = function(offset)
{  
    if (editAllowed) {
        Sortable.create("articlelist", {
           onUpdate: function() {
                setRows();
                new Ajax.Request("index.php?cmd=knowledge{MODULE_INDEX}&section=articles&act=sort&offset="+offset, {
                    method : "post",
                    parameters : Sortable.serialize("articlelist"),
                    onSuccess : function(transport) {
                        if (statusMsg(transport.responseText)) {
                            new Effect.Highlight('article_'+lastDragged, {duration: 1.5});
                        }
                    }
                }); 
           },
           handle : "drag_handle"
        });
    }
}

/**
 * The functionality for the category tree on the left
 */
var catTree = {
    /**
     * Open a category
     */
    openPart : function(obj)
    {
        if (obj.parentNode.cleanWhitespace == undefined) {
            Element.extend(obj.parentNode);
        }
        obj.parentNode.cleanWhitespace();
        var subLists = $A(obj.parentNode.parentNode.childNodes);
        subLists.each(function(elem) {
            try {
                if (elem.tagName == "UL") {
                    //elem.show();
                /* show doesn't work in ie6 */
                    elem.style.display = "";
                }
            } catch(e) {}
        });

        if (obj.select == undefined) {
            Element.extend(obj);
        }
        obj.select('.openClosePicture')[0].src = "images/icons/minuslink.gif";
        obj.nextSibling.src = "../images/modules/knowledge/folder-open.small.png";
        obj.onclick = function() { catTree.closePart(obj); }
    },
    /**
     * Close a category
     */
    closePart : function(obj)
    {
        var subLists = $A(obj.parentNode.parentNode.childNodes);
        subLists.each(function(elem) {
            if (elem.tagName == "UL") {
                if (elem.hide == undefined) {
                    Element.extend(elem);
                }
                elem.hide();
            }
        });
        if (obj.select == undefined) {
            Element.extend(obj);
        }
        obj.select('.openClosePicture')[0].src = "images/icons/pluslink.gif";
        obj.nextSibling.src = "../images/modules/knowledge/folder.small.png";
        obj.onclick = function() { catTree.openPart(obj); }
    }
}

/**
 * Make an article
 */
var switchActive = function(id, obj, action)
{
    new Ajax.Request('index.php', {
        method: 'get',
        parameters: {cmd : "knowledge{MODULE_INDEX}", section : "articles", act : "switchState", id : id, switchTo : action},
        onSuccess: function(transport) {
            if (statusMsg(transport.responseText)) {
                var img = obj.getElementsByTagName("img")[0];
                if (action == 0) {
                    img.src = "images/icons/led_red.gif";
                    obj.onclick = function() { switchActive(id, obj, 1); }
                } else {
                    img.src = "images/icons/led_green.gif";
                    obj.onclick = function() { switchActive(id, obj, 0); }
                }
                new Effect.Highlight('article_'+id);
            }
        }
    });
}

/**
 * Delete an article
 */
var deleteArticle = function(id)
{
    if (confirm("{TXT_CONFIRM_ARTICLE_DELETION}")) {
        new Ajax.Request('index.php', {
            method: 'get',
            parameters: {cmd : "knowledge{MODULE_INDEX}", section : "articles", act : "delete", id : id},
            onSuccess: function() {
                articles.deleteRow(id);
            }
        });
    }
}

