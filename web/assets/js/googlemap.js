var map;

function initMap() {
    map = new google.maps.Map(document.getElementById('map'), {
        center: {lat: -34.397, lng: 150.644},
        zoom: 8
    });

    var iconBase = 'https://maps.google.com/mapfiles/kml/shapes/';

    google.maps.event.addListener(map, 'bounds_changed', function() {
        var bounds = map.getBounds();
        var ne = bounds.getNorthEast();
        var sw = bounds.getSouthWest();
        var nw = new google.maps.LatLng(ne.lat(), sw.lng());
        var se = new google.maps.LatLng(sw.lat(), ne.lng());

        retrieveImages(nw.lat(), sw.lat(), nw.lng(), ne.lng());
    });
    //console.log(bounds);
}

function retrieveImages(minLat, maxLat, minLng, maxLng) {
    //console.log(minLat, maxLat, minLng, maxLng);
    jQuery.ajax({
        url: jQuery("#list-api-url").val(),
        dataType: 'json',
        data: {
            min_lat: minLat,
            max_lat: maxLat,
            min_lng: minLng,
            max_lng: maxLng
        },
        done: function(data) {
            console.log(data);
        }
    });
}