jQuery(document).ready(function() {
    jQuery("#form-0-iso1, #form-0-country").change(function() {
        generateLabel();
    });

    function generateLabel() {
        var iso1 = jQuery("#form-0-iso1").val();
        var alpha2 = jQuery("#form-0-country").val();

        jQuery.getJSON(
            "index.php?cmd=jsondata",
            {
                object: "Locale",
                act: "getGeneratedLabel",
                "iso1": iso1,
                "alpha2": alpha2
            },
            function(data) {
                jQuery('#form-0-label').val(data.data);
            }
        );

    }
});