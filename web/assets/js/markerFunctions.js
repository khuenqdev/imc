function createMarker(mapEx, jsonString) {
    var mopsiMarker, map;

    var jsonObject = eval("(" + jsonString + ")");
    map = mapEx.map;

    mopsiMarker = new markerX(mapEx, jsonObject);
    mopsiMarker.clusterSize = jsonObject.clusterSize;
    mopsiMarker.myLat = jsonObject.latitude;
    mopsiMarker.myLng = jsonObject.longitude;

    google.maps.event.addListener(mopsiMarker.marker, 'click', function (e) {
        mopsiMarker.clickMarkerOnMap();
    });

    google.maps.event.addListener(mopsiMarker.marker, 'dragstart', function () {
    });

    mapEx.addToOverlays(mopsiMarker);

    return mopsiMarker;
}

function markerX(mapEx, jsonObject) {
    var icon, map;
    map = mapEx.map;

    if (jsonObject.zIndex == undefined || jsonObject.zIndex == null)
        jsonObject.zIndex = 8;

    jsonObject.zIndex = Number(jsonObject.zIndex);

    if (jsonObject.icon == undefined || jsonObject.icon == null)
        icon = getIconForMarkerParamType(jsonObject);
    else
        icon = jsonObject.icon;

    this.marker = new google.maps.Marker({
        position: new google.maps.LatLng(jsonObject.latitude, jsonObject.longitude),
        map: map,
        draggable: jsonObject.draggable == "true" ? true : false,
        title: jsonObject.title,
        optimized: false,
        shadow: getShadowForMarkerParamType(jsonObject.style, jsonObject.clusterSize),
        icon: icon,
        raiseOnDrag: (jsonObject.raiseOnDrag == "false") ? false : true,
        zIndex: 8,
        clickable: jsonObject.clickable,
        destination: jsonObject.destination
    });

    this.marker.addListener('click', function () {
        openImageWindow({
            lat: jsonObject.latitude,
            lng: jsonObject.longitude
        });
    });

    this.mapEx = mapEx;

    this.clusterSize = jsonObject.clusterSize;

    this.draggable = jsonObject.draggable == "true" ? true : false;
    this.thumb = jsonObject.thumb;
    this.map = map;

    this.markerStyle = jsonObject.style;

    this.id = jsonObject.id;

    this.width = jsonObject.width;
    this.height = jsonObject.height;

    //
    if (jsonObject.html != undefined)
        this.labelInfo = jsonObject.html;
    else
        this.labelInfo = jsonObject.label;

    this.type = jsonObject.type;

    if (this.clusterSize > 1)
        this.addLabel(jsonObject.style, jsonObject.type, jsonObject.color, jsonObject.thumb);
}

function getIconForMarkerParamType(jsonObject) {
    var image, size, origin, anchor, iconUrl, scaledSize;

    image = "";
    if (jsonObject.style == "thumbnail") {
        iconUrl = jsonObject.thumb;

        if (!(fileExists(iconUrl))) {
            iconUrl = clusteringAssetPath + "thumb-nophoto.jpg";
        }

        size = new google.maps.Size(jsonObject.width, jsonObject.height);
        origin = new google.maps.Point(0, 0);
        anchor = new google.maps.Point(jsonObject.width / 2, jsonObject.height);
        scaledSize = new google.maps.Size(jsonObject.width, jsonObject.height);
    }

    if (jsonObject.style == "marker1") {
        if (jsonObject.clusterSize > 1) {
            iconUrl = clusteringAssetPath + "marker1_cluster_" + jsonObject.color + ".gif";
            size = new google.maps.Size(jsonObject.width, jsonObject.height);
            origin = new google.maps.Point(0, 0);
            anchor = new google.maps.Point(jsonObject.width / 2, jsonObject.height / 2);
            scaledSize = new google.maps.Size(jsonObject.width, jsonObject.height);
        }
        else {
            iconUrl = clusteringAssetPath + "marker1_single_" + jsonObject.color + ".gif";
            size = new google.maps.Size(jsonObject.width, jsonObject.height);
            origin = new google.maps.Point(0, 0);
            anchor = new google.maps.Point(jsonObject.width / 2, jsonObject.height);
            scaledSize = new google.maps.Size(jsonObject.width, jsonObject.height);
        }
    }

    var image = {
        url: iconUrl,
        size: size,
        origin: origin,
        anchor: anchor,
        scaledSize: scaledSize
    };

    return image;
}

