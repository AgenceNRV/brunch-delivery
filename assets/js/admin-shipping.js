var ELEMENTS = [];
var API_KEY = 'AIzaSyDd4O5Px5rp93hmudEebgX0Y1Y5Rs6Myq8';
var DEFAULT_MAP_ZOOM = 12;
var DEFAULT_MAP_CENTER = { "lat": 47.06067911694654, "lng": 2.3193421776980205 };
var MAP_STYLE_ID ='f6a6ae74ae22cb64';
var MAP = null;
var MARKER_DRIVER_PATH = 'M12 2C8.13 2 5 5.13 5 9c0 3.87 3.13 7 7 7s7-3.13 7-7c0-3.87-3.13-7-7-7zm0 12.5a5.5 5.5 0 1 1 0-11 5.5 5.5 0 0 1 0 11zm0-1.5a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm2-4h2m-6 0H8m3.5-3.5v-2m0 6v2';
var MARKER_DESTINATION_PATH = 'M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5S13.38 11.5 12 11.5z';
var MARKER_ICON_ANCHOR_X = 13;
var MARKER_ICON_ANCHOR_Y = 13;
var MARKERS_DRIVERS = [];
var MARKERS_DESTINATIONS = [];
var BOUNDS;
var CURRENT_DRIVER_SELECTED_ID = null;

function loadJson() {
    return Promise.resolve().then( () => {
        if (ELEMENTS.length === 0) {
            ELEMENTS = JSON.parse(nrvbd_shipping_data);
            ELEMENTS.map((e) => { e.show = true; e.attributed = false; });
        }
        return true;
    });
    /*return fetch('resources/elements.json')
        .then(response => response.json())
        .then(data => {
            ELEMENTS = data;
            ELEMENTS.map((e) => { e.show = true; e.attributed = false; });
            return true;
        })
        .catch(error => console.error('Erreur lors du chargement du fichier JSON:', error));*/
}
function loadGoogleApiAndJson(key, mapstyleid){
    let url = 'https://maps.googleapis.com/maps/api/js?key='+key+'&libraries=geometry&callback=loadJson';
    if ( mapstyleid && mapstyleid !== '' ) {
        url += "&map_ids="+mapstyleid;
    }
    return $.ajax({
        url: url,
        dataType: "script",
        async: true
    }).then( () => {
        return loadJson();
    });
}
function createDriverForm(element) {
    let container = $("#container-drivers");
    let id = element.id;
    let nom = element.nom;
    let html = '';
    html += '<div class="driver driver-'+id+'" id="driver-'+id+'">';
    html += '<div class="btn-container" >';
    html += '<button class="btn-driver" id="btn-driver-'+id+'" data-element="'+id+'">'+nom+'</button>';
    html += '<button class="btn-hide" id="btn-hide-'+id+'" data-element="'+id+'" data-hidden="false">Cacher</button>';
    html += '</div>';
    html += '<div class="location-container locations-driver-'+id+'">';
    html += '</div>';
    html += '</div>';
    container.append(html);
}
function createDriverForms() {
    ELEMENTS.forEach( (element) => {
        if ( element.type == "driver") {
            createDriverForm(element);
        }
    });
    buttonDriverClickListeners();
    btnRmvDestinationClickListener();
}


function getSelectedDriverMarkerById(id){
    return MARKERS_DRIVERS.find( (m) => {
        let item = m.itemObject || {};
        return item.id == id;
    });
}
function getSelectedDestinationMarkerById(id){
    return MARKERS_DESTINATIONS.find( (m) => {
        let item = m.itemObject || {};
        return item.id == id;
    });
}
function getIndexOfSelectedElementById(id){
    return ELEMENTS.findIndex( (m) => {
        return m.id == id;
    });
}
function getConcernedElements(driverId) {
    return ELEMENTS.filter((element) => {
        return element.id == driverId || element.attributed.id == driverId;
    });
}
function getConcernedDestinations(driverId) {
    return ELEMENTS.filter((element) => {
        return element.type == 'adresse' && element.attributed.id == driverId;
    });
}
function getConcernedDrivers(driverId) {
    return ELEMENTS.filter((element) => {
        return element.type == 'driver' && element.attributed.id == driverId;
    });
}

