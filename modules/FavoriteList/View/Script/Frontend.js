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

    cx.favoriteListAddFavorite = function () {
        cx.ajax(
            'FavoriteList',
            'addFavorite',
            {
                data: {
                    title: title,
                    link: link,
                    description: description,
                    info: info,
                    image_1: image_1,
                    image_2: image_2,
                    image_3: image_3,
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
