cx.ready(function () {
    function favoriteListLoadSidebar() {
        cx.ajax(
            'FavoriteList',
            'getCatalog',
            {
                data: {},
                success: function (data) {
                    cx.jQuery('#favoriteListSidebar').empty();
                    cx.jQuery(data.data).appendTo('#favoriteListSidebar');
                }
            }
        );
    }

    function favoriteListAddFavorite() {
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
                    image_3: image_3
                },
                success: function () {
                    favoriteListLoadSidebar();
                }
            }
        );
    }

    function favoriteListRemoveFavorite(id) {
        cx.ajax(
            'FavoriteList',
            'removeFavorite',
            {
                data: {
                    id: id
                },
                success: function () {
                    favoriteListLoadSidebar();
                }
            }
        );
    }

    favoriteListLoadSidebar();
});