function getShadowForMarkerParamType(markerStyle, type, clusterSize) {
    var shadow = "";

    if (markerStyle == "thumbnail") {
        if (clusterSize > 1)
            shadow = new google.maps.MarkerImage(clusteringAssetPath + 'photoCollectionShadow.gif', null, new google.maps.Point(0, 0), new google.maps.Point(35, 57));
        else
            shadow = new google.maps.MarkerImage(clusteringAssetPath + 'photoShadow.gif', null, new google.maps.Point(0, 0), new google.maps.Point(35, 49));
    }

    if (markerStyle == "marker1") {
        if (clusterSize > 1)
            shadow = "";
        else
            shadow = "";
    }


    return shadow;
}

function getFilenameFromWholePath(url) {
    if (url == undefined || url == "")
        return url;

    var filename = url.replace(/^.*[\\\/]/, '');

    return filename;
}

//methods
markerX.prototype.setPosition = function (latlng) {
    this.marker.setPosition(latlng);
}
markerX.prototype.getPosition = function () {
    return this.marker.position;
}
markerX.prototype.getDestination = function () {
    return this.marker.destination;
}
markerX.prototype.setDestination = function (latlng) {
    this.marker.destination = latlng;
}
markerX.prototype.setIcon = function (icon) {
    this.marker.setOptions({icon: icon});
}
markerX.prototype.setMap = function (map) {
    this.marker.setMap(map);
    try {
        this.label.setMap(map);
    } catch (err) {
    }
}

markerX.prototype.setDraggable = function (draggable) {
    this.marker.setDraggable(draggable);
}
markerX.prototype.getDraggable = function () {
    return this.marker.getDraggable();
}
markerX.prototype.setTitle = function (title) {
    this.marker.setTitle(title);
}
markerX.prototype.getTitle = function () {
    return this.marker.getTitle();
}

markerX.prototype.getThumb = function () {
    var iconUrl, image, size, origin, anchor, scaledSize;

    if (this.markersClusteringObj.options.serverClient == "client")
        iconUrl = this.markersClusteringObj.markersData[this.mapEx.infoWindow.index].thumburl;
    else
        iconUrl = this.markersClusteringObj.markerClusters.clusters[this.group].thumburl;

    if (!(fileExists(iconUrl)))
        iconUrl = clusteringAssetPath + "thumb-nophoto.jpg";

    size = new google.maps.Size(this.width, this.height);
    origin = new google.maps.Point(0, 0);
    anchor = new google.maps.Point(this.width / 2, this.height);
    scaledSize = new google.maps.Size(this.width, this.height);

    image = {
        url: iconUrl,
        size: size,
        origin: origin,
        anchor: anchor,
        scaledSize: scaledSize
    };

    return image;
}
markerX.prototype.getType = function () {
    return this.type;
}

//listener support
markerX.prototype.addListener = function (event, callbackFunction) {
    var marker = this.marker;
    var mopsiMarker = this;
    google.maps.event.addListener(this.marker, event, function () {
        callbackFunction(mopsiMarker)
    });
}
markerX.prototype.removeListener = function (type) {
    google.maps.event.clearListeners(this.marker, type);
}

