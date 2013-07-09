var calendarFrontend = {
    
    _validation : function(form) {
        $form = form;
        that  = this;
        var frmError = false;
        
        $form.find('.validate_required').each(function(){
            $field = $J(this);
            if ( !that._validateField($field) ) {
                $field.css('border', '#ff0000 1px solid');
                $J("#calendarErrorMessage").show();
                frmError = true;
            } else {
                $field.css('border', '');
            }
        });
        
        if ( frmError ) {
            return false;
        }
        
        return true;
    },
    _validateField : function(field) {
        /**
         * inspired from validation Engine
         */
        switch (field.prop("type")) {
            case "text":
            case "password":
            case "textarea":
            case "file":
            case "select-one":
            case "select-multiple":
            default:
                    var field_val      = $J.trim( field.val() );                    
                    if (
                               ( !field_val )                            
                    ) {
                            return false;
                    }
                    return true;
                    break;
            case "radio":
            case "checkbox":
                    var form = field.closest("form");
                    var name = field.attr("name");
                    if (form.find("input[name='" + name + "']:checked").size() == 0) {
                            if (form.find("input[name='" + name + "']:visible").size() == 1)
                                    return true;
                            else
                                    return false;
                    }
                    break;
        }

    }
};

$J(function(){
    $J("#formModifyEvent").submit(function(){
        form = $J(this);
        return calendarFrontend._validation(form);
    });
});
