/**
 * @name mopsiClustering
 * @class This class includes functions at logic level for clustering objects
 *
 * @param {array of objects} dataIn All input data information that should be clustered and it includes:
 *  e.g. dataIn[i].x and dataIn[i].y
 * @param {json} params includes all required parameters for clustering,
 *  e.g. for grid-based clustering:
 *  params.type in this case it is "gridBased"
 *  params.minX minimum x in x axis of view
 *  params.maxX maximum x in x axis of view
 *  params.minY minimum y in y axis of view
 *  params.maxY maximum y in y axis of view
 *  params.cellHeight cell height in grid
 *  params.cellWidth cell width in grid
 *  params.cellWidth cell width in grid
 *  params.distMerge distance threshold to merge two clusters, changed it to minimum distance between two markers
 *  params.representativeType determines the criteria for the location of clusters' reprersentatives which
 *   can be "mean", "first" and "middleCell"
 * @return {json} dataOut includes all information about output clusters and also cluster labels of input data, it includes:
 *  dataOut.numClusters {number} represents number of clusters
 *  dataOut.dataSize {number} represents size of input data
 *  dataOut.clusters {array} represents information of clusters and includes:
 *   dataOut.clusters[i].clusterSize {number} the size of cluster i
 *   dataOut.clusters[i].group {array} the indexes of the objects in cluster i among whole input data
 *   dataOut.clusters[i].represent {array} the representative of cluster i, for grid-based clustering it includes:
 *    dataOut.clusters[i].represent[0] x value of location of representative (in pixel)
 *    dataOut.clusters[i].represent[1] y value of location of representative (in pixel)
 *  dataOut.dataLabel {array} represents cluster label for every input data
 */

function mopsiClustering(dataIn, params) {
    this.params = params;
    this.dataIn = dataIn;
    this.mmcObj = null; // the mopsiMarkerClustering object which created this
    this.init();
}

/**
 * intitialization and checking input data and parameters
 */
mopsiClustering.prototype.init = function () {

}

/**
 * clustering procedure starts here
 */
mopsiClustering.prototype.applyClustering = function () {
    switch (this.params.type) {
        case "distancebased":
            this.distanceBasedClustering2();
            break;
        case "gridbased":
            this.gridBasedClustering();
            break;
        case "pnn":
            this.pnnClustering();
            break;
        case "gridbasedClientServer":
            this.gridbasedClientServer(); // for both: serverClient and Server side clustering
            break;
        case "clutter_noClustering":
            this.clutterNoClustering();
            break;
        default:
            break;
    }

    return this.dataOut;
}

/**
 * main flow of grid based clustering
 */
mopsiClustering.prototype.gridBasedClustering = function () {
    var clusters, representType, clustersOrder;
    representType = this.params.representativeType; // mean or first

    this.mmcObj.initialClusteringOnClient = new Date();
    clusters = this.initializeGridBasedClusters();
    clustersOrder = this.assignPointsToCells(clusters);

    this.setRepresentatives(clusters, clustersOrder, representType);

    this.mmcObj.initialClusteringOnClient = new Date() - this.mmcObj.initialClusteringOnClient;

    this.mmcObj.mergeTimeOnClient = new Date();
    // this.checkOverlapAmongNeighbors(clusters, clustersOrder, representType);
    this.handleOverlappedClusters(clusters, clustersOrder, representType);

    this.mmcObj.mergeTimeOnClient = new Date() - this.mmcObj.mergeTimeOnClient;

    this.dataOut = this.constructOutputClusters(clusters);

}

/**
 * processing data both on server and client
 */
mopsiClustering.prototype.gridbasedClientServer = function () {
    var nClusters1, clusters, x, y, nR, nC, n, clusters1;
    var lat, lng;
    var k, total, i, j, rep, dataSize;
    var numArray, numRow, numColumn, representType;
    var clustersOrder = new Array();

    this.mmcObj.totalTimeClientClustering = new Date;

    representType = this.params.representativeType; // mean or first

    var maxX = this.params.maxX;
    var maxY = this.params.maxY;
    var minX = this.params.minX;
    var minY = this.params.minY;

    if (this.params.representativeType == undefined || this.params.representativeType == null)
        rep = "mean"; // mean or first
    else
        rep = this.params.representativeType; // mean or first

    clusters1 = this.dataIn;
    nClusters1 = clusters1.length; // number of initial clusters from server

    clusters = {};
    clusters.clusters = [];

    numRow = Math.ceil((maxY - minY) / this.params.cellHeight);
    numColumn = Math.ceil((maxX - minX) / this.params.cellWidth);

    dataSize = 0;
    for (i = 0; i < nClusters1; i++) {
        clusters.clusters[i] = {};
        clusters.clusters[i].clusterSize = clusters1[i].n;
        clusters.clusters[i].n = clusters1[i].n; // original cluster size for cells
        clusters.clusters[i].valid = true;
        clusters.clusters[i].represent = [];
        clusters.clusters[i].represent[0] = clusters1[i].x;
        clusters.clusters[i].represent[1] = clusters1[i].y;
        clusters.clusters[i]['latMin'] = clusters1[i].latMin;
        clusters.clusters[i]['latMax'] = clusters1[i].latMax;
        clusters.clusters[i]['lonMin'] = clusters1[i].lonMin;
        clusters.clusters[i]['lonMax'] = clusters1[i].lonMax;

        // original bounding box for a clusetr before merge
        clusters.clusters[i]['latMinO'] = clusters1[i].latMin;
        clusters.clusters[i]['latMaxO'] = clusters1[i].latMax;
        clusters.clusters[i]['lonMinO'] = clusters1[i].lonMin;
        clusters.clusters[i]['lonMaxO'] = clusters1[i].lonMax;

        clusters.clusters[i].child = -1;
        clusters.clusters[i].sibling = -1;

        clusters.clusters[i]['thumburl'] = clusters1[i].thumburl;
        clusters.clusters[i]['photourl'] = clusters1[i].photourl;
        clusters.clusters[i]['id'] = clusters1[i].id;
        dataSize += clusters1[i].n;

        k = this.getCellNum(clusters1[i].x, clusters1[i].y, numColumn, numRow);
        clustersOrder[i] = k;
    }
    clusters.dataSize = dataSize;
    clusters.numColumn = numColumn;
    clusters.numRow = numRow;
    clusters.numCells = numRow * numColumn;

    this.mmcObj.mergeTimeOnClient = new Date();

    // this.checkOverlapAmongNeighbors(clusters, clustersOrder, representType);
    this.handleOverlappedClusters(clusters, clustersOrder, representType);
    this.mmcObj.mergeTimeOnClient = new Date() - this.mmcObj.mergeTimeOnClient;
    this.mmcObj.totalTimeClientClustering = new Date - this.mmcObj.totalTimeClientClustering;
    this.dataOut = this.constructOutputClusters(clusters);

}

