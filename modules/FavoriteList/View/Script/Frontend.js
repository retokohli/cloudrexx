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
                    cx.favoriteListUpdateBlock(data.data);
                }
            }
        );
    };
    cx.favoriteListLoadBlock();

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
                    message: cx.jQuery(element).data('message'),
                    price: cx.jQuery(element).data('price'),
                    image_1: cx.jQuery(element).data('image1'),
                    image_2: cx.jQuery(element).data('image2'),
                    image_3: cx.jQuery(element).data('image3')
                },
                success: function (data) {
                    cx.favoriteListUpdateBlock(data.data);
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
                    cx.favoriteListUpdateBlock(data.data);
                }
            }
        );
    };

    cx.favoriteListEditFavoriteMessage = function (id, element) {
        cx.ajax(
            'FavoriteList',
            'editFavoriteMessage',
            {
                data: {
                    id: id,
                    message: cx.jQuery(element).closest('.favoriteListBlockListEntity').find('[name="favoriteListBlockListEntityMessage"]').val(),
                    themeId: cx.variables.get('themeId'),
                    lang: cx.variables.get('language')
                },
                success: function (data) {
                    cx.favoriteListUpdateBlock(data.data);
                }
            }
        );
    };

    cx.favoriteListUpdateBlock = function (data) {
        cx.jQuery('#favoriteListBlock').empty();
        cx.jQuery(data).appendTo('#favoriteListBlock');
    };
});
