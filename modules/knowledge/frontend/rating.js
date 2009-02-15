var Rating = function(id, average, max_rating, actual_id, indicator) {
    var foo = new Starbox(id, average, { 
    	overlay: 'pointy.png', 
    	className: 'pointy', 
    	total: 1,
    	stars: max_rating,
    	indicator: indicator,
    	max: max_rating,
    	buttons: max_rating,
    	onRate : function(element, info) {
    		info.id = actual_id;
    		var foo = new Ajax.Request('index.php?section=knowledge&act=rate', {
    			method: "post",
    			parameters : info
    		});
    	}
    });
};