/**
 * it checks a cluster with 8 neighbor cells (if contains clusters) for overlap,
 * in case of overlap, it merges two clusters
 */
mopsiClustering.prototype.checkOverlapAmongNeighbors = function (clusters, clustersOrder, representType) {
    var i, j, c, cluster1, cluster2, numColumn, index1, index2, cellNumber;

    numColumn = clusters.numColumn;

    // initialize parent of each cluster as itself
    for (c = 0; c < clustersOrder.length; c++) {
//    alert(clusters.clusters[clustersOrder[c]].group.length);
        if (this.params.type == "gridbasedClientServer")
            i = c;
        else
            i = clustersOrder[c];

        clusters.clusters[i].parent = i;
    }

    for (c = 0; c < clustersOrder.length; c++) {
        if (this.params.type == "gridbasedClientServer")
            index1 = c;
        else
            index1 = clustersOrder[c];
        cellNumber = clustersOrder[c]; // for client-side clustering, cellNumber is equal to index1

        cluster1 = clusters.clusters[index1];

        if (cluster1.valid == false)
            continue;

        // check 8 neighbors
        for (j = 0; j < 9; j++) {
            index2 = this.getNeighbourCellNum(j, cellNumber, numColumn);
            if (this.params.type == "gridbasedClientServer")
                index2 = clustersOrder.indexOf(index2);
            ;

            cluster2 = clusters.clusters[index2];

            if (cluster2 != undefined && index2 != index1 && cluster2.clusterSize > 0) {
                index2 = this.checkForMerge(clusters, index1, index2, representType); // might be new index different from index2
                if (index2 != -1) {
                    //if ( index1 == 7 )
                    //  alert(clusters.clusters[index1].clusterSize+" "+index2+" "+clusters.clusters[index2].clusterSize);
                    this.mergeTwoClusters(clusters, index1, index2, representType, false);
                }
            }
        }
    }
}

/**
 * it checks a cluster with 8 neighbor cells (if contains clusters) for overlap,
 * in case of overlap, it merges two clusters
 */
mopsiClustering.prototype.handleOverlappedClusters = function (clusters, clustersOrder, representType) {
    var c1, c2, index1, index2, variableSizeIcon, maxCheck, i, flag, j, temp, index, jj;

    variableSizeIcon = false;
    if (this.params.markerStyle == "marker1") { // only for the marker with circle shape
        variableSizeIcon = true;
    }

    for (c1 = 0; c1 < clustersOrder.length; c1++) {
        index1 = this.getClusterIndex(clustersOrder, c1);
        clusters.clusters[index1].overlapWithWhichClusters = [];
        if (variableSizeIcon)
            this.setIconSize(clusters, index1);
    }

    // find and mark overlapped clusters
    for (c1 = 0; c1 < clustersOrder.length; c1++) {
        index1 = this.getClusterIndex(clustersOrder, c1);
        for (c2 = c1 + 1; c2 < clustersOrder.length; c2++) {
            index2 = this.getClusterIndex(clustersOrder, c2);
            this.checkOverlapTwoClusters(clusters, index1, index2, variableSizeIcon);
        }
    }

    // merge overlapped clusters
    maxCheck = 10000;
    flag = true;
    i = 0;
    while (flag && (i < maxCheck)) {
        // alert("1: "+this.getDataSizeFromClusters(clusters, clustersOrder));
        c1 = this.findOverlappedCluster(clusters, clustersOrder); // return real index
        if (c1 == -1) {
            flag = false; // no overlap anymore
        } else {
            c2 = this.getRelatedOverlappedClusterWithClosestCentroid(clusters, c1);
            // merge two clusters c1 and c2
            this.mergeTwoClusters(clusters, c1, c2, representType, variableSizeIcon);
            // alert("2: "+this.getDataSizeFromClusters(clusters, clustersOrder));
            // remove cluster c2 from the list of overlapped of other clusters
            for (j = 0; j < clustersOrder.length; j++) {
                index = this.getClusterIndex(clustersOrder, j);
                temp = clusters.clusters[index].overlapWithWhichClusters;
                jj = temp.indexOf(c2);
                if (jj > -1)
                    clusters.clusters[index].overlapWithWhichClusters.splice(jj, 1); // delete the removed cluster from the list
            }

            // update overlap status of c1 with other clusters, maybe it has now overlap with new clusters or
            // some overlap has been removed
            this.updateOverlapStatusOneCluster(clusters, clustersOrder, c1, variableSizeIcon);
        }
        i++;
    }
}

