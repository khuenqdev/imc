(function ($) {
    $(document).ready(function () {
        $searchInput = $('#search-input');

        $searchInput.on('keyup', _.debounce(function () {
            var search = $(this).val();
            var params = {
                'offset': 0,
                'limit': 1,
                'search': search,
                'only_with_location': 1,
                'only_location_from_exif': 1
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
        var editRoute = Routing.generate('gallery_edit', {'id': image.id}, false);
        var deleteRoute = Routing.generate('gallery_delete', {'id': image.id}, false);

        itemHtml += '<div class="col s4">' +
            '<div class="card hoverable sticky-action large">' +
            '<div class="card-image waves-effect waves-block waves-light">' +
            '<img src="' + getImcImage(image) + '" alt="' + image.alt + '" class="gallery-image activator" />\n' +
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
            '<div class="row"><div class="col s6"><b>Description</b></div><div class="col s6">' + image.description + '</div></div>' +
            '<div class="row"><div class="col s6"><b>Type</b></div><div class="col s6">' + image.type + '</div></div>' +
            '<div class="row"><div class="col s6"><b>Size</b></div><div class="col s6">' + image.width + 'x' + image.height + ' px</div></div>' +
            '<div class="row"><div class="col s6"><b>Address</b></div><div class="col s6">' + (image.address ? image.address : 'N/A') + '</div></div>' +
            '<div class="row"><div class="col s6"><b>Latitude</b></div><div class="col s6">' + (image.latitude ? parseFloat(image.latitude).toFixed(6) : 'N/A') + '</div></div>' +
            '<div class="row"><div class="col s6"><b>Longitude</b></div><div class="col s6">' + (image.latitude ? parseFloat(image.longitude).toFixed(6) : 'N/A') + '</div></div>' +
            '<div class="row"><div class="col s6"><b>URL</b></div><div class="col s6">' + image.src + '</div></div>' +
            '<div class="row"><div class="col s6"><b>Author</b></div><div class="col s6">' + (image.image_metadata.author ? image.image_metadata.author : 'N/A') + '</div></div>' +
            '</div>' +
            '</div>' +
            '</div>';
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
            only_with_location: 1,
            only_location_from_exif: 1
        },
        success: function (data) {
            if (data.length > 0) {
                var imageContent = buildImageContent(data);

                $('#image-container').html(imageContent);
                $('#modal').modal("open");
            }
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
        retrieveMarkers({
            only_location_from_exif: 1
        }, map);
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