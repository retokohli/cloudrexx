/*
 * useful stuff with forms & ajax.
 * all functions want jquery elements where elements are requested.
 */

/**
 * Associative Array from form contents. 
 * 
 * Useful with jQuery.ajax()
 * 
 * @todo implement handler for selects.
 * @param form the form 
 * @param arrayName supply this if you want to build an array for post
 * @return { <name> : <val>, ... }
 */
function formToArray(form, arrayName)
{
    var data = {};

    var handleFields = function(fields)
    {
	fields.each(function(index)
	    {
		input = $J(fields[index]);
		var name = input.attr('name');
		if(name)
		{
		    if(arrayName)
		    {
			name = arrayName+'['+name+']';
		    }
		    data[name] = input.val();
		}
		    
	    });
    };
    handleFields(form.find('input'));
    handleFields(form.find('textarea'));

    return data;
}