mopsiClustering.prototype.getDataSizeFromClusters = function (clusters, clustersOrder) {
    var c, dataSize, i;

    dataSize = 0;
    for (c = 0; c < clustersOrder.length; c++) {
        i = this.getClusterIndex(clustersOrder, c);
        if (clusters.clusters[i].valid == true)
            dataSize += clusters.clusters[i].clusterSize;
    }

    return dataSize;
}

/**
 * find the overlapped cluster which has overlap with more number of clusters
 * returns the index of the overlapped cluster, or -1 if no overlap
 * note: returns real index to be used in the variable clusters.clusters
 */
mopsiClustering.prototype.findOverlappedCluster = function (clusters, clustersOrder) {
    var index, c, maxNumOverlap, i, cluster;

    index = -1;
    maxNumOverlap = 0;
    for (c = 0; c < clustersOrder.length; c++) {
        i = this.getClusterIndex(clustersOrder, c);
        cluster = clusters.clusters[i];
        if (cluster.valid && cluster.overlapWithWhichClusters.length > maxNumOverlap) {
            maxNumOverlap = cluster.overlapWithWhichClusters.length;
            index = i;
        }
    }

    return index;
}

/**
 * find the overlapped cluster which has overlap with more number of clusters
 * returns the index of the overlapped cluster, or -1 if no overlap
 * note: returns real index to be used in the variable clusters.clusters
 */
mopsiClustering.prototype.getRelatedOverlappedClusterWithClosestCentroid = function (clusters, c) {
    var index, i, cluster, j, point1, point2, dist, minDist;

    cluster1 = clusters.clusters[c];
    j = cluster1.overlapWithWhichClusters[0];
    cluster2 = clusters.clusters[j];
    // distance between the centroids
    point1 = {};
    point2 = {};
    point1.x = cluster1.represent[0];
    point1.y = cluster1.represent[1];
    point2.x = cluster2.represent[0];
    point2.y = cluster2.represent[1];

    minDist = this.EucDistance(point1, point2);
    index = j;

    for (i = 1; i < cluster1.overlapWithWhichClusters.length; i++) {
        j = cluster1.overlapWithWhichClusters[i]; // note: this variable overlapWithWhichClusters contains the index to be used in clusters.clusters
        cluster2 = clusters.clusters[j];
        // distance between the centroids
        point1 = {};
        point2 = {};
        point1.x = cluster1.represent[0];
        point1.y = cluster1.represent[1];
        point2.x = cluster2.represent[0];
        point2.y = cluster2.represent[1];

        dist = this.EucDistance(point1, point2);

        if (dist < minDist) {
            minDist = dist;
            index = j;
        }
    }

    return index;
}

/**
 * it checks two clusters index1 and index2 for overlap based on the distance between their representatives
 */
mopsiClustering.prototype.checkOverlapTwoClusters = function (clusters, c1, c2, variableSizeIcon) {
    var cnt, cluster1, cluster2, point1, point2, minDist, distx, disty, thx, thy;

    point1 = {};
    point2 = {};
    cluster1 = clusters.clusters[c1];
    cluster2 = clusters.clusters[c2];
    minDist = this.params.distMerge; // threshold in pixel

    point1.x = cluster1.represent[0];
    point1.y = cluster1.represent[1];
    point2.x = cluster2.represent[0];
    point2.y = cluster2.represent[1];

    distx = Math.abs(point1.x - point2.x);
    disty = Math.abs(point1.y - point2.y);

    if (variableSizeIcon) {
        thx = minDist + (cluster1.iconWidth + cluster2.iconWidth) / 2.0;
        thy = minDist + (cluster1.iconHeight + cluster2.iconHeight) / 2.0;
    }
    else {
        thx = minDist + this.params.iconWidth;
        thy = minDist + this.params.iconHeight;
    }

    if ((distx < thx) && (disty < thy)) {
        // there is overlap
        cnt = cluster1.overlapWithWhichClusters.length;
        cluster1.overlapWithWhichClusters[cnt] = c2;
        cnt = cluster2.overlapWithWhichClusters.length;
        cluster2.overlapWithWhichClusters[cnt] = c1;
    }
}