function changeBtnAndRowStatus(driverId, disabled){
    $("#btn-driver-"+driverId).prop('disabled', disabled);
    setCurrentDriverSelected(disabled ? null:driverId);
    $(".row-destination .btn-rmv-destination", $(".locations-driver-"+driverId)).prop('disabled', disabled);
    $(".driver-"+driverId).css('opacity', disabled ? 0.5:1);
}
function displayCurrentDriverSelected(id){
    $('.label-drivers').removeClass('selected');
    $(".driver").removeClass('selected');
    $("#driver-"+id).addClass('selected');
}
function setCurrentDriverSelected(id) {
    CURRENT_DRIVER_SELECTED_ID = id;
    displayCurrentDriverSelected(id);
}
function animDriverMarker(id) {
    var selectedMarker = getSelectedDriverMarkerById(id);
    if (selectedMarker){
        MAP.panTo(selectedMarker.position);
        selectedMarker.setAnimation(google.maps.Animation.BOUNCE);
        setTimeout(function(){
            selectedMarker.setAnimation(null);
        }, 1000);
    }
}

function showConcernedElements(concernedElements, show) {
    for (let i = 0; i < ELEMENTS.length; i ++){
        let searchingId = ELEMENTS[i].id;
        let concernedFound = concernedElements.find( (cE) => {
            return cE.id == searchingId;
        });
        if (concernedFound) {
            ELEMENTS[i].show = show;
        }
    }
}
function updateElements(target, driverId, hidden, buttonText, showElements, btnRowStatus) {

    let concernedElements = getConcernedElements(driverId);
    if (concernedElements.length > 0) {
        $(target).data('hidden', hidden);
        $(target).text(buttonText);
        showConcernedElements(concernedElements, showElements);
        changeBtnAndRowStatus(driverId, btnRowStatus);
        removeAllMarkers();
        createMarkersAndBoundsMap();
    }

}
function checkElementsForSubmitButton() {
    let nb_adresse = 0, nb_attributed = 0;
    ELEMENTS.forEach( (element) => {
        if (element.type === 'adresse') {
            nb_adresse ++;
            if ( element.attributed !== false ) {
                nb_attributed ++;
            }
        }
    });
    $('#submit-btn').prop('disabled', !(nb_adresse > 0 ? nb_adresse == nb_attributed : false) );
}
function markerDriverClickHandler(marker) {
    let itemObject = marker.itemObject;
    let id = itemObject.id;
    setCurrentDriverSelected(id);
}
function addMarkerDriverClickListener(marker) {
    google.maps.event.addListener(marker, 'click', function() {
        markerDriverClickHandler(marker)
    });
}
function buttonDriverClickListeners() {
    $(document).on('click', '.btn-driver', function(ev){
        let driverId = $(ev.target).data('element');
        setCurrentDriverSelected(null);
        if (driverId && driverId != '') {
            setCurrentDriverSelected(driverId);
            animDriverMarker(driverId);
        }
    });
    $(document).on('click', '.btn-hide', function(ev){
        let hidden = $(ev.target).data('hidden');
        let driverId = $(ev.target).data('element');
        if (getConcernedDestinations(driverId).length > 0) {
            if (hidden == true) {
                updateElements(ev.target, driverId, false, 'Cacher', true, false);
            } else {
                updateElements(ev.target, driverId,true, 'Afficher', false, true);
            }
        }
    });
}
function btnRmvDestinationClickListener() {
    $(document).on('click', '.row-destination-item', function(ev){
        let btn = $(ev.target).is('button') ? $(ev.target) : $(ev.target).parents('button');
        let destinationId = btn.data('destination');
        if (destinationId) {
            let destMarker = getSelectedDestinationMarkerById(destinationId);
            removeMarkerColor(destMarker.itemObject);
            removeDestinationToDriver(destMarker.itemObject);
            checkElementsForSubmitButton();
        }
    });
}

