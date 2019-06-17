cx.ready(function() {
    jQuery('.news_category_switch').bind('click', function() {
        cx.ajax(
            'News',
            'switchCategoryVisibility',
            {
                data: {
                    id: jQuery(this).data('id')
                },
                element: this,
                beforeSend: function() {
                    cx.ui.messages.showLoad();
                },
                success: function(data) {
                    cx.ui.messages.removeAll();
                    if (data.status != 'success') {
                        return;
                    }
                    if (data.data == "1") {
                        jQuery(this.element).removeClass('hidden');
                    } else {
                        jQuery(this.element).addClass('hidden');
                    }
                }
            }
        );
    });
});