mopsiClustering.prototype.updateOverlapStatusOneCluster = function (clusters, clustersOrder, c1, variableSizeIcon) {
    var i, j;
    cluster1 = clusters.clusters[c1];
    cluster1.overlapWithWhichClusters = [];
    for (i = 0; i < clustersOrder.length; i++) {
        c2 = this.getClusterIndex(clustersOrder, i);
        cluster2 = clusters.clusters[c2];
        j = cluster2.overlapWithWhichClusters.indexOf(c1);
        if (j > -1) {
            cluster2.overlapWithWhichClusters.splice(j, 1);
        }
    }

    // check overlap with all again
    for (i = 0; i < clustersOrder.length; i++) {
        c2 = this.getClusterIndex(clustersOrder, i);
        if (c2 != c1 && clusters.clusters[c2].valid) {
            this.checkOverlapTwoClusters(clusters, c1, c2, variableSizeIcon);
        }
    }
}

/**
 * return the real index of cluster in the variable: clusters.clusters
 */
mopsiClustering.prototype.getClusterIndex = function (clustersOrder, i) {
    var index = i;

    if (this.params.type != "gridbasedClientServer")
        index = clustersOrder[i]; // considering all cells as clusters, where some cells are empty

    return index;
}

/**
 * set icon size for clusters (only for the icons with circle or square shapes )
 */
mopsiClustering.prototype.setIconSize = function (clusters, c) {
    var cluster, n, w, h, w0, h0;

    cluster = clusters.clusters[c];
    n = cluster.clusterSize;

    w0 = 15;
    w = this.params.iconWidth;
    if (n > 1)
        w = w0 + Math.floor(8 * Math.log10(n) + 0.5);
    h0 = 15;
    h = this.params.iconHeight;
    if (n > 1)
        h = h0 + Math.floor(8 * Math.log10(n) + 0.5);

    cluster.iconWidth = w;
    cluster.iconHeight = h;
}

/**
 * merge two clusters
 * cluster index2 is merged in cluster index1
 * the representative locations of both clusters are updated to new location
 */
mopsiClustering.prototype.mergeTwoClusters = function (clusters, index1, index2, representType, variableSizeIcon) {
    var cluster1, cluster2, point1, point2, k, n1, n2;

    point1 = {};
    point2 = {};
    cluster1 = clusters.clusters[index1];
    cluster2 = clusters.clusters[index2];

    n1 = cluster1.clusterSize;
    n2 = cluster2.clusterSize;
    point1.x = cluster1.represent[0];
    point1.y = cluster1.represent[1];
    point2.x = cluster2.represent[0];
    point2.y = cluster2.represent[1];

    if (this.params.type != "gridbasedClientServer")
        for (k = 0; k < n2; k++) {
            cluster1.group.push(cluster2.group[k]);
            clusters.dataLabel[cluster2.group[k]] = index1;
        }
    cluster1.clusterSize += cluster2.clusterSize;
    if (variableSizeIcon) {
        this.setIconSize(clusters, index1);
    }
    // alert(index1+" "+index2+" "+cluster2.clusterSize);
    cluster2.valid = false; // not valid after merged into cluster1

    // update the representative
    if (representType == "mean") {
        cluster1.represent[0] = (point1.x * n1 + point2.x * n2) / (n1 + n2);
        cluster1.represent[1] = (point1.y * n1 + point2.y * n2) / (n1 + n2);
//    cluster2.represent[0] = cluster1.represent[0];
//    cluster2.represent[1] = cluster1.represent[1];

        cluster2.parent = index1;
        if (this.params.type == "gridbasedClientServer") {
            // update bounding box
            if (cluster2.latMin < cluster1.latMin)
                cluster1.latMin = cluster2.latMin;
            if (cluster2.latMax > cluster1.latMax)
                cluster1.latMax = cluster2.latMax;
            if (cluster2.lonMin < cluster1.lonMin)
                cluster1.lonMin = cluster2.lonMin;
            if (cluster2.lonMax > cluster1.lonMax)
                cluster1.lonMax = cluster2.lonMax;

            this.setChildSibling(clusters, index1, index2);
        }
    }
    if (representType == "first") {
        cluster1.represent[0] = point1.x;
        cluster1.represent[1] = point1.y;
    }
}

/**
 * remember history of merging neighbor clusters
 */
mopsiClustering.prototype.setChildSibling = function (clusters, i, j) {
    var cluster1, cluster2, cluster, cnt;
    cluster1 = clusters.clusters[i];
    cluster2 = clusters.clusters[j];
    if (cluster1.child == -1) // no child
        cluster1.child = j;
    else {
        // find last sibling of the child
        cluster = clusters.clusters[cluster1.child];
        cnt = 0;
        while (cluster.sibling != -1) {
            cluster = clusters.clusters[cluster.sibling];
            cnt++;
            if (cnt > 50) {
                alert("Too many repeat in setChildSibling function!");
            }
        }

        cluster.sibling = j;
    }
}

/**
 * it checks two clusters index1 and index2 for overlap based on the distance between their representatives
 * if cluster index2 is already merged with another cluster, its parents are checked
 */