// click on marker on the map
markerX.prototype.clickMarkerOnMap = function () {
    var i, j, n, dist, p, q, type, markersData, cluster;

    var myMarker = this;
    var flagZoomToCluster = 0;

    if (myMarker.markersClusteringObj.options.serverClient == "server") {
        i = myMarker.group;
        cluster = myMarker.markersClusteringObj.markerClusters.clusters[i];

        if (cluster.clusterSize > 1 && ((cluster.latMax - cluster.latMin) > 0.01 ||
                (cluster.lonMax - cluster.lonMin) > 0.01)) {
            myMarker.markersClusteringObj.nonSpatial = false; // spatial query
            myMarker.markersClusteringObj.dataBounds.northWest = new google.maps.LatLng(cluster.latMax, cluster.lonMin);
            myMarker.markersClusteringObj.dataBounds.southEast = new google.maps.LatLng(cluster.latMin, cluster.lonMax);
            // alert("x " + cluster.latMin+" "+cluster.latMax+" "+cluster.lonMin+" " +cluster.lonMax);
            myMarker.zoomToCluster();
//       myMarker.markersClusteringObj.apply();
        }
        else {
            if (typeof openOrUpdateInfoWindow == 'function') {
                openOrUpdateInfoWindow(this); // this function is expecte to be in the main page that uses the API
            }
            // myMarker.openOrUpdateInfoWindow(); // ?? if several markers at almost same location, does is iterate through the results?
        }
    } else {
        markersData = this.markersClusteringObj.markersData;
        if (myMarker.clusterIndexes == undefined || myMarker.clusterIndexes == null)
            n = 1;
        else
            n = myMarker.clusterIndexes.length;

        if (n > 1)
            type = "cluster";
        else
            type = "single";

        // Are objects in a cluster very close?
        if (type == "cluster") {
            if (n < 50 && n > 1) { // this check is not necessary for big clusters
                for (i = 0; i < n && !flagZoomToCluster; i++) {
                    p = myMarker.clusterIndexes[i];
                    for (j = i + 1; j < n && !flagZoomToCluster; j++) {
                        q = myMarker.clusterIndexes[j];
                        dist = this.mapEx.hvs(markersData[p].lat, markersData[q].lat, markersData[p].lon, markersData[q].lon);
                        if (dist > 20) // in meter
                            flagZoomToCluster = 1;
                    }
                }
            }
            else
                flagZoomToCluster = 1;
        }

        if (flagZoomToCluster)
            myMarker.zoomToCluster();
        else {
            if (typeof openOrUpdateInfoWindow == 'function') {
                openOrUpdateInfoWindow(this); // this function is expecte to be in the main page that uses the API
            }
            // myMarker.openOrUpdateInfoWindow();
        }
    }
}

// click marker or cluster on the map
markerX.prototype.openOrUpdateInfoWindow = function () {
    var check = false;
    if (this.mapEx.infoWindow.isOpen) {
        if (this.markersClusteringObj.options.serverClient == "client") {
            if (this.clusterIndexes.indexOf(this.mapEx.infoWindow.index) > -1) {
                check = true;
            }
        } else {
            if (this.group == this.mapEx.infoWindow.group)
                check = true;
        }
    }

    if (check) {
        this.marker.selectedIndex += 1;
        this.updateInfoWindow();
    }
    else
        this.openInfoWindow();
}

// open infowindow with this.marker.selectedIndex 
markerX.prototype.openInfoWindow = function () {
    var i, selectedIndex, doOpen;
    doOpen = true;

    if (this.mapEx.infoWindow.isOpen) {
        if (this.markersClusteringObj.options.serverClient == "client") {
            if (this.clusterIndexes.indexOf(this.mapEx.infoWindow.index) > -1)
                doOpen = false;
        }
        else {
            if (this.group == this.mapEx.infoWindow.group)
                doOpen = false;
        }
    }

    if (doOpen) {
        this.mapEx.closeInfoWindow();
        this.marker.selectedIndex = -1;
    }

    this.updateInfoWindow();

    if (doOpen) {
        this.mapEx.infoWindow.open(this.map, this.marker);
        this.mapEx.infoWindow.isOpen = true;
    }

}

