
function mapX(map){
  this.map = map;
  this.myClickListener = null;
  
  this.overlay = new google.maps.OverlayView();
  this.overlay.draw = function() {};
  this.overlay.setMap(this.map);
  
  this.infoWindow = new google.maps.InfoWindow({zIndex:1010});
  this.infoWindow.index = -1;
  // associative array for overlays
  this.overlays = new Array(); 
  this.pendingInfo=null;
  this.zIndex=0;
  this.closeInfoWindowByClick = true;
  this.selectedMarker = null;
  
  var me=this;
  me.myClickListener = google.maps.event.addListener(this.map, "click", function() {
    setTimeout(function() {
      var temp = me.closeInfoWindowByClick;
      if ( temp ) {// because of conflict with clicking on a label
        me.closeInfoWindow();
      }
      me.closeInfoWindowByClick = true;
    }, 100);
  });
  
  google.maps.event.addListener(this.infoWindow,'closeclick',function(){
     me.closeInfoWindowByClick = true;
     me.infoWindow.isOpen = false;
  });
}

mapX.prototype.addToOverlays = function(overlay)
{
  if ( this.overlays[overlay.type] == null ) {
    this.overlays[overlay.type] = new Array();
  }
  this.overlays[overlay.type].push(overlay);
}

mapX.prototype.removeOverlays = function() 
{
  this.removeMarkersWithType(CLUSTER); 
}

//methods
mapX.prototype.getZIndex = function(){
	this.zIndex++;
	return this.zIndex-1;
}
mapX.prototype.setCenter = function(latlng){
	this.map.setCenter(latlng);
}
mapX.prototype.getCenter = function(){
	return this.map.getCenter();
}

mapX.prototype.setOptions=function(options){
	this.map.setOptions(options);
}

mapX.prototype.getLatLngFromPoint=function(point)
{
  var latLng, point1, lat, lon;
  var zoomlevel = this.getZoom();
  point1 = {};
  
  point1.x = point.x / Math.pow(2, zoomlevel);
  point1.y = point.y / Math.pow(2, zoomlevel);
    
  latLng = this.map.getProjection().fromPointToLatLng(new google.maps.Point(  point1.x,  point1.y));

  return latLng;
}

mapX.prototype.getPointFromLatLng=function(lat, lng)
{
  var point;
  var zoomlevel = this.getZoom();
  
  point = this.map.getProjection().fromLatLngToPoint(new google.maps.LatLng(lat,lng));
  point.x = point.x * Math.pow(2, zoomlevel);
  point.y = point.y * Math.pow(2, zoomlevel);
  point.x = Math.floor(point.x);
  point.y = Math.floor(point.y);
 
  return point;
}

mapX.prototype.setBoundsFromMarkers = function(){
	var bounds=null;
	for(var i in this.overlays){
		for(var j=0;j<this.overlays[i].length;j++){
			try{
				var latlng=this.overlays[i][j].getPosition();
				if(bounds==null){
					bounds = new google.maps.LatLngBounds (latlng,latlng);
				}else{
					bounds.extend (latlng);
				}
			}catch(err){}
		}
	}
	if(bounds!=null && !bounds.getSouthWest().equals(bounds.getNorthEast())){
		this.setBounds(bounds);
	}
}

mapX.prototype.setBoundsFromLatLngBoundingBox = function(minLat, maxLat, minLng, maxLng)
{
  var bounds=null;
  var latlng, j;
  
  latlng = new google.maps.LatLng(minLat, minLng);
  bounds = new google.maps.LatLngBounds (latlng,latlng);
  latlng = new google.maps.LatLng(maxLat, maxLng);
  bounds.extend(latlng);
  
  alert("x " + minLat+" "+maxLat+" "+minLng+" " +maxLng);
  
  if ( bounds != null )
    this.setBounds(bounds);
}

mapX.prototype.setBoundsFromIndexes = function(markersData, indexes){
  var bounds=null;
  var latlng, j;
  
  var minLat, maxLat, minLon, maxLon;
  minLat = 1000.0;
  maxLat = -1000.0;
  minLon = 1000.0;
  maxLon = -1000.0;

  for(var i = 0 ; i < indexes.length ; i++){
    try{
      j = indexes[i];
      latlng = new google.maps.LatLng(markersData[j].lat, markersData[j].lon);
      
      if ( markersData[j].lat < minLat )
        minLat = markersData[j].lat;
      if ( markersData[j].lat > maxLat ) 
        maxLat = markersData[j].lat;
        
      if ( markersData[j].lon < minLon )
        minLon = markersData[j].lon;
      if ( markersData[j].lon > maxLon )
        maxLon = markersData[j].lon;
	  
      if(bounds==null){
      	bounds = new google.maps.LatLngBounds (latlng,latlng);
      }else{
      	bounds.extend(latlng);
      }
    }catch(err){}
  }
  
  // alert("y " + minLat+" "+maxLat+" "+minLon+" " +maxLon);
  
  if(bounds!=null )
  	this.setBounds(bounds);
}

