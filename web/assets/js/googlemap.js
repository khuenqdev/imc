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

function retrieveMarkers(params, map) {
    $.ajax({
        url: Routing.generate('get_markers', {}, false),
        dataType: 'json',
        data: (params ? params : {}),
        success: function (data) {
            addMarkers(data, map);
        }
    });
}

function addMarkers(data, map) {
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
            var obj = {};
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

        for(var i = 0; i < clusteringObj.markers.length; i++) {
            var marker = clusteringObj.markers[i].marker;

            marker.addListener('click', function () {
                openImageWindow({
                    lat: this.lat,
                    lon: this.lon
                });
            });
        }
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
    var map = createMap(62.60, 29.76, "map", 12);

    var mapReadyListener = google.maps.event.addListenerOnce(map, 'idle', _.debounce(function () {
        retrieveMarkers({}, map);
        google.maps.event.removeListener(mapReadyListener);
    }, 100));

    var mapHeight =  Math.round(jQuery(window).innerHeight() * 0.75);
    jQuery('.googlemap').css({ height: mapHeight });

    jQuery(window).resize(function(){
        jQuery('.googlemap').css({ height: mapHeight });
    });
}

function createMap(lat, lon, canvasId, zoomLevel) {
    var map, defaultOptions, mapTypeIds, type;
    mapTypeIds = [];

    for (type in google.maps.MapTypeId) {
        mapTypeIds.push(google.maps.MapTypeId[type]);
    }

    defaultOptions = {
        zoom: zoomLevel,
        center: new google.maps.LatLng(lat, lon),
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        mapTypeControlOptions: {
            mapTypeIds: mapTypeIds
        },
        rotateControl: false,
        streetViewControl: false,
        panControl: false
    };

    map = new google.maps.Map(document.getElementById(canvasId), defaultOptions);

    return map;
}