//infoWindow 
markerX.prototype.updateInfoWindow = function () {
    var N, iconUrl;

    if (this.markersClusteringObj.options.serverClient == "client")
        N = this.clusterIndexes.length;
    else
        N = this.marker.len;

    if (this.marker.selectedIndex >= N || this.marker.selectedIndex < 0)
        this.marker.selectedIndex = 0;

    // index for this.mapEx.infoWindow is considered among all data in markersData
    if (this.markersClusteringObj.options.serverClient == "client")
        this.mapEx.infoWindow.index = this.clusterIndexes[this.marker.selectedIndex];
    else
        this.mapEx.infoWindow.group = this.group;

    this.mapEx.infoWindow.anchor = this.marker;
    //this.mapEx.infoWindow.setContent(this.createInfoWindow());

    // update marker icon
    if (this.markerStyle == "thumbnail") {
        iconUrl = this.getThumb();
        this.setIcon(iconUrl);
    }
}

markerX.prototype.setContentInfoWindow = function (html) {
    this.mapEx.infoWindow.setContent(html);
}

markerX.prototype.zoomToCluster = function () {
    var zoomlevel_before, zoomlevel_after, bounds;
    zoomlevel_before = this.mapEx.getZoom();

    if (this.mapEx.myZoomListener != null) {
        google.maps.event.removeListener(this.mapEx.myZoomListener);
        this.mapEx.myZoomListener = null;
    }
    if (this.mapEx.myDragListener != null) {
        google.maps.event.removeListener(this.mapEx.myDragListener);
        this.mapEx.myDragListener = null;
    }

    if (this.markersClusteringObj.options.serverClient == "server") {
        bounds = new google.maps.LatLngBounds(this.markersClusteringObj.dataBounds.northWest, this.markersClusteringObj.dataBounds.southEast);
        this.mapEx.setBounds(bounds);
    } else {// client
        this.mapEx.setBoundsFromIndexes(this.markersClusteringObj.markersData, this.clusterIndexes);
    }

    zoomlevel_after = this.mapEx.getZoom();
    // alert(zoomlevel_before+" "+zoomlevel_after);
    if (zoomlevel_after <= zoomlevel_before) {
        zoomlevel_after = zoomlevel_before + 1; // forcing one level zoom in
        this.mapEx.setZoom(zoomlevel_after);
    }

    this.markersClusteringObj.apply("zoom_pan");

}

markerX.prototype.addLabel = function (markerStyle, type, color, thumb) {
    var map = this.map;
    this.thumb = thumb;

    if (markerStyle == "marker3")
        return;

    if (this.labelInfo != undefined || type == CLUSTER) {
        this.label = new Label({
            map: map,
            type: this.type,
            color: color,
            clusterSize: this.clusterSize,
            thumb: this.thumb,
            markerStyle: markerStyle,
            marker: this
        });

        this.label.set('zIndex', 10);
        this.label.bindTo('position', this.marker, 'position');
        if (this.labelInfo == undefined) {
            this.labelInfo = " ";
        }
        this.label.set('text', this.labelInfo);
    }
}

markerX.prototype.createInfoWindow = function () {
    var content, photourl, info, filename;

    info = this.getSelectedObjectInfo(); // info of selected object in cluster
    info.location = formatLat(info.lat, 2) + "," + formatLon(info.lon, 2);

    photourl = info.photourl;
    if (!(fileExists(photourl)))
        photourl = clusteringAssetPath + "nophoto.jpg";

    content = '<div id="infoWindowContentMain" class="infoWindowContentMain" >';
    content += '<div id="infoWindowTitle" class="infoWindowTitle" ';
    if (this.markerStyle == "marker1")
        content += ' style="border-bottom:2px solid black;margin-bottom:10px;" ';

    content += '>' + info.title + '</div>';

    if (this.markerStyle == "thumbnail")
        content += '<div id="photoInfoWindow" class="photoInfoWindow"><img class="bigThumbnail1" src="' + photourl + '" /></div>';

    content += '<div class="infoWindowDetailInfo" id="infoWindowDetailInfo">';
    content += '<div>' + info.location + '</div>';

    content += '</div>'; // infoWindowDetailInfo
    content += '<div>'; // infoWindowContentMain

    return content;
}