function attributesDriverColorToMarker(index, element) {
    MARKERS_DESTINATIONS[index].setMap(null);
    let driverSelected = getSelectedDriverMarkerById(CURRENT_DRIVER_SELECTED_ID);
    let marker = new google.maps.Marker({
        map: MAP,
        position: element.position,
        icon: createIconForDest(driverSelected.itemObject.color),
        itemObject: element
    });
    addMarkerDestinationClickListener(marker);
    MARKERS_DESTINATIONS[index] = marker;
}
function reinitDriverColorToMarker(index, itemObject) {
    MARKERS_DESTINATIONS[index].setMap(null);
    itemObject.attributed = false;
    let marker = new google.maps.Marker({
        map: MAP,
        position: itemObject.position,
        icon: createIconForDest(null),
        itemObject: itemObject
    });
    addMarkerDestinationClickListener(marker);
    MARKERS_DESTINATIONS[index] = marker;
}
function getIndexFromDestinationMarkers(selectedId) {
    return MARKERS_DESTINATIONS.findIndex( (marker) => {
        if (marker && marker.itemObject && marker.itemObject.id == selectedId) {
            return marker
        }
    });
}
function changeMarkerColor(itemObject) {
    let index = getIndexFromDestinationMarkers(itemObject.id);
    if (index >= 0 && index < MARKERS_DESTINATIONS.length && MARKERS_DESTINATIONS[index]) {
        attributesDriverColorToMarker(index, itemObject);
    }
}
function removeMarkerColor(itemObject) {
    let index = getIndexFromDestinationMarkers(itemObject.id);
    if (index >= 0 && index < MARKERS_DESTINATIONS.length && MARKERS_DESTINATIONS[index]) {
        reinitDriverColorToMarker(index, itemObject);
    }
}
function changeElementDriverAttribution(elementIndex, itemObject){
    let driverSelected = getSelectedDriverMarkerById(CURRENT_DRIVER_SELECTED_ID);
    itemObject.attributed = driverSelected.itemObject;
    ELEMENTS[elementIndex] = itemObject;
}
function resetElementDriverAttribution(elementIndex, itemObject){
    itemObject.attributed = false;
    ELEMENTS[elementIndex] = itemObject;
}
function addDestinationToDriver(itemObject) {
    if (itemObject.type == 'adresse') {
        let locationContainer = $('.locations-driver-'+CURRENT_DRIVER_SELECTED_ID);
        let html = '';
        html += '<div class="row-destination row-destination-'+itemObject.id+'" id="row-destination-'+itemObject.id+'" data-destination="'+itemObject.id+'">';

        html += '<div class="left-part">';
        html += '<div class="row-destination-item">'+itemObject.nom+'</div>';
        html += '<div class="row-destination-item">'+itemObject.adresse+'</div>';
        html += '<div class="row-destination-item">'+itemObject.cp+'</div>';
        html += '<div class="row-destination-item">'+itemObject.ville+'</div>';
        html += '</div>';

        html += '<div class="right-part">';
        html += '<div class="row-destination-item"><button class="btn-rmv-destination" data-destination="'+itemObject.id+'">X</button></div>';
        html += '</div>';

        html += '</div>';
        locationContainer.append(html);
    }
}
function removeDestinationToDriver(itemObject) {
    if (itemObject.type == 'adresse') {
        $('#row-destination-'+itemObject.id).remove();
    }
}


