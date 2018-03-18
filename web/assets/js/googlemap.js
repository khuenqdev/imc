var map;
var markers = [];
var imageContents = [];
var lastInfoWindow;

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

    google.maps.event.addListener(map, 'tilesloaded', function () {
        setMarkers(map);
    });
}

/**
 * Retrieve images from API
 *
 * @param minLat
 * @param maxLat
 * @param minLng
 * @param maxLng
 */
function retrieveImages(minLat, maxLat, minLng, maxLng) {
    jQuery.ajax({
        url: jQuery("#list-api-url").val(),
        dataType: 'json',
        data: {
            min_lat: minLat,
            max_lat: maxLat,
            min_lng: minLng,
            max_lng: maxLng
        },
        success: function (data) {
            buildImageContents(data);
            addMarkers(data);
        }
    });
}

function clearOldMarkers() {
    for (var i = 0; i < markers.length; i++) {
        markers[i].setMap(null);
    }

    markers = [];
}

function addMarkers() {
    clearOldMarkers();

    for (var coordinates in imageContents) {
        var latlng = coordinates.split('|');

        addMarker({
            lat: parseFloat(latlng[0]),
            lng: parseFloat(latlng[1])
        }, imageContents[coordinates]);
    }
}

function buildImageContents(data) {
    for (var i = 0; i < data.length; i++) {
        var image = data[i];
        var index = image.latitude + '|' + image.longitude;

        if (imageContents[index] !== undefined) {
            imageContents[index] += '<div class="content col s3">' +
                '<img src="' + image.src + '" alt="' + image.alt + '" class="map-image"/>' +
                '</div>';
        } else {
            imageContents[index] = '<div class="content col s3">' +
                '<img src="' + image.src + '" alt="' + image.alt + '" class="map-image"/>' +
                '</div>';
        }
    }
}

function addMarker(position, imageContent) {
    var marker = new google.maps.Marker({
        position: position,
        map: map
    });

    var infoWindow = new google.maps.InfoWindow({
        content: imageContent
    });

    marker.addListener('click', function() {
        if(lastInfoWindow !== undefined) {
            lastInfoWindow.close();
        }

        infoWindow.open(map, marker);
        lastInfoWindow = infoWindow;
    });

    markers.push(marker);
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

    retrieveImages(sw.lat(), nw.lat(), nw.lng(), ne.lng());
}