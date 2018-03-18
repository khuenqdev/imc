(function ($) {
    $(document).ready(function () {
        $searchInput = $('#search-input');

        $searchInput.on('keyup', _.debounce(function () {
            var search = $(this).val();
            var params = {
                'page': 1,
                'limit': 12,
                'search': search
            };

            var url = Routing.generate('list_images') + '?' + $.param(params);

            $.getJSON(url, function (data) {
                var html = '';

                for (var i = 0; i < data.length; i++) {
                    var image = data[i];
                    html += getImageItemHtml(image);
                }

                $('#gallery-container').html(html);
            });
        }, 300));

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

                return Routing.generate('list_images') + '?' + $.param(params);
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
                var image = data[i];
                html += getImageItemHtml(image);
            }

            // convert HTML string into elements
            var $items = $(html);

            // append item elements
            $container.infiniteScroll('appendItems', $items);
        });

        $container.infiniteScroll('loadNextPage');
    });

    /**
     * Build the image
     *
     * @param image
     * @returns {string}
     */
    function getImageItemHtml(image) {
        var editRoute = Routing.generate('gallery_edit', {'id': image.id}, false);
        var deleteRoute = Routing.generate('gallery_delete', {'id': image.id}, false);

        return '<div class="col s4">' +
            '<div class="card hoverable sticky-action medium">' +
            '<div class="card-image waves-effect waves-block waves-light">' +
            '<img src="' + image.src + '" alt="' + image.alt + '" class="gallery-image activator" />\n' +
            '<span class="card-title activator">' + (image.address ? image.address : '') + '</span>' +
            '<a class="btn-floating halfway-fab waves-effect waves-light light-blue btn-edit" href="' + editRoute + '">' +
            '<i class="material-icons">edit</i>' +
            '</a>' +
            '<a class="btn-floating halfway-fab waves-effect waves-light red btn-delete" href="' + deleteRoute + '">' +
            '<i class="material-icons">delete</i>' +
            '</a>' +
            '</div>' +
            '<div class="card-content">' +
            '<p><b>' + image.alt + '</b><i class="card-title activator material-icons right">more_vert</i></p>' +
            '<p class="truncate">' + image.description + '</p>' +
            '</div>' +
            '<div class="card-reveal">' +
            '<span class="row card-title activator">Image details<i class="material-icons right">close</i></span>' +
            '<div class="row"><div class="col s6"><b>Type</b></div><div class="col s6">' + image.type + '</div></div>' +
            '<div class="row"><div class="col s6"><b>Size</b></div><div class="col s6">' + image.width + 'x' + image.height + ' px</div></div>' +
            '<div class="row"><div class="col s6"><b>Address</b></div><div class="col s6">' + (image.address ? image.address : 'N/A') + '</div></div>' +
            '<div class="row"><div class="col s6"><b>Latitude</b></div><div class="col s6">' + (image.latitude ? parseFloat(image.latitude).toFixed(6) : 'N/A') + '</div></div>' +
            '<div class="row"><div class="col s6"><b>Longitude</b></div><div class="col s6">' + (image.latitude ? parseFloat(image.longitude).toFixed(6) : 'N/A') + '</div></div>' +
            '<div class="row"><div class="col s6"><b>Location from metadata?</b></div><div class="col s6">' + (image.is_exif_location ? 'Yes' : 'No') + '</div></div>' +
            '<div class="row"><div class="col s6"><b>Description</b></div><div class="col s6">' + image.description + '</div></div>' +
            '</div>' +
            '</div>' +
            '</div>';
    }
})(jQuery);