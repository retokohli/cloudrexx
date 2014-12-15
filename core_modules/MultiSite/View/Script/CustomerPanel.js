var websiteLoginUrl;
cx.ready(function() {
    websiteLoginUrl = cx.variables.get('cadminPath', 'contrexx') + 'index.php&cmd=JsonData&object=MultiSite&act=websiteLogin';    
});

function getQueryParams(qs) {
    qs = qs.split("+").join(" ");
    var params = {},
        tokens,
        re = /[?&]?([^=]+)=([^&]*)/g;

    while (tokens = re.exec(qs)) {
        params[decodeURIComponent(tokens[1])] = decodeURIComponent(tokens[2]);
    }

    return params;
}
    
function  loadContent(jQuerySelector, url) {
    jQuery.ajax({
        dataType: 'html',
        url: url,
        type: 'GET',
        success: function(data) {
            if (data) {
                jQuery(jQuerySelector).html(data);
            }
        },
        fail: function(data) {}
    });
}

/**
 * Generate Remote website-login token
 * depends bootstrap js
 * 
 * @param object elm jQuery button object
 */
function getRemoteLoginToken($this) {
    var websiteId = $this.data('id');
    
    jQuery.ajax({
        dataType: "json",
        url: websiteLoginUrl,
        data: {
            websiteId :  websiteId
        },
        type: "POST",
        beforeSend: function (xhr, settings) {
            $this.button('loading');
            $this.prop('disabled', true);
        },
        success: function(response) {
            if (response.status == 'success') {
                resp = response.data;
                if (resp.status == 'success') {
                    var newWindow = window.open(resp.webSiteLoginUrl, '_blank');
                    if(newWindow) {
                        //Browser has allowed it to be opened
                        newWindow.focus();
                    }
                } else {
                    alert(resp.message);
                }
            } else {
                alert(response.message);
            }
        },
        complete: function (xhr, settings) {
            $this.button('reset');
            $this.prop('disabled', false);
        },
        error: function() { }
    });
}