mopsiClustering.prototype.checkForMerge = function (clusters, index1, index2, representType) {
    var flagC, cnt, cluster1, cluster2, point1, point2, minDist, indexX, distx, disty, thx, thy;

    indexX = index2;
    flagC = false;
    point1 = {};
    point2 = {};
    cluster1 = clusters.clusters[index1];
    cluster2 = clusters.clusters[index2];
    minDist = this.params.distMerge; // threshold in pixel

    point1.x = cluster1.represent[0];
    point1.y = cluster1.represent[1];
    point2.x = cluster2.represent[0];
    point2.y = cluster2.represent[1];

    if (this.params.markerStyle == "marker1") {
        if (cluster1.clusterSize == 1)
            point1.y = point1.y - this.params.iconHeight / 2;
        if (cluster2.clusterSize == 1)
            point2.y = point2.y - this.params.iconHeight / 2;
    }

    // dist = this.EucDistance(point1, point2); // between two representative
    distx = Math.abs(point1.x - point2.x);
    disty = Math.abs(point1.y - point2.y);

    thx = minDist + this.params.iconWidth;
    thy = minDist + this.params.iconHeight;

    if ((distx < thx) && (disty < thy)) {
        flagC = true;
        if (representType == "mean") {
            cnt = 0;
            while (cluster2.valid == false && flagC) {
                if (cluster2.parent == index1)
                    flagC = false;
                else {
                    indexX = cluster2.parent;
                    cluster2 = clusters.clusters[cluster2.parent];
                    // parent has same representative, so no need to update point2 and check dist,
                    // we update the location of a cluster to new representative location when it is merged
                }

                if (cnt > 50) {
                    alert("Too many checks for neighbors in grid-based clustering!");
                    flagC = false;
                }

                cnt++;
            }
        }

        if (representType == "first" && cluster2.valid == false)
            flagC = false;
    }

    if (flagC)
        return indexX;
    else
        return -1;
}

/**
 * it finds the cell containing every input data and constructs the initial clusters
 * the points in the same cell are considered in one cluster
 */
mopsiClustering.prototype.assignPointsToCells = function (clusters) {
    var dataSize, x, y, k, i, j;
    var lat, lng, numRow, numColumn, clustersOrder;

    numRow = clusters.numRow;
    numColumn = clusters.numColumn;
    dataSize = this.dataIn.length;

    j = 0;
    clustersOrder = new Array();
    for (i = 0; i < dataSize; i++) {
        x = this.dataIn[i].x;
        y = this.dataIn[i].y;

        k = this.getCellNum(x, y, numColumn, numRow);

        clusters.dataLabel[i] = k;
        if (k == -1)
            continue;

        if (k < 0 || k >= clusters.numCells || (clusters.clusters[k] == undefined))
            alert("Fatal error: in grid-based clustering in clustering.js");

        if (clusters.clusters[k].clusterSize == 0) {
            clustersOrder[j] = k;
            j++;
        }

        clusters.clusters[k].group[clusters.clusters[k].clusterSize] = i;
        clusters.clusters[k].clusterSize += 1;
        clusters.clusters[k].valid = true;
    }

    return clustersOrder;
}

/**
 * it provides grid based on input parameters and initializes clusters
 * every cell in the grid is ceonsidered as an empty cluster
 */
mopsiClustering.prototype.initializeGridBasedClusters = function () {
    var dataSize, clusters, i;
    var numRow, numColumn, minX, maxX, minY, maxY;

    maxX = this.params.maxX;
    maxY = this.params.maxY;
    minX = this.params.minX;
    minY = this.params.minY;
    dataSize = this.dataIn.length;

    clusters = {};
    clusters.clusters = [];
    clusters.dataLabel = [];

    numRow = Math.ceil((maxY - minY) / this.params.cellHeight);
    numColumn = Math.ceil((maxX - minX) / this.params.cellWidth);
    clusters.numCells = numRow * numColumn;

    for (i = 0; i < dataSize; i++)
        clusters.dataLabel[i] = -1;

    for (i = 0; i < clusters.numCells; i++) {
        clusters.clusters[i] = {};
        clusters.clusters[i].clusterSize = 0; // counter over clusters
        clusters.clusters[i].valid = false;
        clusters.clusters[i].group = [];
        clusters.clusters[i].represent = [];
    }

    clusters.dataSize = dataSize;
    clusters.numRow = numRow;
    clusters.numColumn = numColumn;

    return clusters;
}

/**
 * it does not apply clustering to remove clutter and just provides same output data format
 */
mopsiClustering.prototype.clutterNoClustering = function () {
    var dataSize, clusters, x, y;
    var i;

    var maxX = this.params.maxX;
    var maxY = this.params.maxY;
    var minX = this.params.minX;
    var minY = this.params.minY;

    dataSize = this.dataIn.length;

    clusters = {};
    clusters.clusters = [];
    clusters.dataLabel = [];

    clusters.numClusters = dataSize;

    for (i = 0; i < dataSize; i++)
        clusters.dataLabel[i] = -1;

    for (i = 0; i < clusters.numClusters; i++) {
        clusters.clusters[i] = {};
        clusters.clusters[i].clusterSize = 0;
        clusters.clusters[i].valid = false;
        clusters.clusters[i].group = [];
        clusters.clusters[i].represent = [];
    }

    clusters.dataSize = dataSize;

    for (i = 0; i < dataSize; i++) {
        x = this.dataIn[i].x;
        y = this.dataIn[i].y;

        clusters.dataLabel[i] = i;

        clusters.clusters[i].group[0] = i;
        clusters.clusters[i].clusterSize += 1;
        clusters.clusters[i].valid = true;

        clusters.clusters[i].represent[0] = x;
        clusters.clusters[i].represent[1] = y;
    }

    this.dataOut = clusters;

}

