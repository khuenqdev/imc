var map;
var markers = [];
var lastInfoWindow;

/**
 * Initialize map data
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
            clearOldMarkers();
            addMarkersFromData(data);
        }
    });
}

function clearOldMarkers() {
    for (var i = 0; i < markers.length; i++) {
        markers[i].setMap(null);
    }

    markers = [];
}

function addMarkersFromData(data) {
    for (var i = 0; i < data.length; i++) {
        addMarker({
            lat: data[i].latitude,
            lng: data[i].longitude
        }, data[i]);
    }
}

function addMarker(position, image) {
    var marker = new google.maps.Marker({
        position: position,
        map: map,
        title: image.address
    });

    var imageContent = '<div class="content">' +
        '<img src="' + image.src + '" alt="' + image.alt + '" width="100px"/>' +
        '</div>';

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