markerX.prototype.getSelectedObjectInfo = function () {
    var selectedIndex, info, markersData, indexes, j, group;

    info = {};
    selectedIndex = this.marker.selectedIndex; // if a cluster has 5 objects, selectedIndex is a number between 0 to 4

    if (this.markersClusteringObj.options.serverClient == "client") {
        markersData = this.markersClusteringObj.markersData;
        indexes = this.clusterIndexes;

        j = indexes[selectedIndex]; // real index in markersData containing whole data
        info.photourl = markersData[j].photourl;
        info.thumburl = markersData[j].thumburl;
        info.title = markersData[j].name;
        info.lat = markersData[j].lat;
        info.lon = markersData[j].lon;
    }
    else {
        group = this.group;
        photoInfo = this.getPhotoInfoFromCluster(group, selectedIndex);
        this.markersClusteringObj.markerClusters.clusters[this.group].thumburl = photoInfo.thumburl;
        info.photourl = photoInfo.photourl;
        info.thumburl = photoInfo.thumburl;
        info.title = photoInfo.name;
        info.lat = photoInfo.lat;
        info.lon = photoInfo.lon;
    }

    if (info.title == "")
        info.title = "Untitled";

    return info;
}

// info of j-th object in clutser i
markerX.prototype.getPhotoInfoFromCluster = function (i, j) {
    var k, clusterSize, selected, clusters;
    var latMin, latMax, lonMin, lonMax, photoInfo, mc;

    photoInfo = null;
    mc = this.markersClusteringObj.markerClusters;

    mc.selClusterIW = 0; // selected cluster among children of a displayed cluster for getting photo info
    mc.selItemIW = 0; // object number in a cluster
    mc.contSearch = 1; // continue search in children of a cluster until find the target cell
    mc.n1 = 0; // the number of objects in previously searched clusters

    clusterSize = mc.clusters[i].clusterSize;
    if (j >= clusterSize) {
        alert("The requested object exceeds cluster size!");
        return null;
    }

    // find the related cell for object j in cluster i
    this.searchThroughChildSibling(i, j);
    selected = mc.selClusterIW;
    k = mc.selItemIW;
    if (k < mc.clusters[selected]['n']) {
        latMin = mc.clusters[selected]['latMinO'];
        latMax = mc.clusters[selected]['latMaxO'];
        lonMin = mc.clusters[selected]['lonMinO'];
        lonMax = mc.clusters[selected]['lonMaxO'];

        // ge photo info
        photoInfo = this.getPhotoInfoInCell(latMin, latMax, lonMin, lonMax, k, this.markersClusteringObj.options.dataSize);
    }
    else
        alert("The requested object exceeds cluster size (in cell) !");

    return photoInfo;
}

markerX.prototype.getPhotoInfoInCell = function (minLat, maxLat, minLon, maxLon, k, dataSize) {
    var type, photoInfo, query, results, reverseX;
    reverseX = 0; // ??? maybe the cell is over vertical line where new world starts in google maps
    photoInfo = null;
    type = "photoInfoBoundingBox";
    query = clusteringServerPath + 'markerClustering.php?type=' + type
        + '&minLat=' + minLat + '&maxLat=' + maxLat + '&minLon=' + minLon + '&maxLon=' + maxLon
        + '&selected=' + k + '&dataSize=' + dataSize + '&reverseX=' + reverseX;

    query = encodeURI(query);
    results = httpLoad(query);

    if (results != "Error") {
        results = eval('(' + results + ')');
        photoInfo = results[0];
    }
    else {
        alert("Cannot fetch data from database!");
    }

    return photoInfo;
}