/**
 * clustering algorithm to remove overlap of markers:
 * it check the distance of markers to a marker and the close markers are merged into it
 * the first point in a cluster is selected as representative
 */
mopsiClustering.prototype.distanceBasedClustering1 = function () {
    var cnt, dataSize, i, j, dataOut, groupID, visited, distFlag, x, y;

    dataSize = this.dataIn.length;
    dataOut = {};
    dataOut.clusters = [];
    dataOut.dataLabel = [];
    visited = [];

    var maxX = this.params.maxX;
    var maxY = this.params.maxY;
    var minX = this.params.minX;
    var minY = this.params.minY;

    for (i = 0; i < dataSize; i++) {
        visited[i] = 0;
        dataOut.dataLabel[i] = -1;
    }

    groupID = 0;

    for (i = 0; i < dataSize; i++)
        if (visited[i] != 1) {
            visited[i] = 1;
            dataOut.dataLabel[i] = groupID;
            dataOut.clusters[groupID] = {};
            dataOut.clusters[groupID].clusterSize = 1;
            dataOut.clusters[groupID].represent = [];
            dataOut.clusters[groupID].group = [];
            dataOut.clusters[groupID].represent[0] = this.dataIn[i].x;
            dataOut.clusters[groupID].represent[1] = this.dataIn[i].y;
            dataOut.clusters[groupID].group[0] = i;
            dataOut.clusters[groupID].valid = true;
            for (j = i + 1; j < dataSize; j++)
                if (visited[j] != 1) {
                    distFlag = this.checkDist(this.dataIn[i], this.dataIn[j]);
                    if (!distFlag) { // two points are considered as in one cluster
                        dataOut.dataLabel[j] = groupID;
                        cnt = dataOut.clusters[groupID].clusterSize;
                        dataOut.clusters[groupID].group[cnt] = j;
                        dataOut.clusters[groupID].clusterSize += 1;
                        visited[j] = 1;

                    }
                }
            groupID++;
        }

    dataOut.numClusters = groupID;

    dataOut.dataSize = dataSize;

    this.dataOut = dataOut;
}


/**
 * clustering algorithm to remove overlap of markers:
 * it check the distance of markers to a marker and the close markers are merged into it
 * the average location of points in a cluster is the location of representative
 */
mopsiClustering.prototype.distanceBasedClustering2 = function () {
    var cnt, dataSize, i, j, dataOut, groupID, visited, distFlag, x, y;

    this.mmcObj.initialClusteringOnClient = new Date();

    dataSize = this.dataIn.length;
    dataOut = {};
    dataOut.clusters = [];
    dataOut.dataLabel = [];
    visited = [];

    var maxX = this.params.maxX;
    var maxY = this.params.maxY;
    var minX = this.params.minX;
    var minY = this.params.minY;

    for (i = 0; i < dataSize; i++) {
        visited[i] = 0;
        dataOut.dataLabel[i] = -1;
    }

    groupID = 0;

    for (i = 0; i < dataSize; i++) {
        if (visited[i] != 1) {
            visited[i] = 1;
            dataOut.dataLabel[i] = groupID;
            dataOut.clusters[groupID] = {};
            dataOut.clusters[groupID].clusterSize = 1;
            dataOut.clusters[groupID].represent = [];
            dataOut.clusters[groupID].group = [];
            dataOut.clusters[groupID].represent[0] = this.dataIn[i].x;
            dataOut.clusters[groupID].represent[1] = this.dataIn[i].y;
            dataOut.clusters[groupID].group[0] = i;
            dataOut.clusters[groupID].valid = true;
            for (j = i + 1; j < dataSize; j++) {
                if (visited[j] != 1) {
                    distFlag = this.checkDist(this.dataIn[i], this.dataIn[j]);
                    if (!distFlag) { // two points are considered as in one cluster
                        dataOut.dataLabel[j] = groupID;
                        cnt = dataOut.clusters[groupID].clusterSize;
                        dataOut.clusters[groupID].group[cnt] = j;
                        dataOut.clusters[groupID].clusterSize += 1;
                        visited[j] = 1;
                        // to update centroid
                        dataOut.clusters[groupID].represent[0] += this.dataIn[j].x;
                        dataOut.clusters[groupID].represent[1] += this.dataIn[j].y;

                    }
                }
            }

            // average the location for representative
            dataOut.clusters[groupID].represent[0] /= dataOut.clusters[groupID].clusterSize;
            dataOut.clusters[groupID].represent[1] /= dataOut.clusters[groupID].clusterSize;

            groupID++;
        }


    }

    dataOut.numClusters = groupID;

    dataOut.dataSize = dataSize;

    this.mmcObj.initialClusteringOnClient = new Date() - this.mmcObj.initialClusteringOnClient;

    this.dataOut = dataOut;
}

/** pnn
 * clustering algorithm to remove overlap of markers:
 */