function markerDestinationClickHandler(marker) {
    if (CURRENT_DRIVER_SELECTED_ID) {
        let itemObject = marker.itemObject || {};
        let itemObjectId = itemObject.id;
        let elementIndex = getIndexOfSelectedElementById(itemObjectId);
        if (ELEMENTS[elementIndex].attributed === false) {
            changeElementDriverAttribution(elementIndex, itemObject);
            changeMarkerColor(itemObject);
            addDestinationToDriver(itemObject);
        } else {
            resetElementDriverAttribution(elementIndex, itemObject);
            removeMarkerColor(itemObject);
            removeDestinationToDriver(itemObject);
        }
        checkElementsForSubmitButton();
    } else {
        $('.label-drivers').addClass('selected');
    }

}
function addMarkerDestinationClickListener(marker) {
    google.maps.event.addListener(marker, 'click', function() {
        let selectedMarker = getSelectedDestinationMarkerById( marker.itemObject.id );
        markerDestinationClickHandler(selectedMarker);
    });
}


function createIconForDest(color) {
    return {
        path: MARKER_DESTINATION_PATH,
        fillColor: color ? color :'#029505',
        fillOpacity: 1,
        anchor: new google.maps.Point(MARKER_ICON_ANCHOR_X,MARKER_ICON_ANCHOR_Y),
        strokeWeight: 0,
        scale: 1
    }
}
function createIconForDriver(color) {
    return {
        path: MARKER_DRIVER_PATH,
        fillColor: color,
        fillOpacity: 1,
        anchor: new google.maps.Point(MARKER_ICON_ANCHOR_X,MARKER_ICON_ANCHOR_Y),
        strokeWeight: 0,
        scale: 1
    }
}
function createGoogleMarker(element){
    if ( element.position && element.show ){

        let defaultColor = element.attributed !== false ? element.attributed.color : null;
        let icon = createIconForDest(defaultColor)
        if (element.type == 'driver') {
            icon = createIconForDriver(element.color)
        }

        return new google.maps.Marker({
            map: MAP,
            position: element.position,
            icon: icon,
            itemObject: element
        });
    }
    return null;
}
function createAndAddMarker(element){
    let marker = createGoogleMarker(element);
    if ( marker ){
        BOUNDS.extend(marker.position);
        if ( element.type == "driver") {
            addMarkerDriverClickListener(marker);
            MARKERS_DRIVERS.push(marker);
        } else {
            addMarkerDestinationClickListener(marker);
            MARKERS_DESTINATIONS.push(marker);
        }
    }
}

function removeDestinationMarkers() {
    MARKERS_DESTINATIONS.forEach( (m) => {
        m.setMap(null);
    });
    MARKERS_DESTINATIONS = [];
}
function removeDriverMarkers() {
    MARKERS_DRIVERS.forEach( (m) => {
        m.setMap(null);
    });
    MARKERS_DRIVERS = [];
}
function removeAllMarkers() {
    removeDriverMarkers();
    removeDestinationMarkers();
}
function createMarkersAndBoundsMap() {
    BOUNDS = new google.maps.LatLngBounds();
    ELEMENTS.forEach( (element) => {
        if (element.show) {
            createAndAddMarker(element);
        }
    });
    MAP.fitBounds(BOUNDS);
}

