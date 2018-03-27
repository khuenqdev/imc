(function ($) {
    $(document).ready(function () {
        $searchInput = $('#search-input');

        $searchInput.on('keyup', _.debounce(function () {
            var search = $(this).val();
            var params = {
                'offset': 0,
                'limit': 1,
                'search': search,
                'only_with_location': 1
            };

            var url = Routing.generate('list_images') + '?' + $.param(params);

            $.getJSON(url, function (data) {
                var image = data[0];

                if (typeof image !== 'undefined') {
                    map.setCenter({
                        lat: image.latitude,
                        lng: image.longitude
                    });

                    map.setZoom(4);
                }
            });
        }, 300));

        $('.modal').modal({
            dismissible: true, // Modal can be dismissed by clicking outside of the modal
            inDuration: 300, // Transition in duration
            outDuration: 200, // Transition out duration
            startingTop: '2%', // Starting top style attribute
            endingTop: '2%' // Ending top style attribute
        });

        Materialize.toast('Click on the map markers to reveal images', 4000, 'rounded');
    });

    
})(jQuery);

var map;
var markers = [];

/**
 * Set map markers
 *
 * @param map
 */
function setMarkers(map) {
    var bounds = map.getBounds();
    var ne = bounds.getNorthEast();
    var sw = bounds.getSouthWest();
    var nw = new google.maps.LatLng(ne.lat(), sw.lng());
    // var se = new google.maps.LatLng(sw.lat(), ne.lng());

    var minLat = sw.lat();
    var maxLat = nw.lat();
    var minLng = nw.lng();
    var maxLng = ne.lng();

    if (minLng > maxLng) {
        var tmp = minLng;
        minLng = maxLng;
        maxLng = tmp;
    }

    retrieveMarkers(minLat, maxLat, minLng, maxLng);
}

function retrieveMarkers(minLat, maxLat, minLng, maxLng) {
    $.ajax({
        url: Routing.generate('get_markers', {}, false),
        dataType: 'json',
        data: {
            min_lat: minLat,
            max_lat: maxLat,
            min_lng: minLng,
            max_lng: maxLng
        },
        success: function (data) {
            addMarkers(data);
        }
    });
}

function addMarkers(data) {
    for (var i = 0; i < data.length; i++) {
        addMarker({
            lat: parseFloat(data[i].latitude),
            lng: parseFloat(data[i].longitude)
        });
    }
}

function addMarker(position) {
    var marker = new google.maps.Marker({
        position: position,
        map: map
    });

    marker.addListener('click', function () {
        openImageWindow(position);
    });

    markers.push(marker);
}

function clearOldMarkers() {
    for (var i = 0; i < markers.length; i++) {
        markers[i].setMap(null);
    }

    markers = [];
}

function buildImageContent(data) {
    var itemHtml = "";

    for (var i = 0; i < data.length; i++) {
        var image = data[i];

        itemHtml += '<a class="carousel-item" href="#image' + i + '!">' +
            '<img src="' + getImcImage(image) + '" alt="' + image.alt + '" class="map-image" data-id="' + image.id + '"/>' +
            '</a>';
    }

    return itemHtml;
}

function openImageWindow(position) {
    $.ajax({
        url: Routing.generate('list_images', {}, false),
        dataType: 'json',
        data: {
            min_lat: position.lat,
            max_lat: position.lat,
            min_lng: position.lng,
            max_lng: position.lng,
            only_with_location: 1
        },
        success: function (data) {
            var imageContent = buildImageContent(data);

            $('#image-container').html('<div class="carousel">'
                + imageContent
                + '</div>'
            );

            $('#modal').modal("open");

            $('.carousel').carousel({
                onCycleTo: function(el, dragged) {
                    getImageInfoHtml(el.find('img').data('id'));
                }
            });

            getImageInfoHtml(data[0].id);
        }
    });
}

function getImageInfoHtml(imageId) {
    $.ajax({
        url: Routing.generate('gallery_view', {id: imageId}, false),
        dataType: 'html',
        success: function (data) {
            $('#image-info').html(data);
        }
    });
}

/**
 * Initialize map data (for image location/homepage)
 */
function initMap() {
    map = new google.maps.Map(document.getElementById('map'), {
        center: {lat: 0.000000, lng: 0.000000},
        zoom: 3
    });

    google.maps.event.addListener(map, 'idle', _.debounce(function () {
        clearOldMarkers();
        setMarkers(map);
    }, 100));

    // google.maps.event.addListener(map, "bounds_changed", function() {
    //     var bounds = map.getBounds();
    //     var ne = bounds.getNorthEast();
    //     var sw = bounds.getSouthWest();
    //     var nw = new google.maps.LatLng(ne.lat(), sw.lng());
    //
    //     var minLat = sw.lat();
    //     var maxLat = nw.lat();
    //     var minLng = nw.lng();
    //     var maxLng = ne.lng();
    //
    //     console.log("Bounding box: {", minLat, maxLat, minLng, maxLng, '}');
    // });
}