mopsiClustering.prototype.pnnClustering = function () {
    var cnt, dataSize, i, j, dataOut, x, y, tx, ty;
    var distanceMatrix, iMin, nClusters, i1, i2;
    var indexes, data, n1, n2, clusters, c, th, k;

    this.mmcObj.initialClusteringOnClient = new Date();

    th = this.params.distMerge * this.params.distMerge;

    dataSize = this.dataIn.length;
    dataOut = {};
    dataOut.clusters = [];
    dataOut.dataLabel = [];
    iMin = [];
    data = [];
    clusters = [];

    var maxX = this.params.maxX;
    var maxY = this.params.maxY;
    var minX = this.params.minX;
    var minY = this.params.minY;

    for (i = 0; i < dataSize; i++) {
        x = this.dataIn[i].x;
        y = this.dataIn[i].y;
        data[i] = {};
        data[i].x = x;
        data[i].y = y;
    }

    N = dataSize; // number of data objects in map view

    // initialize clusters
    for (i = 0; i < N; i++) {
        clusters[i] = {};
        clusters[i].represent = [];
        clusters[i].represent[0] = this.dataIn[i].x;
        clusters[i].represent[1] = this.dataIn[i].y;
        clusters[i].clusterSize = 1;
        clusters[i].group = [];
        clusters[i].group[0] = i;
    }

    // find all paiwise distances
    distanceMatrix = new Array(N);
    for (i = 0; i < N; i++)
        distanceMatrix[i] = new Array(N);
    for (i = 0; i < N; i++) {
        for (j = i + 1; j < N; j++) {
            tx = data[i].x - data[j].x;
            ty = data[i].y - data[j].y;
            distanceMatrix[i][j] = (tx * tx) + (ty * ty);
            distanceMatrix[j][i] = distanceMatrix[i][j];
        }
    }
    for (i = 0; i < N; i++)
        distanceMatrix[i][i] = Number.MAX_VALUE;

    for (i = 0; i < N; i++)
        iMin[i] = i;

    for (i = 0; i < N; i++)
        for (j = 0; j < N; j++)
            if (distanceMatrix[i][j] < distanceMatrix[i][iMin[i]])
                iMin[i] = j;

    nClusters = N;

    // find two most similar clusters
    i1 = 0;
    for (i = 0; i < N; i++)
        if (distanceMatrix[i][iMin[i]] < distanceMatrix[i1][iMin[i1]])
            i1 = i;
    i2 = iMin[i1]; // we know that i1 is always less than i2

    while (distanceMatrix[i1][i2] < th) {
        // merge clusters i1 and i2 and update centroid
        for (i = 0; i < clusters[i2].group.length; i++)
            clusters[i1].group.push(clusters[i2].group[i]);

        n1 = clusters[i1].clusterSize;
        n2 = clusters[i2].clusterSize;
        x1 = clusters[i1].represent[0];
        y1 = clusters[i1].represent[1];
        x2 = clusters[i2].represent[0];
        y2 = clusters[i2].represent[1];
        clusters[i1].represent[0] = (x1 * n1 + x2 * n2) / (n1 + n2);
        clusters[i1].represent[1] = (y1 * n1 + y2 * n2) / (n1 + n2);

        clusters[i1].clusterSize += n2;

        // Max_VALUE to row i2 and column i2
        for (i = 0; i < N; i++)
            distanceMatrix[i2][i] = distanceMatrix[i][i2] = Number.MAX_VALUE;

        // update iMin and replace ones that previous pointed to i2 to point to i1
        for (i = 0; i < N; i++) {
            if (iMin[i] == i2)
                iMin[i] = i1;
            if (distanceMatrix[i1][i] < distanceMatrix[i1][iMin[i1]])
                iMin[i1] = i;
        }
        clusters[i2].group = []; // no object in cluster i2

        nClusters--;

        // find next most similar clusters
        i1 = 0;
        for (i = 0; i < N; i++)
            if (distanceMatrix[i][iMin[i]] < distanceMatrix[i1][iMin[i1]])
                i1 = i;
        i2 = iMin[i1]; // we know that i1 is always less than i2
    }

    // write clusters to output variable
    for (i = 0; i < dataSize; i++)
        dataOut.dataLabel[i] = -1;

    c = 0;
    for (k = 0; k < N; k++) {
        if (clusters[k].group.length > 0) {
            dataOut.clusters[c] = {};
            dataOut.clusters[c].clusterSize = clusters[k].clusterSize;
            dataOut.clusters[c].represent = [];
            dataOut.clusters[c].represent[0] = clusters[k].represent[0];
            dataOut.clusters[c].represent[1] = clusters[k].represent[1];
            dataOut.clusters[c].valid = true;

            dataOut.clusters[c].group = [];
            for (i = 0; i < clusters[k].group.length; i++) {
                j = clusters[k].group[i];
                dataOut.dataLabel[j] = c;
                dataOut.clusters[c].group[i] = j;
            }

            c++;
        }
    }

    dataOut.numClusters = c;
    dataOut.dataSize = dataSize;

    this.mmcObj.initialClusteringOnClient = new Date() - this.mmcObj.initialClusteringOnClient;

    this.dataOut = dataOut;
}

/**
 * provides variable as output data of clustering
 */
mopsiClustering.prototype.constructOutputClusters = function (clusters) {
    var i, j, k, dataOut;

    dataOut = {};
    dataOut.clusters = [];
    dataOut.dataLabel = [];

    k = 0;
    for (i = 0; i < clusters.clusters.length; i++) {
        if (this.params.type != "gridbasedClientServer") {
            if (clusters.clusters[i].valid == true) {
                dataOut.clusters.push(clusters.clusters[i]);
                for (j = 0; j < clusters.clusters[i].clusterSize; j++) // we don't know what objects are in clusters in server-side clustering
                    dataOut.dataLabel[clusters.clusters[i].group[j]] = k;
                k++;
            }
        } else { // server-side clustering
            dataOut.clusters.push(clusters.clusters[i]); // we need invalid clusters as well to access the objects in a cluster using bounding box and childsibling
        }
    }

    dataOut.numClusters = dataOut.clusters.length;
    dataOut.dataSize = clusters.dataSize;

    return dataOut;
}