// recursive search to find target cluster among merged clusters of a displayed cluster
markerX.prototype.searchThroughChildSibling = function (i, j) {
    var n2, flag, mc;
    mc = this.markersClusteringObj.markerClusters;
    n1 = mc.n1;
    n2 = n1 + mc.clusters[i].n; // original cluster size for the cell
    mc.n1 = n2;

    if (j < n2) { // found
        mc.selClusterIW = i; // cluster number
        mc.selItemIW = j - n1; // object number in the cluster
        mc.contSearch = 0;
    }
    else {
        flag = mc.contSearch;
        if (flag) {
            if (mc.clusters[i].child != -1)
                this.searchThroughChildSibling(mc.clusters[i].child, j);
        }
        flag = mc.contSearch;
        if (flag) {
            if (mc.clusters[i].sibling != -1)
                this.searchThroughChildSibling(mc.clusters[i].sibling, j);
        }
    }
}

markerX.prototype.clickThumbCircleOnMap = function () {
    this.mapEx.closeInfoWindowByClick = false; // because of the issue of clicking on the circle triggers also click on map
    if (typeof openOrUpdateInfoWindow == 'function') {
        openOrUpdateInfoWindow(this); // this function is expecte to be in the main page that uses the API
    }
    // this.openOrUpdateInfoWindow();
}

markerX.prototype.doubleClickThumbCircleOnMap = function () {
    this.mapEx.setOptions({disableDoubleClickZoom: true});
    var myMap = this.mapEx;
    setTimeout(function () {
        myMap.setOptions({disableDoubleClickZoom: false});
    }, 500);
}

//----LABEL----//

function Label(opt_options) {
    var top = "0px;";
    // Initialization
    this.setValues(opt_options);
    this.type = opt_options.type;
    this.thumb = opt_options.thumb;
    this.clusterSize = opt_options.clusterSize;
    this.marker = opt_options.marker;
    this.markerStyle = opt_options.markerStyle;

    switch (this.markerStyle) {
        case "thumbnail":
            top = "0px;";
            break;

        case "marker1":
            top = "0px;";
            break;

        default:
            top = "0px;";
            break;
    }

    // Here go the label styles
    var div = this.div_ = document.createElement('div');
    if (this.markerStyle == "thumbnail")
        div.style.cssText = 'position: absolute; display: none;font-weight: bold;font-size: 15px;font-family: Arial;';
    else
        div.style.cssText = 'position: absolute; display: none;font-weight: bold;font-size: 12px;font-family: Arial;';
};

Label.prototype = new google.maps.OverlayView;

Label.prototype.onAdd = function () {
    var pane;
    if (this.markerStyle == "thumbnail")
        pane = this.getPanes().floatPane;
    if (this.markerStyle == "marker1")
        pane = this.getPanes().overlayImage;

    pane.appendChild(this.div_);

    // Ensures the label is redrawn if the text or position is changed.
    var me = this;
    this.listeners_ = [
        google.maps.event.addListener(this, 'position_changed', function () {
            me.draw();
        }),
        google.maps.event.addListener(this, 'text_changed', function () {
            me.draw();
        }),
        google.maps.event.addListener(this, 'zindex_changed', function () {
            me.draw();
        })
    ];
};

// Implement onRemove
Label.prototype.onRemove = function () {
    this.div_.parentNode.removeChild(this.div_);
    // Label is removed from the map, stop updating its position/text.
    for (var i = 0, I = this.listeners_.length; i < I; ++i)
        google.maps.event.removeListener(this.listeners_[i]);
};

