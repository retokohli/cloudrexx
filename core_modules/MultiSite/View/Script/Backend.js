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
            var className = $(this).attr('class');
            var id = parseInt(className.match(/[0-9]+/)[0], 10);
            var title = $(this).attr('title');
            cx.tools.StatusMessage.showMessage("<div id=\"loading\">" + $('#loading').html() + "</div>");
            domainUrl = cx.variables.get('baseUrl', 'MultiSite') + cx.variables.get('cadminPath', 'contrexx') + "index.php?cmd=JsonData&object=MultiSite&act=getLicense";
            $.ajax({
                url: domainUrl,
                type: "POST",
                data: {command: 'getLicense', websiteId: id},
                dataType: "json",
                success: function(response) {
                    cx.trigger("loadingEnd", "showLicense", {});
                    if (response.status == 'error') {
                        cx.tools.StatusMessage.showMessage(response.message, null, 4000);
                    }
                    if (response.status == 'success') {
                        var theader = '<table cellspacing="0" cellpadding="3" border="0" class="adminlist" width="100%">';
                        var tbody = '';
                        var i = 1;
                        $.each(response.data.result, function(key, data) {
                            if (typeof data === 'object') {
                                var jsonString = JSON.stringify(data);
                                tbody += '<tr>';
                                tbody += '<td>' + key + '</td><td><span id="ui_'+key+'" style="display: none;">'+jsonString+'</span><span id="'+ key + '">';
                                $.each(JSON.parse(jsonString), function(key, data) {
                                    if (typeof data === 'object') {
                                        tbody += '<strong>' + data.lang_name + ':</strong>&nbsp;';
                                        tbody += data.message + '<br>';
                                    } else {
                                        tbody += key + ' : ' + data + ', ';
                                    }
                                });
                                tbody += '</span><a href="javascript:void(0);" class="editLicense editLicenseData editLicense_'+ key +'" title="Edit License Information" data-field="'+ key +'" data-websiteid="'+ id +'" onclick="Multisite.editLicense(cx.jQuery(this))"></a>';
                                tbody += '</td></tr>';
                            } else {
                                tbody += '<tr>';
                                tbody += '<td>' + key + '</td>';
                                tbody += '<td><span class="'+ key + '">' + data + '</span><a href="javascript:void(0);" class="editLicense editLicenseData editLicense_'+ key +'" title="Edit License Information" data-field="'+ key +'" data-value="'+ data +'" data-websiteid="'+ id +'" onclick="Multisite.editLicense(cx.jQuery(this))"></a></td>';
                                tbody += '</tr>';
                            }
                        });
                        var tfooter = '</table>';
                        html = theader + tbody + tfooter;
                    }
                    cx.tools.StatusMessage.showMessage(cx.variables.get('licenseInfo', "multisite/lang"), null, 3000);
                    cx.ui.dialog({
                        width: 820,
                        height: 400,
                        title: title,
                        content: html,
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
                    var resultCount = 0;

                    if (value.sqlResult) {
                        var cols = Object.keys(value.sqlResult).length;
                        $.each(value.sqlResult, function(key, data) {
                            console.log(resultCount);
                            tbody += "<tr class =row1>";
                            if (col_count == 0) {
                                thead += "<th colspan='2'>" + value.websiteName + "</th>";
                            }
                            if (col_count < cols) { 
                                tbody += "<td><div class='"+ data +"'>" + key + "</td>";
                                if (value.selectQueryResult) {
                                    var count = 0;
                                    var tsbody = "";
                                    var tshead = "";
                                    var no_cols = (value.selectQueryResult[resultCount]).length;

                                    $.each(value.selectQueryResult[resultCount], function(key, data) {
                                        tsbody += "<tr class =row1>";
                                        for (key in data) {
                                            if (count == 0) {
                                                tshead += "<th>";
                                                tshead += key;
                                                tshead += "</th>"
                                            }
                                            if (count < no_cols) {
                                                tsbody += "<td>";
                                                tsbody += data[key];
                                                tsbody += "</td>"
                                            }
                                        }
                                        count++;
                                        tsbody += "</tr>";
                                    });
                                    resultTable = theader + tshead + tsbody + "</table></br>";
                                }
                                tbody += "<td>" + resultTable + "</td>";
                            }
                            resultCount++;
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
        var fieldLabel = $this.attr('data-field');
        var title = $this.attr('title');
        var websiteId = $this.attr('data-websiteid');
        var licenseArray = ['licenseMessage', 'dashboardMessages', 'licenseGrayzoneMessages'];
        var liceneseTable = '';
        var uiDiv = '';
        var uiTab = '';
        var i = 1;
        if ($J.inArray(fieldLabel, licenseArray) !== -1) {
            var jsonString = $J('#ui_' + fieldLabel).html();
            $J.each(JSON.parse(jsonString), function(index, data) {
                if (typeof data === 'object') {
                    uiTab += '<li><a href="#tabs-' + i + '">' + data.lang_name + '</a></li>';
                    uiDiv += '<div id="tabs-' + i + '" data-langId ="' + data.lang_id + '" data-langName ="' + data.lang_name + '">';
                    uiDiv += '<table  cellspacing="0" cellpadding="3" border="0" class="adminlist licenceEdit" width="100%">';
                    uiDiv += '<tr><td><label>' + fieldLabel + '</label></td><td><textarea rows="4" cols="40" class = "' + fieldLabel + '" name ="' + fieldLabel + '">' + data.message + '</textarea></td></tr>';
                    uiDiv += '</table>';
                    uiDiv += '</div>';
                    i++;
                }
            });
            liceneseTable = '<div id="tab_menu" class="tab-' + fieldLabel + '"><ul>' + uiTab + '</ul>' + uiDiv + '</div>';
        } else {
            liceneseTable = '<table id="editLicense" cellspacing="0" cellpadding="3" border="0" class="adminlist licenceEdit" width="100%">';
            liceneseTable += '<tr><td><label>' + fieldLabel + '</label></td><td><textarea rows="4" cols="40" class = "' + fieldLabel + '" name ="' + fieldLabel + '">' + $this.attr('data-value') + '</textarea></td></tr>';
            liceneseTable += '</table>';
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
                    if ($J.inArray(fieldLabel, licenseArray) !== -1) {
                        var fieldValue = [];
                        var messageText = '';
                        var messageUiTab = [];
                        $J('#tab_menu.tab-' + fieldLabel + ' div').each(function() {
                            messageText += '<strong>' + $J(this).attr('data-langName') + ':</strong>&nbsp;';
                            messageText += ""+$J(this).find('textarea.' + fieldLabel).text() + '<br>';
                            messageUiTab.push({lang_id: $J(this).attr('data-langId'), lang_name: $J(this).attr('data-langName'), message: $J(this).find('textarea.' + fieldLabel).val()});
                            fieldValue[$J(this).attr('data-langId')] = {text: $J(this).find('textarea.' + fieldLabel).val()};
                        });
                    } else {
                        var fieldValue = $J('.licenceEdit').find('textarea.' + fieldLabel).val();
                    }
                    $J.ajax({
                        url: domainUrl,
                        type: "POST",
                        data: {licenseLabel: fieldLabel, licenseValue: fieldValue, websiteId: websiteId},
                        dataType: "json",
                        success: function(response) {
                            if (response.data.status == 'error') {
                                cx.tools.StatusMessage.showMessage(response.data.message, null, 4000);
                            }
                            if (response.status == 'success' && response.data.status == 'success') {
                                if ($J.isArray(fieldValue)) {
                                    $J('#'+fieldLabel).html(messageText);
                                    $J('#ui_' + fieldLabel).html(JSON.stringify(messageUiTab));
                                } else {
                                    $J('span.' + fieldLabel).text(fieldValue);
                                    $J('.editLicense_' + fieldLabel).attr('data-value', fieldValue);
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
    }
};