/**
 * finds representative location for every cluster
 */
mopsiClustering.prototype.setRepresentatives = function (clusters, clustersOrder, representType) {
    var i, k, t, dataIn;

    dataIn = this.dataIn;

    for (i = 0; i < clustersOrder.length; i++) {
        k = clustersOrder[i]; // cluster number
        switch (representType) {
            case "gridMiddle":
                this.setRepresentativeCellMiddle(clusters, k);
                break;

            case "first":
                t = clusters.clusters[k].group[0];
                clusters.clusters[k].represent[0] = dataIn[t].x; // center of the grid
                clusters.clusters[k].represent[1] = dataIn[t].y;
                break;

            case "mean":
            default:
                this.setRepresentativeMean(clusters, k);
                break;
        }
    }
}

/**
 * calculates the middle location of cell as representative location for cluster
 */
mopsiClustering.prototype.setRepresentativeCellMiddle = function (clusters, k) {
    var nC, nR, numColumn;

    numColumn = clusters.numColumn;

    nC = k % numColumn;
    nR = Math.floor(k / numColumn);
    clusters.clusters[k].represent[0] = Math.floor(this.params.minX + (nC * this.params.cellWidth) + this.params.cellWidth / 2); // center of the grid
    clusters.clusters[k].represent[1] = Math.floor(this.params.minY + (nR * this.params.cellHeight) + this.params.cellHeight / 2);
}

/**
 * calculates the average location of objects in a cluster as representative location
 */
mopsiClustering.prototype.setRepresentativeMean = function (clusters, k) {
    var tmpX, tmpY, j, n, t, dataIn;

    dataIn = this.dataIn;

    tmpX = 0;
    tmpY = 0;
    n = clusters.clusters[k].clusterSize;

    for (j = 0; j < n; j++) {
        t = clusters.clusters[k].group[j];
        tmpX += dataIn[t].x;
        tmpY += dataIn[t].y;
    }

    tmpX /= n;
    tmpY /= n;

    if (n === 1) {
        t = clusters.clusters[k].group[0];
        clusters.clusters[k].coordinates = [];
        clusters.clusters[k].represent[0] = Math.floor(tmpX);
        clusters.clusters[k].represent[1] = Math.floor(tmpY);
        clusters.clusters[k].coordinates[0] = dataIn[t].lat;
        clusters.clusters[k].coordinates[1] = dataIn[t].lng;
    } else {
        clusters.clusters[k].represent[0] = Math.floor(tmpX);
        clusters.clusters[k].represent[1] = Math.floor(tmpY);
    }
}

/**
 * calculates Euclidean distance between two points in cartezian space
 */
mopsiClustering.prototype.EucDistance = function (point1, point2) {
    var tempX, tempY;
    tempX = (point1.x - point2.x) * (point1.x - point2.x);
    tempY = (point1.y - point2.y) * (point1.y - point2.y);

    return Math.sqrt(tempX + tempY);
}

/**
 * check whether the disatce between two points is bigger than a threshold
 */
mopsiClustering.prototype.checkDist = function (point1, point2) {
    var dist = (point1.x - point2.x) * (point1.x - point2.x) + (point1.y - point2.y) * (point1.y - point2.y);
    var th = this.params.distMerge * this.params.distMerge;
    if (dist > th)
        return true;
    else
        return false;
}

/**
 * finds the cell number containing a point
 */
mopsiClustering.prototype.getCellNum = function (x, y, numColumn, numRow) {
    var row, column;
    var clusterNum;

    var maxX = this.params.maxX;
    var maxY = this.params.maxY;
    var minX = this.params.minX;
    var minY = this.params.minY;

    // photo is out of the map bounding box
    if (x > maxX || x < minX || y > maxY || y < minY)
        return -1;

    row = Math.floor((y - minY) / this.params.cellHeight);
    column = Math.floor((x - minX) / this.params.cellWidth);

    row = row < 0 ? 0 : row;
    row = row >= numRow ? numRow - 1 : row;
    column = column < 0 ? 0 : column;
    column = column >= numColumn ? numColumn - 1 : column;

    clusterNum = row * numColumn + column;
    return clusterNum;
}

/**
 * find the cell index of neighbour cluster k (0 to 8) in grid,
 *
 * the nubmers of 8 neighbours are shown as below
 * (index-numColumn-1)  (index-numColumn)  (index-numColumn+1)
 *     (index-1)          index          index+1
 * (index+numColumn-1)  (index+numColumn)  (index+numColumn+1)
 */
mopsiClustering.prototype.getNeighbourCellNum = function (k, index, numColumn) {
    var r = Math.floor(k / 3);
    var c = k % 3;
    var n;

    if (r == 0) {
        n = index - numColumn - 1 + c;
    } else if (r == 1) {
        n = index - 1 + c;
    } else if (r == 2) {
        n = index + numColumn - 1 + c;
    }

    return n;
}