// Implement draw
Label.prototype.draw = function () {
    var width, widthNumber, heightNumber, height, marginLeft;
    var projection = this.getProjection();
    var position = projection.fromLatLngToDivPixel(this.get('position'));
    var div = this.div_;

    div.style.display = 'block';
    div.style.zIndex = 10;

    if (this.markerStyle == "thumbnail") {
        width = "22px"; // label width
        height = "22px";
        widthNumber = 22;
        heightNumber = 22;
        marginLeft = '-10px';
        if (this.clusterSize > 9) {
            width = "30px";
            widthNumber = 30;
            marginLeft = '-14px';
        }
    } else {
        width = "14px"; // label width
        height = "14px";
        widthNumber = 14;
        heightNumber = 14;
        marginLeft = '-10px';
        if (this.clusterSize > 100) {
            width = "22px";
            widthNumber = 22;
            marginLeft = '-14px';
        }
    }

    if (this.markerStyle == "thumbnail") {
        div.style.left = position.x + this.marker.width / 2 - widthNumber / 2 + 'px';
        div.style.top = position.y - this.marker.height - heightNumber / 2 + 'px';
    } else {
        div.style.left = position.x - widthNumber / 2 + 'px';
        div.style.top = position.y - heightNumber / 2 + 'px';
    }

    div.style.height = "14px";
    div.style.width = width; // if I don't set this, in dragging map, the number in circle is misplaced

    switch (this.markerStyle) {
        case "thumbnail":
            if (this.thumb != undefined && this.clusterSize > 1) {
                var icon_filename = 'circle_' + this.color + '.gif';
                var nr = this.clusterSize > 99 ? "99+" : this.clusterSize;
                var id = Math.round(position.x) + "" + Math.round(position.y);

                div.innerHTML = '<img src="' + clusteringAssetPath + icon_filename + '" style="margin: 0 ' + marginLeft + '; cursor: pointer; z-index:1000;position: absolute; width:' + width + ';height:' + height + '; " id="' + id + '" />' + '<span id="P' + id + '" style="display:table-cell;vertical-align:middle;margin:auto;cursor: pointer; z-index:1001;overflow: hidden;position:relative;text-align: center;width:' + width + ';height: ' + height + ';">' + nr + '</span>';

                var me = this;
                $(div).unbind('click');
                $(div).unbind('dblclick');

                $(div).click(function () {
                    me.marker.clickThumbCircleOnMap();
                });

                div.addEventListener('dblclick', function (e) {
                    me.marker.doubleClickThumbCircleOnMap();
                });
            } else {
                //
            }
            break;

        case "marker1":
            var nr = this.clusterSize > 99 ? "99+" : this.clusterSize;
            div.innerHTML = '<span style="display:table-cell;vertical-align:middle;margin:auto;ursor: pointer; z-index:1000;overflow: hidden;position: relative;text-align: center;width:' + width + ';">' + nr + '</span>';
            break;
    }
}

// format latitude like N 62.60 E 29.75
function formatLat(num, accuracy) {
    var temp = new Number(num);
    var Fnum = "";
    if (num > 0) {
        Fnum = temp.toFixed(accuracy);
        Fnum = "N " + Fnum;
    }
    else {
        temp = -1 * temp;
        Fnum = temp.toFixed(accuracy);
        Fnum = "S " + Fnum;
    }
    return Fnum;
}//endfunction formatLat

// format longitude like N 62.60 E 29.75
function formatLon(num, accuracy) {
    var temp = new Number(num);
    var Fnum = "";
    if (num > 0) {
        Fnum = temp.toFixed(accuracy);
        Fnum = "E " + Fnum;
    }
    else {
        temp = -1 * temp;
        Fnum = temp.toFixed(accuracy);
        Fnum = "W " + Fnum;
    }
    return Fnum;
}//endfunction formatLon;

// url should be relative address (without main address of website)
function fileExists(url) {
    var xhr = new XMLHttpRequest();
    xhr.open('HEAD', url, false);
    xhr.send();

    if (xhr.status == "404") {
        return false;
    } else {
        return true;
    }
}