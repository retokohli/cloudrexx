(function($) {
    cx.ready(function() {
        $('#instance_table').append('<div id ="load-lock"></div>');
        
        $(".defaultCodeBase").change(function() {
            domainUrl = cx.variables.get('baseUrl', 'MultiSite') + cx.variables.get('cadminPath', 'contrexx') + "index.php?cmd=JsonData&object=MultiSite&act=updateDefaultCodeBase";
            cx.jQuery.ajax({
                dataType: "json",
                url: domainUrl,
                data: {
                    defaultCodeBase: $(this).val(),
                },
                type: "POST",
               
                success: function(response) {
                    if (response.data) {
                        cx.tools.StatusMessage.showMessage(response.data, null,2000);
                    }
                }

            });
        });
        $('.changeWebsiteStatus').focus(function() {
//Store old value
            $(this).data('lastValue', $(this).val());
            
            cx.bind("loadingStart", cx.lock, "websitestatus");
            cx.bind("loadingEnd", cx.unlock, "websitestatus");
            //changing dropdown value
        }).change(function() {
            domainUrl = cx.variables.get('baseUrl', 'MultiSite') + cx.variables.get('cadminPath', 'contrexx') + "index.php?cmd=JsonData&object=MultiSite&act=updateWebsiteState";
            var websiteDetails = $(this).attr('data-websiteDetails').split("-");
            if (confirm("Please confirm to change the state of website " + websiteDetails[1] + ' to ' + $(this).val())) {
                cx.trigger("loadingStart", "websitestatus", {});
                cx.tools.StatusMessage.showMessage("<div id=\"loading\">" + cx.jQuery('#loading').html() + "</div>");
                cx.jQuery.ajax({
                    dataType: "json",
                    url: domainUrl,
                    data: {
                        websiteId: websiteDetails[0],
                        status: $(this).val()
                    },
                    type: "POST",
                    success: function(response) {
                        if (response.data) {
                            cx.tools.StatusMessage.showMessage(response.data, null, 2000);
                        }
                        cx.trigger("loadingEnd", "websitestatus", {});
                    }

                });
            }else{
                $(this).val($(this).data('lastValue'));
            }
        });
        /**
         * Locks the website status in order to prevent user input
         */
        cx.lock = function() {
            cx.jQuery("#load-lock").show();
        };
        /**
         * Unlocks the website status in order to allow user input
         */
        cx.unlock = function() {
            cx.jQuery("#load-lock").hide();
        };
        // show license
        $('.showLicense').click(function() {
            $('#instance_table').append('<div id ="load-lock"></div>');
            cx.bind("loadingStart", cx.lock, "showLicense");
            cx.bind("loadingEnd", cx.unlock, "showLicense");
            cx.trigger("loadingStart", "showLicense", {});
            
            var className = $(this).attr('class'),
                id        = parseInt(className.match(/[0-9]+/)[0], 10),
                title     = cx.variables.get('getLicenseTitle', "multisite/lang") + $(this).data('websitename');
            
            domainUrl = cx.variables.get('baseUrl', 'MultiSite') + cx.variables.get('cadminPath', 'contrexx') + "index.php?cmd=JsonData&object=MultiSite&act=getLicense";                        
            $.ajax({
                url: domainUrl,
                type: "POST",
                data: {command: 'getLicense', websiteId: id},
                dataType: "json",
                beforeSend : function() {
                    cx.tools.StatusMessage.showMessage("<div id=\"loading\">" + $('#loading').html() + "</div>");
                },
                success: function(response) {
                    cx.trigger("loadingEnd", "showLicense", {});
                    if (response.status == 'error') {
                        cx.tools.StatusMessage.showMessage(response.message, null, 4000);
                    }
                    if (response.status == 'success') {
                        if (response.data.result != 'undefined') {
                            $table = $('<table cellspacing="0" cellpadding="3" border="0" class="adminlist" width="100%" />');
                            $.each(response.data.result, function(key, data) {
                                $tr = $('<tr />');
                                $('<td />')
                                    .html(key)
                                    .appendTo($tr);
                                
                                container = $('<div />')
                                                .attr('id', key);
                                licenseContent = data.content;
                                if (typeof licenseContent === 'object') {
                                    jsonString = JSON.stringify(licenseContent);                                    
                                    $('<span />')
                                        .attr('id', 'ui_'+key)
                                        .html(jsonString)
                                        .hide()
                                        .appendTo(container);
                                    textContent = $('<span />');
                                    
                                    $.each(licenseContent, function(key, data) {
                                        if (typeof data === 'object') {
                                            $('<span />')
                                                .addClass('ui_license '+data.lang_name)
                                                .css('font-weight', 'bold')
                                                .html(data.lang_name)
                                                .appendTo(textContent);
                                            textContent.append(':&nbsp;');
                                            $('<span />')
                                                .addClass('ui_licenseMsg '+data.lang_id)
                                                .html(data.message)
                                                .appendTo(textContent);
                                            textContent.append('<br />');
                                        } else {
                                            textContent.append(key + ' : ' + data + ', ')
                                        }
                                    });
                                    container.append(textContent);
                                } else {
                                    $('<span >')                                        
                                        .html(licenseContent)
                                        .appendTo(container);                                    
                                }
                                $('<a >')
                                    .attr('href', 'javascript:void(0);')
                                    .attr('title', 'Edit License Information')
                                    .addClass('editLicense editLicenseData editLicense_'+ key)
                                    .data('field', key)
                                    .data('websiteid', id)
                                    .data('value', licenseContent)
                                    .data('options',data.values)
                                    .data('editType',data.type)
                                    .click(function(){
                                        Multisite.editLicense($(this));
                                    })
                                    .appendTo(container);
                                $('<td />')
                                    .append(container)
                                    .appendTo($tr);
                            
                                $table.append($tr);
                            });
                        } else {
                            $table = $('<span>').html('No data found!');
                        }
                    }
                    cx.tools.StatusMessage.showMessage(cx.variables.get('licenseInfo', "multisite/lang"), null, 3000);
                    cx.ui.dialog({
                        width: 820,
                        height: 400,
                        title: title,
                        content: $('<div />').append($table),
                        autoOpen: true,
                        modal: true,
                        buttons: {
                            "Close": function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        });
        // execute query on websites / service server's websites
        $('.executeQuery').click(function() {
            $('#instance_table').append('<div id ="load-lock"></div>');
            cx.bind('loadingStart', executeQueryLock, 'executeSql');
            cx.bind('loadingEnd', executeQueryUnlock, 'executeSql');
            var title = $(this).attr('title');
            var paramsArr = ($(this).attr('data-params')).split(':');
            var argName = paramsArr[0];
            var argValue = paramsArr[1];
            var initialContent = '<div><form id="executeSql"><div id="statusMsg"></div><div class="resultSet"></div><textarea rows="10" cols="100" class="queryContent" name="executeQuery"></textarea></form></div>';
            cx.ui.dialog({
                width: 820,
                height: 400,
                title: title,
                content: initialContent,
                autoOpen: true,
                modal: true,
                buttons: {
                    'Cancel': function() {
                        $('#executeSql').remove();
                        $(this).dialog('close');
                    },
                    'Execute': function() {
                        $('.resultSet').html('');
                        cx.trigger('loadingStart', 'executeSql', {});
                        cx.tools.StatusMessage.showMessage("<div id=\"loading\">" + $('#loading').html() + "</div>");
                        var query = $('.queryContent').val();
                        if (query == '') {
                            cx.tools.StatusMessage.showMessage(cx.variables.get('plsInsertQuery', "multisite/lang"), null, 3000);
                            cx.trigger('loadingEnd', 'executeSql', {});
                            return false;
                        } else {
                            if ($('.ui-dialog-buttonpane button:contains("Stop Execution...") span').hasClass('stop-execution')) {
                                domainUrl = cx.variables.get('baseUrl', 'MultiSite') + cx.variables.get('cadminPath', 'contrexx') + "index.php?cmd=JsonData&object=MultiSite&act=stopQueryExecution";
                                $.ajax({
                                    url: domainUrl,
                                    type: 'POST',
                                    dataType: 'json',
                                    success: function(response) {
                                        cx.tools.StatusMessage.showMessage(response.data.message, null, 3000);
                                        $('.ui-dialog-buttonpane button:contains("Stop Execution...") span').text('Execute').removeClass('stop-execution');
                                    }
                                });
                                
                            } else {
                                $('.ui-dialog-buttonpane button:contains("Stop Execution...") span').addClass('stop-execution');
                                domainUrl = cx.variables.get('baseUrl', 'MultiSite') + cx.variables.get('cadminPath', 'contrexx') + "index.php?cmd=JsonData&object=MultiSite&act=executeSql";
                                $.ajax({
                                    url: domainUrl,
                                    type: 'POST',
                                    data:{
                                        query: query,
                                        mode: argName,
                                        id: argValue,
                                        command: 'executeSql'
                                        },
                                    dataType: 'json',
                                    success: function(response) {
                                        if (response.status == 'error') {
                                            cx.trigger('loadingEnd', 'executeSql', {});
                                            cx.tools.StatusMessage.showMessage(cx.variables.get('errorMsg', 'multisite/lang'), null, 3000);
                                            $('#statusMsg').text(response.message);
                                            cx.trigger('loadingEnd', 'executeSql', {});
                                        }
                                        if (response.status == 'success' && argName == 'website') {
                                            $('.resultSet').html(parseQueryResult(response));
                                            cx.tools.StatusMessage.showMessage(cx.variables.get('completedMsg', 'multisite/lang'), null, 3000);
                                            cx.trigger('loadingEnd', 'executeSql', {});
                                        }
                                        if (response.status == 'success' && argName == 'service') {
                                            executeSql();
                                        }
                                    }
                                });
                            }
                        }
                    }
                },
                close: function() { 
                    $('#executeSql').remove();
                }
            });
        });

        /**
         * Locks the text area and Execute button in order to prevent user input
         */
        var executeQueryLock = function() {
            $('.queryContent').attr('readonly', true);
            $('.ui-dialog-buttonpane button:contains("Execute") span').text('Stop Execution...');
        };
        
        /**
         * Unlocks the text area and Execute button in order to allow user input
         */
        var executeQueryUnlock = function() {
            $('.queryContent').attr('readonly', false);
            $('.ui-dialog-buttonpane button:contains("Stop Execution...") span').text('Execute').removeClass('stop-execution');
        };
            
        /**
         * Show the Executed Query result in dialog window
         */
        var parseQueryResult = function(response) {
            var html = '';
            var resultTable = '';
            $.each(response.data, function(key, value) {
                if (value.status) {
                    var theader = '<table cellspacing="0" cellpadding="3" border="0" class="adminlist">';
                    var col_count = 0;
                    var tbody = "";
                    var thead = "";
                    if(value.resultSet) {
                        var cols = Object.keys(value.resultSet).length;
                        $.each(value.resultSet, function(resultSetkey, resultSetData) {
                            tbody += "<tr class =row1>";
                            if (col_count == 0) {
                                thead += "<th colspan='2'>" + value.websiteName + "</th>";
                            }
                            if (col_count < cols) {
                                var count = 0;
                                var tsbody = "";
                                var tshead = "";
                                tbody += "<td><div class='" + resultSetData.queryStatus + "'>" + resultSetData.query + "</td>";
                                if (typeof resultSetData.resultValue === 'object') {
                                    var no_cols = Object.keys(resultSetData.resultValue).length;
                                    $.each(resultSetData.resultValue, function(resultValueKey, resultValueData) {
                                        tsbody += "<tr class =row1>";
                                        for (resultValueKey in resultValueData) {
                                            if (count == 0) {
                                                tshead += "<th>";
                                                tshead += resultValueKey;
                                                tshead += "</th>"
                                            }
                                            if (count < no_cols) {
                                                tsbody += "<td>";
                                                tsbody += resultValueData[resultValueKey];
                                                tsbody += "</td>"
                                            }
                                        }
                                        count++;
                                        tsbody += "</tr>";
                                    });
                                } else if (resultSetData.resultValue) {
                                    tsbody += "<tr class =row1>";
                                    tsbody += "<td>";
                                    tsbody += resultSetData.resultValue;
                                    tsbody += "</td>";
                                    tsbody += "</tr>";
                                }
                                resultingTable = theader + tshead + tsbody + "</table></br>";
                                tbody += "<td>" + resultingTable + "</td>";
                            }
                            col_count++;
                            tbody += "</tr>";
                        });
                        html +=  theader + thead + tbody + "</table></br>";
                    } 
                }
            });
            return html;
        };
        
        //Login to remote website when the following click operation is performed
        $('.remoteWebsiteLogin').click(function() {
            cx.bind("loadingStart", cx.lock, "remoteLogin");
            cx.bind("loadingEnd", cx.unlock, "remoteLogin");
            cx.trigger("loadingStart", "remoteLogin", {});
            cx.tools.StatusMessage.showMessage("<div id=\"loading\">" + cx.jQuery('#loading').html() + "</div>");
            domainUrl = cx.variables.get('baseUrl', 'MultiSite') + cx.variables.get('cadminPath', 'contrexx') + "index.php?cmd=JsonData&object=MultiSite&act=remoteLogin";
            $.ajax({
                url: domainUrl,
                type: "POST",
                data: {websiteId: $(this).attr('data-id')},
                dataType: "json",
                success: function(response) {
                    if (response.status == 'error' && response.data.status == 'error') {
                        cx.tools.StatusMessage.showMessage(response.data.message, null, 4000);
                        cx.trigger("loadingEnd", "remoteLogin", {});
                    }
                    if (response.status == 'success' && response.data.status == 'success') {
                        cx.tools.StatusMessage.showMessage(response.data.message, null, 2000);
                        cx.trigger("loadingEnd", "remoteLogin", {});
                        window.open(response.data.webSiteLoginUrl, '_blank');
                    }
                }
            });
        });
        
        //Fetch multisite Website Configuration settings
        $('.multiSiteWebsiteConfig').click(function() {
            var title = $(this).data('title'),
                websiteId = $(this).attr('data-id');
            cx.bind("loadingStart", cx.lock, "multiSiteWebsiteConfig");
            cx.bind("loadingEnd", cx.unlock, "multiSiteWebsiteConfig");
            cx.trigger("loadingStart", "multiSiteWebsiteConfig", {});
            cx.tools.StatusMessage.showMessage("<div id=\"loading\">" + cx.jQuery('#loading').html() + "</div>");
            domainUrl = cx.variables.get('baseUrl', 'MultiSite') + cx.variables.get('cadminPath', 'contrexx') + "index.php?cmd=JsonData&object=MultiSite&act=modifyMultisiteConfig";
            $.ajax({
                url: domainUrl,
                type: "POST",
                data: {websiteId: websiteId},
                dataType: "json",
                success: function(response) {
                    if (response.status == 'success' && response.data.status == 'success') {
                        var $table = $('<table style="width: 100% !important;position: static;margin: .5em 1em;padding:0;"/>')
                                         .attr({cellspacing : "0", cellpadding: "3", border: "0"})
                                         .addClass('adminlist');
                        
                        $.each(response.data.result, function(key, data) {
                            $tr = $('<tr />');
                            
                            $('<td />')
                                .html('<strong>' + data.name + '</strong>')
                                .appendTo($tr);                            
                            
                            container = $('<span />');
                            
                            $('<span />')
                                .attr('id', data.name)
                                .html(data.value)
                                .appendTo(container);
                            
                            $('<a href="javascript:void(0);" />')
                                .addClass('editMultisiteConfig '+ data.name)
                                .attr('title', 'Edit Multisite Configuration of ' + data.name)
                                .data('field', data.name)
                                .data('websiteId', websiteId)
                                .click(function(){
                                    Multisite.editMultisiteConfig($(this));
                                })
                                .appendTo(container);
                            
                            
                            $td = $('<td />')
                                    .append(container);
                            $('<span />')
                                .attr('id', 'editMultisiteConfig_' + data.name)
                                .html(JSON.stringify(data))
                                .hide()
                                .appendTo($td);
                            
                            $tr.append($td);
                            
                            $table.append($tr);
                        });
                                                
                        cx.tools.StatusMessage.showMessage(response.data.message, null, 2000);
                    }
                    if (response.status == 'error' && response.data.status == 'error') {
                        cx.tools.StatusMessage.showMessage(response.data.message, null, 4000);
                    }
                    cx.ui.dialog({
                        width: 820,
                        height: 400,
                        title: title,
                        content: $table,
                        autoOpen: true,
                        modal: true,
                        buttons: {
                            "Close": function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                    cx.trigger("loadingEnd", "multiSiteWebsiteConfig", {});
                }
            });
        });
        
        /**
         * Execute the queued Sql Query in corresponding website
         */
        var executeSql = function() {
            domainUrl = cx.variables.get('baseUrl', 'MultiSite') + cx.variables.get('cadminPath', 'contrexx') + 'index.php?cmd=JsonData&object=MultiSite&act=executeQueryBySession';
            
            $.ajax({
                url: domainUrl,
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.status == 'error') {
                        cx.tools.StatusMessage.showMessage(response.message, null, 3000);
                        cx.trigger('loadingEnd', 'executeSql', {});
                        return;
                    }
                    if (response.data.status == 'error') {
                        cx.tools.StatusMessage.showMessage(cx.variables.get('completedMsg', 'multisite/lang'), null, 3000);
                        cx.trigger('loadingEnd', 'executeSql', {});
                        return;
                    }
                    if (response.status == 'success') {
                        $('.resultSet').append(parseQueryResult(response));
                        executeSql();
                    }
                }
            });
        };
    });
})(jQuery);

var Multisite = {
    //Edit License data
    editLicense: function($this) {
        var fieldLabel = $this.data('field'),
            title = $this.attr('title'),
            websiteId = $this.data('websiteid'),
            editType = $this.data('editType'),
            licenseArray = ['licenseMessage', 'dashboardMessages', 'licenseGrayzoneMessages'],
            liceneseTable = $J('<div />');
            
        if ($J.inArray(fieldLabel, licenseArray) !== -1) {
            var jsonString = $J('#ui_' + fieldLabel).html(),
                uiDiv = $J('<div>')
                            .addClass('tab-' + fieldLabel)
                            .attr('id', 'tab_menu'),
                uiTab = $J('<ul />'),
                i = 1;
            $J.each(JSON.parse(jsonString), function(index, data) {
                if (typeof data === 'object') {
                    $J('<li>')
                        .append(
                            $J('<a>')
                                .attr('href', '#tabs-' + i)
                                .html(data.lang_name)
                        )
                        .appendTo(uiTab);
                
                    $uiTable = $J('<form>')
                        .addClass('saveLicense')
                        .append($J('<table  cellspacing="0" cellpadding="3" border="0" class="adminlist licenceEdit" width="100%">'));
                    $tr = $J('<tr />');
                    $J('<td>')
                        .html(
                            $J('<label>').html(fieldLabel)
                        )
                        .appendTo($tr);
                        editFieldName = 'licenseValue['+data.lang_id+'][text]';
                    $J('<td>')
                        .html(                      
                            getEditOption(editType, editFieldName, fieldLabel, data.message, $this.data('options')) 
                        )
                        .appendTo($tr);
                    $uiTable.append($tr);
                    $uiTable.append(
                    $J('<input type="hidden">')
                       .attr('name','licenseLabel')
                       .val(fieldLabel)
                       );
                    
                    $J('<div>')
                        .attr('id', 'tabs-' + i)
                        .data('langId', data.lang_id)
                        .data('langName', data.lang_name)
                        .append($uiTable)
                        .appendTo(uiDiv);
                
                    i++;
                }
            });
            uiTab.prependTo(uiDiv);
            uiDiv.appendTo(liceneseTable);
        } else {
            $uiTable = $J('<form>')
                        .addClass('saveLicense')
                        .append($J('<table  cellspacing="0" cellpadding="3" border="0" class="adminlist licenceEdit" width="100%">'));
            $tr = $J('<tr />');
            $J('<td>')
                .html(
                    $J('<label>').html(fieldLabel)
                )
                .appendTo($tr);
            $J('<td>')
                    .html(getEditOption(editType, 'licenseValue', fieldLabel, $this.data('value'),$this.data('options')))
                    .appendTo($tr);
            $uiTable.append($tr);
            $uiTable.append(
                    $J('<input type="hidden">')
                       .attr('name','licenseLabel')
                       .val(fieldLabel)
                       );
            $uiTable.appendTo(liceneseTable);
        }
        domainUrl = cx.variables.get('baseUrl', 'MultiSite') + cx.variables.get('cadminPath', 'contrexx') + "index.php?cmd=JsonData&object=MultiSite&act=editLicense";
        cx.ui.dialog({
            width: 500,
            height: 250,
            title: title,
            content: liceneseTable,
            autoOpen: true,
            modal: true,
            buttons: {
                "Save": function() {
                    cx.tools.StatusMessage.showMessage("<div id=\"loading\">" + cx.jQuery('#loading').html() + "</div>");
                        var formValues = $J( "form.saveLicense" ).serialize();
                    $J.ajax({
                        url: domainUrl,
                        type: "POST",
                        data: formValues +"&websiteId="+ websiteId,
                        dataType: "json",
                        success: function(response) {
                            if (response.data.status == 'error') {
                                cx.tools.StatusMessage.showMessage(response.data.message, null, 4000);
                            }
                            if (response.status == 'success' && response.data.status == 'success') {
                                if (typeof response.data.data === 'object') {
                                    var uiTabContent = [];
                                console.log(response.data.data);
                                    $J.each(response.data.data,function(key,data){
                                        $J('#'+fieldLabel).find('span.ui_licenseMsg.'+key).text(data.text);
                                        uiTabContent.push({lang_id: key, message:data.text });
                                    });
                                    $J('#ui_' + fieldLabel).html(JSON.stringify(uiTabContent));
                                } else {
                                    $J('#'+fieldLabel+' span').text(response.data.data);
                                    $J('.editLicense_' + fieldLabel).data('value', response.data.data);
                                }
                                cx.tools.StatusMessage.showMessage(response.data.message, null, 2000);
                            }
                        }
                    });
                    $J('#editLicense').remove();
                    $J('#tab_menu').remove();
                    $J(this).dialog("close");
                },
                "Cancel": function() {
                    $J('#editLicense').remove();
                    $J('#tab_menu').remove();
                    $J(this).dialog("close");
                }
            },
            close: function() {
                $J('#editLicense').remove();
                $J('#tab_menu').remove();
            }
        });
        $J('#tab_menu').tabs();
    },
    
    //Edit Multisite Configuration Option
    editMultisiteConfig: function($this) {
        var configOption = $this.data('field'),
                websiteId = $this.data('websiteId'),
                title = $this.attr('title'),
                configData = JSON.parse($J("#editMultisiteConfig_" + configOption).text()),
                configGroup = configData.group,
                $table = $J('<table style="width: 100% !important;position: static;margin: .5em 1em;padding:0;"/>')
                            .attr({cellspacing: "0", cellpadding: "3", border: "0", id: "editMultisiteConfig"})
                            .addClass('adminlist'),
                $tr = $J('<tr />'),
                $td = $J('<td />').css("border", "none");
        $J('<td />')
                .html('<strong>' + configOption + '</strong>').css("border", "none")
                .appendTo($tr);

        if (configData.type == 'dropdown' && configData.values != 'null') {
            var selectOptions = [],
                    selected = '',
                    $selectBox = $J('<select />').attr({name: "editConfig_" + configOption, id: "editConfig_" + configOption});

            selectOptions = configData.values.replace(/\s+/g, '').split(',');
            $J.each(selectOptions, function(key, data) {
                $option = $J('<option />').html(data.substr(0, data.indexOf(':')));
                if (data.substr(0, data.indexOf(':')) == configData.value) {
                    $option.attr('selected', 'selected');
                }
                $selectBox.append($option);
            });
            $td.append($selectBox);
            $tr.append($td);
            $table.append($tr);
        } else {
            $J('<input />').attr({id: "editConfig_" + configOption, name: "editConfig_" + configOption, type: "text"})
                    .val(configData.value)
                    .appendTo($td);
            $tr.append($td);
            $table.append($tr);
        }
        domainUrl = cx.variables.get('baseUrl', 'MultiSite') + cx.variables.get('cadminPath', 'contrexx') + "index.php?cmd=JsonData&object=MultiSite&act=modifyMultisiteConfig";
        cx.ui.dialog({
            width: 450,
            height: 180,
            title: title,
            content: $table,
            autoOpen: true,
            modal: true,
            buttons: {
                "Save": function() {
                    cx.tools.StatusMessage.showMessage("<div id=\"loading\">" + cx.jQuery('#loading').html() + "</div>");
                    var configValue = $J('#editConfig_' + configOption).val();
                    $J.ajax({
                        url: domainUrl,
                        type: "POST",
                        data: {configGroup: configGroup, configOption: configOption, configValue: configValue, websiteId: websiteId},
                        dataType: "json",
                        success: function(response) {
                            if (response.data.status == 'error') {
                                cx.tools.StatusMessage.showMessage(response.data.message, null, 4000);
                            }
                            if (response.status == 'success' && response.data.status == 'success') {
                                configData.value = configValue;
                                $J('span#' + configOption).text(configValue);
                                $J('span#editMultisiteConfig_' + configOption).text(JSON.stringify(configData));
                                cx.tools.StatusMessage.showMessage(response.data.message, null, 2000);
                            }
                        }
                    });
                    $J('#editMultisiteConfig').remove();
                    $J(this).dialog("close");
                },
                "Cancel": function() {
                    $J('#editMultisiteConfig').remove();
                    $J(this).dialog("close");
                }
            },
            close: function() {
                $J('#editMultisiteConfig').remove();
            }
        });
    }
};

function getEditOption(type, name, fieldLabel, editValue, editOptions) {
    switch (type) {
        case 'text':
            htmlResult = $J('<input type="text"/>')
                    .addClass(fieldLabel)
                    .attr('name', name)
                    .val(editValue);
            break;

        case 'radio':
            htmlResult = $J('<div />');
            $J.each(editOptions, function(key, value) {
                valueAndLabel = value.split(':');
                radio = $J('<input type="radio"/>')
                        .addClass(fieldLabel)
                        .attr('name', name)
                        .val(valueAndLabel['0']);
                if (valueAndLabel['0'] == editValue) {
                    radio.attr('checked', 'checked');
                }
                label = $J('<label>').html(valueAndLabel['1']);

                htmlResult.append(radio).append(label);
            });
            break;

        case 'textarea':
            htmlResult = $J('<textarea rows="4" cols="40">')
                            .addClass(fieldLabel)
                            .attr('name', name)
                            .html(editValue);
            break;

        case 'dropdown':
            htmlResult = $J('<select')
                            .addClas(fieldLabel)
                            .attr('name', name);
            $J.each(editOptions, function(key, value) {
                valueAndLabel = value.split(':');
                $J(htmlResult)
                        .append($J("<option></option>")
                        .attr("value", valueAndLabel['0'])
                        .text(valueAndLabel['1']));
                if (valueAndLabel['0'] == editValue) {
                    htmlResult.attr('selected', 'selected');
                }
            });
            
            break;

        case 'checkbox':
            htmlResult = $J('<div />');
            $J.each(editOptions, function(key, value) {
                valueAndLabel = value.split(':');
                isChecked = valueAndLabel['0'] == editValue ? 'checked' : '';
                checkbox = $J('<input type="checkbox"/>')
                                .addClass(fieldLabel)
                                .attr('name', name)
                                .val(valueAndLabel['0']);
                if (valueAndLabel['0'] == editValue) {
                    checkbox.attr('checked', 'checked');
                }
                label = $J('<label>').html(valueAndLabel['1']);

                htmlResult.append(checkbox).append(label);

            });

            break;
    }
    return htmlResult;
}