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
                success: function(data) {
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
