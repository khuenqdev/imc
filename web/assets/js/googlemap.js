var map;
var markers = [];

/**
 * Initialize map for image edit page
 */
function initEditMap() {
    var $map = jQuery("#map");
    var position = {
        lat: parseFloat($map.data('lat')),
        lng: parseFloat($map.data('lng'))
    };

    map = new google.maps.Map(document.getElementById('map'), {
        center: position,
        zoom: 14
    });

    var marker = new google.maps.Marker({
        position: position,
        map: map
    });
}

/**
 * Initialize map data (for image location/homepage)
 */
function initMap() {
    map = new google.maps.Map(document.getElementById('map'), {
        center: {lat: 65.397, lng: -20.644},
        zoom: 6
    });

    google.maps.event.addListener(map, 'idle', _.debounce(function () {
        clearOldMarkers();
        setMarkers(map);
    }, 100));
}

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

    retrieveMarkers(sw.lat(), nw.lat(), nw.lng(), ne.lng());
}

function retrieveMarkers(minLat, maxLat, minLng, maxLng) {
    jQuery.ajax({
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
    jQuery.ajax({
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

            jQuery('#image-container').html('<div class="carousel">'
                + imageContent
                + '</div>'
            );

            jQuery('#modal').modal("open");

            jQuery('.carousel').carousel({
                onCycleTo: function(el, dragged) {
                    getImageInfoHtml(el.find('img').data('id'));
                }
            });

            getImageInfoHtml(data[0].id);
        }
    });
}

function getImageInfoHtml(imageId) {
    jQuery.ajax({
        url: Routing.generate('gallery_view', {id: imageId}, false),
        dataType: 'html',
        success: function (data) {
            jQuery('#image-info').html(data);
        }
    });
}