function searchLatLngForObject(object, index) {
    if(object && object.lat && object.lng && object.lat.length > 2 && object.lng.length > 2){
        // Use lat lng from object
        let lat = object.lat.trim();
        let lng = object.lng.trim();
        return Promise.resolve({ position: new google.maps.LatLng(lat, lng), fromApi: false  });
    }
    // search lat lng from google geocoder api
    // https://developers.google.com/maps/documentation/javascript/reference/geocoder
    return new Promise( (resolve, reject) => {
        geocoder.geocode( { 'address': object.name + ' ' +object.adresse + ' ' + object.codepostal + ' ' + object.ville }, function(results, status) {
            if (status === 'OK') {
                setTimeout(() => {
                    resolve( { status, position : results[0].geometry.location, fromApi: true });
                }, 600);
            } else {
                reject( { status , code : 'Geocode was not successful for the following reason' } );
            }
        });
    });
}
function createLatLngForObject(object, index){
    return searchLatLngForObject(object, index).then( (res) => {
        if( res.fromApi ){
            console.info(' "lat" : "' + res.position.lat() + '", "lng" : "' + res.position.lng() + '"', object.ville, ' id : ' + object.id + ' address : ' + object.adresse + ' ' + object.cp + ' ' + object.ville);
            // TODO : envoyer en api les coordonnées manquantes
            object.lat = res.position.lat().toString();
            object.lng = res.position.lng().toString();
        }
        if ( res.position ){
            object.position = res.position;
        }
    }).catch( (e) => { console.error(e)});;
}
function checkGeocodingItems(arrayItems) {
    if ( arrayItems.length == 0) {
        return Promise.resolve();
    }
    return new Promise( (resolve, reject) => {
        let index = 0, currentItem;

        function checkGeocodingOneMarker( currentItem, index ) {
            return createLatLngForObject( currentItem, index ).then( () => {
                relaunch();
            }).catch( (err) => {
                console.error(err.code);
                relaunch();
            });
        }
        function relaunch(){
            index++;
            if ( index < arrayItems.length ) {
                checkGeocodingOneMarker(arrayItems[index], index);
            }else{
                resolve();
            }
        }
        currentItem = arrayItems[index];
        checkGeocodingOneMarker(currentItem, index);
    });
}


function initMarkers() {
    return checkGeocodingItems(ELEMENTS).then( () => {
        createMarkersAndBoundsMap();
    }).catch( (e) => { console.error(e)});
}
function initMap() {
    return new Promise( (resolve, reject) => {
        MAP = new google.maps.Map(document.getElementById("googleMap"), {
            zoom: DEFAULT_MAP_ZOOM,
            center: DEFAULT_MAP_CENTER,
            streetViewControl: false,
            mapTypeControl: false,
            mapId: MAP_STYLE_ID
        });
        return resolve();
    });
}
function launchMap() {
    geocoder = new google.maps.Geocoder();
    directionsService = new google.maps.DirectionsService();
    return initMap().then( () => {
        return initMarkers()
    }).catch( (e) => { console.error(e)});;
}

loadGoogleApiAndJson(API_KEY, MAP_STYLE_ID)
    .then( () => {
        createDriverForms();
        launchMap().catch( (e) => { console.error(e)});
    });
/*




function createGoogleMarker(element){
  if ( element.position && element.show ){
    return new google.maps.Marker({
      map: MAP,
      position: element.position,
      icon: element.type === "driver" ? createIconForDriver(element.color) : createIconForDest() ,
      itemObject: element
    });
  }
  return null;
}








function markerDestinationClickHandler(marker) {
  if (CURRENT_DRIVER_SELECTED_ID) {
    let itemObject = marker.itemObject;
    if (itemObject.attributed === false) {
      changeMarkerColor(marker, itemObject);
      addDestinationToDriver(itemObject);
    } else {
      removeMarkerColor(marker, itemObject);
      removeDestinationToDriver(itemObject);
    }
  } else {
    $('.label-drivers').addClass('selected');
  }
}
function addMarkerDestinationClickListener(marker) {
  google.maps.event.addListener(marker, 'click', function() {
    let selectedMarker = getSelectedDestinationMarkerById( marker.itemObject.id );
    markerDestinationClickHandler(selectedMarker);
  });
}






function disabledConcernedRow(state, concernedDestId ) {
  $(".btn-rmv-destination","#row-destination-"+concernedDestId).prop('disabled', state);
}
function hideDestinationsAndDrivers(state, driver, concernedDestinations){
  MARKERS_DRIVERS.forEach( (marker) => {
    if (driver.id == marker.itemObject.id) {
      marker.itemObject.hide = state;
    }
  });
  concernedDestinations.forEach( (concernedDest) => {
    MARKERS_DESTINATIONS.forEach( (marker) => {
      if (concernedDest.id == marker.itemObject.id) {
        marker.itemObject.hide = state;
      }
    });
  });

}











*/


