cx.ready(function () {
    cx.favoriteListLoadSidebar = function () {
        cx.ajax(
            'FavoriteList',
            'getCatalog',
            {
                data: {
                    lang: cx.jQuery('#favoriteListSidebar').data('lang')
                },
                success: function (data) {
                    cx.jQuery('#favoriteListSidebar').empty();
                    cx.jQuery(data.data).appendTo('#favoriteListSidebar');
                }
            }
        );
    };

    cx.favoriteListAddFavorite = function (element) {
        cx.ajax(
            'FavoriteList',
            'addFavorite',
            {
                data: {
                    title: cx.jQuery(element).data('title'),
                    link: cx.jQuery(element).data('link'),
                    description: cx.jQuery(element).data('description'),
                    info: cx.jQuery(element).data('info'),
                    image_1: cx.jQuery(element).data('image1'),
                    image_2: cx.jQuery(element).data('image2'),
                    image_3: cx.jQuery(element).data('image3'),
                    lang: cx.jQuery('#favoriteListSidebar').data('lang')
                },
                success: function (data) {
                    cx.jQuery('#favoriteListSidebar').empty();
                    cx.jQuery(data.data).appendTo('#favoriteListSidebar');
                }
            }
        );
    };

    cx.favoriteListRemoveFavorite = function (id) {
        cx.ajax(
            'FavoriteList',
            'removeFavorite',
            {
                data: {
                    id: id,
                    lang: cx.jQuery('#favoriteListSidebar').data('lang')
                },
                success: function (data) {
                    cx.jQuery('#favoriteListSidebar').empty();
                    cx.jQuery(data.data).appendTo('#favoriteListSidebar');
                }
            }
        );
    };

    cx.favoriteListLoadSidebar();
});