mapX.prototype.setBoundsFromData = function(markersData){
  var bounds=null;
  var latlng;
  var data = markersData;

  for(var i = 0 ; i < data.length ; i++){
    try{
      latlng = new google.maps.LatLng(data[i].lat, data[i].lon);
	  
      if(bounds==null){
      	bounds = new google.maps.LatLngBounds (latlng,latlng);
      }else{
      	bounds.extend(latlng);
      }
    }catch(err){}
  }
  
  if(bounds!=null )
  	this.setBounds(bounds);
}

mapX.prototype.getBounds = function(){
	return this.map.getBounds();
}
mapX.prototype.getProjection = function(){
	return this.overlay.getProjection();
}
mapX.prototype.setBounds = function(bounds){
	this.map.fitBounds(bounds);
}
mapX.prototype.getZoom = function(){ 
	return this.map.getZoom();
}
mapX.prototype.setZoom = function(zoom){
	return this.map.setZoom(zoom);
}

mapX.prototype.addListener = function(event,callbackFunction)
{
  var mopsiMap=this;
  google.maps.event.addListener(this.map, event, function() {
    callbackFunction(mopsiMap)
  });
}
mapX.prototype.removeListener = function(type){
	google.maps.event.clearListeners(this.map, type);
}

mapX.prototype.removeMarkersWithType = function(type)
{
  if ( this.overlays[type] != undefined )
    while ( this.overlays[type].length != 0 ) {
      var overlay = this.overlays[type].pop();
      overlay.setMap(null);
    }
}

mapX.prototype.removeMarkersWithId=function(Ids) {

  if ( Ids == undefined )
    return;
  
  for ( ii = 0; ii < Ids.length; ii++ )
    for ( var i in this.overlays ) {
      for ( var j = this.overlays[i].length - 1; j >= 0; j-- ) {
        if ( this.overlays[i][j] == undefined ) {
          continue;
        }
        if ( Ids[ii] == this.overlays[i][j].idx ) {
          var overlay = this.overlays[i][j];
          this.overlays[i].splice(j, 1);
          overlay.setMap(null);
        }
      }
   }
}

mapX.prototype.getMarkerOnLatLng=function(latStamp, lngStamp) {
  for ( var i in this.overlays ) {
    for ( var j = this.overlays[i].length - 1; j >= 0; j-- ) {
      if ( this.overlays[i][j] == undefined ) 
        continue;
      if ( this.overlays[i][j].myLat == latStamp && this.overlays[i][j].myLng == lngStamp )
      	return this.overlays[i][j];
    }
  }
  return null;
}

mapX.prototype.removeClickListenerFromMap=function()
{
  if ( this.myClickListener != null ) {
    google.maps.event.removeListener(this.myClickListener);
    this.myClickListener = null;
  }
}

mapX.prototype.addClickListenerToMap=function()
{
  removeClickListenerFromMap();
  this.myClickListener = google.maps.event.addListener(this.map, "click", function () {
	setTimeout(function(){
      if ( this.closeInfoWindowByClick ) {
        this.closeInfoWindow();
      }
      this.closeInfoWindowByClick = true;
	},100);
  });
}

mapX.prototype.closeInfoWindow = function()
{
  this.infoWindow.close();
  this.infoWindow.isOpen = false;
}

mapX.prototype.hvs = function(lat1,lat2,lng1,lng2)
{
  var earthRadius = 3958.75;
  var dLat = (lat2 - lat1) * Math.PI / 180;
  var dLng = (lng2 - lng1) * Math.PI / 180;
  var a = Math.sin(dLat/2) * Math.sin(dLat/2) +
  			   Math.cos((lat1) * Math.PI / 180) * Math.cos((lat2) * Math.PI / 180) *
  			   Math.sin(dLng/2) * Math.sin(dLng/2);
  var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
  var dist = earthRadius * c;
     
  var meterConversion = 1609;
     
  return dist * meterConversion;
}