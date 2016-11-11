cx.ready(function () {
    cx.favoriteListLoadBlock = function () {
        cx.ajax(
            'FavoriteList',
            'getCatalog',
            {
                data: {
                    themeId: cx.variables.get('themeId'),
                    lang: cx.variables.get('language')
                },
                success: function (data) {
                    cx.jQuery('#favoriteListBlock').empty();
                    cx.jQuery(data.data).appendTo('#favoriteListBlock');
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
                    themeId: cx.variables.get('themeId'),
                    lang: cx.variables.get('language'),
                    title: cx.jQuery(element).data('title'),
                    link: cx.jQuery(element).data('link'),
                    description: cx.jQuery(element).data('description'),
                    info: cx.jQuery(element).data('info'),
                    image_1: cx.jQuery(element).data('image1'),
                    image_2: cx.jQuery(element).data('image2'),
                    image_3: cx.jQuery(element).data('image3')
                },
                success: function (data) {
                    cx.jQuery('#favoriteListBlock').empty();
                    cx.jQuery(data.data).appendTo('#favoriteListBlock');
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
                    themeId: cx.variables.get('themeId'),
                    lang: cx.variables.get('language')
                },
                success: function (data) {
                    cx.jQuery('#favoriteListBlock').empty();
                    cx.jQuery(data.data).appendTo('#favoriteListBlock');
                }
            }
        );
    };

    cx.favoriteListLoadBlock();
});
