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

        initMap();

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
        maxLng = Math.abs(maxLng);
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
    // for (var i = 0; i < data.length; i++) {
    //     addMarker({
    //         lat: parseFloat(data[i].latitude),
    //         lng: parseFloat(data[i].longitude)
    //     });
    // }

    // var markerCluster = new MarkerClusterer(map, markers, {
    //     imagePath: markerClusterImagePath,
    //     gridSize: 80,
    //     imageExtension: 'gif'
    // });

    var options = {};
    options.clusteringMethod = "gridBased";
    options.serverClient = "client"; // “client”, “server”
    options.markerStyle = "thumbnail"; // “thumbnail”, “marker1”
    options.markerColor = "yellow"; // “yellow”, “green”, “red”, “blue”
    options.representativeType = "mean"; // “mean”, “first”, “middleCell”
    options.markerSingleWidth = 48; // width of single marker on map
    options.markerClusterWidth = 48; // width of cluster marker on map
    options.markerSingleHeight = 39; // height of single marker on map
    options.markerClusterHeight = 39; // height of cluster marker on map

    var clusteringObj = new mopsiMarkerClustering(map, options);

    if (clusteringObj.validParams === "YES") { // validParams: “YES” or “NO”
        // add data objects
        // supposing your data is in the array data
        for (var j = 0; j < data.length; j++) {
            // creating objects one by one and adding to clusteringObj using the function: addObject
            obj = {};
            obj.lat = parseFloat(data[j].latitude);
            obj.lon = parseFloat(data[j].longitude);

            // optional
            obj.name = 'marker_' + j;
            obj.thumburl = data[j].thumbnail;
            obj.photourl = data[j].photourl;
            obj.color = "yellow";

            clusteringObj.addObject(obj); // adds object to the array markersData of clusteringObj
        }

        clusteringObj.apply(); // performing clustering algorithm and displaying markers
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
                onCycleTo: function (el, dragged) {
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
        zoom: 4
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