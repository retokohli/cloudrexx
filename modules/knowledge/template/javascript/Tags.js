function Tags(id, lang)
{
    this.tags = $(id);
    this.lang = lang;
    this.getTags('popularity');
    this.typing();
    
    this.availableTags = Array()
    this.loaded = false;
    this.currently_tpying = false;
    
    var ref = this;
    this.tags.onkeyup = function() {
        ref.typing();
    }
}

Tags.prototype.getTags = function(sort)
{
    var ref = this;
    new Ajax.Request('index.php', {
        method: "get",
        parameters : {  cmd : "knowledge",
                        section : "articles", 
                        act : "getTags",
                        sort : sort,
                        lang : ref.lang
        },
        onSuccess : function(transport) {
            var response = transport.responseText.evalJSON();
            $('taglist_'+ref.lang).update(response.html);
            ref.availableTags = $H(response.available_tags);
            ref.loaded = true;
            ref.typing();
        }
    });
    
}

Tags.prototype.addTag = function(id, name, caller)
{
    if (this.tags.value == "") {
        this.tags.value = this.tags.value + name;
    } else {
        if (this.currently_typing) {
            if (this.tags.value.search(/,/) > 0) {
                this.tags.value = this.tags.value.replace(/,[^,]+$/, "");
                this.tags.value = this.tags.value + ", "+name;
            } else {
                this.tags.value = name;
            }
            this.currently_typing = false;   
        } else {
            this.tags.value = this.tags.value + ", "+name;
        }
    }
    
    $('tag_'+id).addClassName("chosen");
    var ref = this;
    caller.onclick = function() {
        ref.removeTag(id, name, caller);
        ref.typing();
    }
};

Tags.prototype.removeTag = function(id, name, caller)
{
    var pattern = new RegExp(name+',?\\s*', 'g');
    this.tags.value = this.tags.value.replace(pattern, "");
    this.tags.value = this.tags.value.replace(/,\s*$/, '');
    
    var ref = this;
    caller.onclick = function() {
        ref.addTag(id, name, caller);
        ref.typing();
    }
}

Tags.prototype.typing = function()
{
    if (this.loaded) {
        this.resetHighlights();
        var value = this.tags.value;
        if (value != "") {
            var tags = value.split(/\s*,\s*/);
            for (var i = 0; i < tags.length; i++) {
                if (tags[i] != "") {
                    tags[i] = this.trim(tags[i]);
                    var result = this.searchValue(tags[i]);
                    if (result.get('type') == 1) {
                        this.highlight(result.get('id'), "chosen");
                    } else if (result.get('type') == 2) {
                        this.highlight(result.get('id'), "typing");
                        this.currently_typing = true;
                    }
                }
            }
        }
    }
};
 
Tags.prototype.searchValue = function(val)
{
    var keys = this.availableTags.keys();
    var response = $H();
    for (var i = 0; i < keys.length; i++) {
        if (this.availableTags.get(keys[i]) == val) {
            response.set('id', keys[i]);
            response.set('type', 1);
            return response;
        } else {
            var pattern = new RegExp("^"+val+".*", "i");
            if (pattern.exec(this.availableTags.get(keys[i]))) {
                response.set('id', keys[i]);
                response.set('type', 2);
                return response;
            }
        }
    }
    response.set('type', 0);
    return response;
};


Tags.prototype.highlight = function(id, type)
{
    this.currently_typing = false;
    var obj = $('tag_'+id);
    if (obj) {
        if (type == "chosen") {
            obj.removeClassName("typing");
            obj.addClassName("chosen");
        } else if (type == "none") {
            obj.removeClassName("chosen");
            obj.removeClassName("typing");
        }else {
            obj.removeClassName("chosen");
            obj.addClassName("typing");
        }
    }
};

Tags.prototype.resetHighlights = function()
{
    var ref = this;
    this.availableTags.each(function(e) {
        ref.highlight(e.key, "none");
    });
};

Tags.prototype.trim = function(str) { 
    str = str.replace(/^\s*/, '').replace(/\s*$/, ''); 
    return str;
}