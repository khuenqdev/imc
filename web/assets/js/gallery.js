(function ($) {

    $(document).ready(function () {
        $container = $('#gallery-container').infiniteScroll({
            path: function () {
                var page = this.loadCount + 1;

                var params = {
                    'page': page,
                    'limit': 12
                };

                var searchInput = $('#search-input');

                if (searchInput.val() !== "") {
                    params.search = searchInput.val();
                }

                return $('#gallery-container').data('source') + '?' + $.param(params);
            },
            responseType: 'text',
            history: false,
            status: '.page-load-status'
        });

        $container.on('load.infiniteScroll', function (event, response) {
            // parse response into JSON data
            var data = JSON.parse(response);
            var html = '';

            for (var i = 0; i < data.length; i++) {
                var location = data[i];
                html += '<div class="col s3">' +
                    '<div class="card small">' +
                    '<div class="card-image">' +
                    '<img src="' + location.src + '" class="gallery-image">\n' +
                    '<span class="card-title">' + location.address + '</span>' +
                    '</div>' +
                    '<div class="card-content">' +
                    '<p>' + location.description + '</p>' +
                    '</div>' +
                    '</div>' +
                    '</div>';
            }

            // convert HTML string into elements
            var $items = $(html);

            // append item elements
            $container.infiniteScroll('appendItems', $items);
        });

        $container.infiniteScroll('loadNextPage');
    });
})(jQuery);