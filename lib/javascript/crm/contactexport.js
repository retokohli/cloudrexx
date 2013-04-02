$J(function(){
    var csvData;
    var totalRows  = 0;
    var currentRow = 1;
    $J('.choose-image').click(function(){        
        $J('#importfile').trigger('click');
    });
    $J('#importfile').change(function(){
        var file = $J(this).val();
        var filename = file.replace(/^.*[\\\/]/, '');

        $J('.choose-image-content .file-text').html(filename);
        $J('.choose-image-text').fadeOut(function(){
           $J('.choose-image-content').fadeIn();
        });
        $J('#start_upload').removeClass('disabled');
    });
    $J('#start_upload').click(function(){
       if (!$J(this).hasClass('disabled')) {
           $J('.ui-state-highlight p span.text_msg, .ui-state-error p span.text_msg').html('');
           $J('.ui-state-highlight, .ui-state-error').hide();
           
           fileName = $J('#importfile').val();
           var ext = fileName.split('.').pop();

           if (ext == 'csv') {
                return true;
           } else {               
               $J('.ui-state-error p span.text_msg').html('Please choose a csv to upload');
               $J('.ui-state-error').fadeIn('slow');
               $J('html, body').animate({
                    scrollTop: $J(".ui-state-error").offset().top
               }, 2000);
           }
       }
       return false;
    });

    $J(".reNext a").click(function(){
        currentRow++;
        
        if (currentRow == totalRows) {
            $J(".reNext").hide();
        }
        if (currentRow <= totalRows) {
            $J("table#mapCSVColumn tbody tr").each(function(index){
               $J(this).find("td").eq(2).html(csvData[currentRow][index]);
            });
            $J(".rePrevious").show();
        }
    });

    $J(".rePrevious a").click(function(){
        currentRow--;

        if (currentRow == 1) {
            $J(".rePrevious").hide();
        }
        if (currentRow >= 1) {
            $J("table#mapCSVColumn tbody tr").each(function(index){
               $J(this).find("td").eq(2).html(csvData[currentRow][index]);
            });
            $J(".reNext").show();
        }            
    });

    $J('#frmImport').bind('submit', function(e) {
        e.preventDefault(); // <-- important
        $J(this).ajaxSubmit({
            dataType:  'json',
            beforeSend: function() {
                $J('.import_step1 .actions').hide();
                $J('.import_step1 .ajax_loading').show();                
            },            
            success: function(json) {                
                var responseData = $J.parseJSON($J.base64Decode(json.data));
                csvData   = $J.parseJSON($J.base64Decode(json.contactData));
                totalRows = json.totalRows;
                $J(".rePrevious, .reNext").hide();
                if (totalRows > currentRow) {
                    $J(".reNext").show();
                }
                var stepTwoArea = $J("#mapCSVColumn tbody");
                                
                stepTwoArea.html("");
                
                $J("#csvColumnTemplate").tmpl(responseData, {
                  renderSelectBox: function(rowIndex) {                    
                    var columnSelector = $J("#crm_contact_option_base").clone();

                    columnSelector.attr("id", $J.vprintf( "columnSelector_%d", [ rowIndex ] ));
                    columnSelector.show();

                    return $J("<div>").append(columnSelector).html();
                    
                  }
                }).appendTo(stepTwoArea);

                var selectBox = $J("#mapCSVColumn select");
                
                selectBox.live("change", function() {
                    var curSelector = $J(this);

                    if (curSelector.find("option:first").is(":selected"))
                        curSelector.parents("td").addClass("not_match");
                    else
                        curSelector.parents("td").removeClass("not_match");
                });

                selectBox.change();
                
                $J('#step1').fadeOut(function(){
                    $J('#step2').fadeIn('slow');
                });
                
            }
        });

        // !!! Important !!!
        // always return false to prevent standard browser submit and page navigation
        return false;
    });
    $J.ajax({
        dataType: "json",
        url     : "index.php?cmd=crm&act=interface&tpl=importoptions",
        success : function loadImportOptions(data) {
                    $J("#columnSelectorTemplate").tmpl(data).appendTo("#crm_contact_option_base");
                    $J("#crm_contact_option_base").find("option[name=header]").each(function() {
                        var curItem = $J(this);

                        curItem.nextUntil('option[name=header]').wrapAll($J("<optgroup>").attr("label", curItem.val()));

                        curItem.remove();
                    });
                  }
    });
});