var ELEMENTS = [];
var API_KEY = nrvbd_API_KEY;
var DEFAULT_MAP_ZOOM = 12;
var DEFAULT_MAP_CENTER = { "lat": 47.06067911694654, "lng": 2.3193421776980205 };
var MAP_STYLE_ID = '6e735f52d985fd6c';//'f6a6ae74ae22cb64';
var TMP_COLOR = '#db890f';
var MARKER_COLOR = '#005a01';
var MAP = null;
var MARKER_DRIVER_PATH = 'M12 2C8.13 2 5 5.13 5 9c0 3.87 3.13 7 7 7s7-3.13 7-7c0-3.87-3.13-7-7-7zm0 12.5a5.5 5.5 0 1 1 0-11 5.5 5.5 0 0 1 0 11zm0-1.5a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm2-4h2m-6 0H8m3.5-3.5v-2m0 6v2';
var MARKER_DESTINATION_PATH = 'M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5S13.38 11.5 12 11.5z';
var MARKER_ICON_ANCHOR_X = 13;
var MARKER_ICON_ANCHOR_Y = 13;
var MARKERS_DRIVERS = [];
var MARKERS_DESTINATIONS = [];
var BOUNDS;
var CURRENT_DRIVER_SELECTED_ID = null;
var LIMITE_DISTANCE_METERS = 15000;
var FROMTO = {};
var DIRECTIONLINES_FROMTO = {};
var SAVE = [];
var tmp_points = [];

// formule HAVERSINE
function calculerDistance(lat1, lng1, lat2, lng2) {
    const earthRadius = 6371; // Rayon moyen de la Terre en kilomètres

    const radianLat1 = (Math.PI / 180) * lat1;
    const radianLng1 = (Math.PI / 180) * lng1;
    const radianLat2 = (Math.PI / 180) * lat2;
    const radianLng2 = (Math.PI / 180) * lng2;

    const dLat = radianLat2 - radianLat1;
    const dLng = radianLng2 - radianLng1;

    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) + Math.cos(radianLat1) * Math.cos(radianLat2) * Math.sin(dLng / 2) * Math.sin(dLng / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

    const distance = earthRadius * c * 1000; // Distance en mètres

    return distance;
}

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
    let color = element.color;
    let html = '';
    html += '<div class="driver driver-'+id+'" data-driver="'+id+'" id="driver-'+id+'">';
    html += '<div class="btn-container" >';
    html += '<button class="btn-driver" id="btn-driver-'+id+'" data-element="'+id+'" style="color:'+color+';border-color:'+color+';">'+nom+'<span class="material-symbols-outlined">pin_drop</span></button>';
    html += '<button class="btn-label nrvbd-button on" id="btn-label-'+id+'" data-element="'+id+'" style="background:'+color+'" data-label="true" title="Cacher ses labels de commandes">'
			+  '<span class="material-symbols-outlined off nrvbd-fs-2-i" style="vertical-align: bottom;">label_off</span>'
			+  '<span class="material-symbols-outlined on nrvbd-fs-2-i" style="vertical-align: bottom;">label</span>'
			+'</button>';
    html += '<button class="btn-hide nrvbd-button on" id="btn-hide-'+id+'" data-element="'+id+'" data-hidden="false" style="background:'+color+'">Cacher</button>';
    html += '</div>';
    html += '<div class="location-container locations-driver-'+id+' sortable" data-driver="'+id+'">';
    html += '</div>';
    html += '<div class="infos-container infos-'+id+'">';
    html += '<div>Distance : <span class="distance-total">0.00 metres</span></div>';
    html += '<div>Durée : <span class="duration-total">0 min</span></div>';
    html += '<div></div>';
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

function getMarkerByElementId(elementId) {
    let marker = MARKERS_DRIVERS.find( (m) => {
        let item = m.itemObject || {};
        return item.id == elementId;
    });
    if (!marker) {
        marker = MARKERS_DESTINATIONS.find( (m) => {
            let item = m.itemObject || {};
            return item.id == elementId;
        });
    }
    return marker;
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
function getElementById(id){
    return ELEMENTS.find( (m) => {
        return m.id == id;
    });
}
function getConcernedElements(driverId) {
    return ELEMENTS.filter((element) => {
        return element.id == driverId || element.attributed.id == driverId;
    });
}
function getDriverElements(driverId) {
    return ELEMENTS.filter((element) => {
        return element.type == 'adresse' || element.attributed.id == driverId;
    });
}
function getDriverElementsHtml(driverId) {
    return $('.row-destination','.locations-driver-'+driverId);
}
function getConcernedDestinations(driverId) {
    return ELEMENTS.filter((element) => {
        return element.type == 'adresse' && element.attributed.id == driverId;
    });
}
function getSelectedDriverElementById(driverId) {
    return ELEMENTS.find((element) => {
        return element.id == driverId;
    });
}
function getNonAttributedAdresses() {
    return ELEMENTS.filter( (element) => {
        return element.type == 'adresse' && element.attributed == false;
    });
}

function changeBtnAndRowStatus(driverId, disabled){
    $("#btn-driver-"+driverId).prop('disabled', disabled);
    changeCurrentDriver(disabled ? null:driverId);
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
function animMarker(id) {
    var selectedMarker = getSelectedDestinationMarkerById(id);
    if (selectedMarker){
        selectedMarker.setAnimation(google.maps.Animation.BOUNCE);
        setTimeout(function(){
            selectedMarker.setAnimation(null);
        }, 1000);
    }
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
function changeTempsColorMarker(nearestId, elementNearest, color) {
    let index = getIndexFromDestinationMarkers(nearestId);
    if (index >= 0 && index < MARKERS_DESTINATIONS.length && MARKERS_DESTINATIONS[index]) {
        attributesNewColorToMarker(index, elementNearest, color);
    }
}
function showElement(elementNearest) {
    let nearestId = elementNearest.id;
    changeTempsColorMarker(nearestId, elementNearest, TMP_COLOR);
    animMarker(nearestId);
}
function getCurrentProposalElement(driverId) {
    let driverElementsTab = getDriverElements(driverId);
    let driverElementsHtml = getDriverElementsHtml(driverId);

    if (driverElementsHtml.length > 0 && driverElementsTab.length > 0) {
        let lastHtml = driverElementsHtml[ driverElementsHtml.length-1 ];
        let elementId = $(lastHtml).data('destination');
        return driverElementsTab.find( (element) => {
            return element.id == elementId;
        });
    }
    return getSelectedDriverElementById(driverId);
}
function disposeElements(driverId) {
    let currentElement = getCurrentProposalElement(driverId); // Adresse or Driver - FROM POS
    let nonAttributedAdresses = getNonAttributedAdresses();

    let distanceMin = Number.MAX_SAFE_INTEGER;
    let elementNearest = null;
    nonAttributedAdresses.forEach(adresse => {
        const distance = calculerDistance(currentElement.lat, currentElement.lng, adresse.lat, adresse.lng);
        if (distance < distanceMin && distance <= LIMITE_DISTANCE_METERS) {
            distanceMin = distance;
            elementNearest = adresse;
        }
    });
    if (elementNearest) {
        showElement(elementNearest);
    }
}
function changeCurrentDriver(driverId, anim) {
    setCurrentDriverSelected(driverId);
    if (anim) {
        animDriverMarker(driverId);
    }
    if (driverId){
        disposeElements(driverId);
    }
}
function markerDriverClickHandler(marker) {
    let itemObject = marker.itemObject;
    let driverId = itemObject.id;
    changeCurrentDriver(driverId);
}
function addMarkerDriverClickListener(marker) {
    google.maps.event.addListener(marker, 'click', function() {
        markerDriverClickHandler(marker)
    });
}
function buttonDriverClickListeners() {
    $(document).on('click', "#btn-initBounds", function(){
       if (MAP && BOUNDS) {
           MAP.fitBounds(BOUNDS);
       }
       changeCurrentDriver(null);
       removeAllMarkers();
       createMarkersAndBoundsMap();
    });
    $(document).on('click', "#btn-hidelabels", function(){
        $(".map-label").toggle();
		$("#btn-hidelabels").toggleClass("nrvbd-button-success");
		$("#btn-hidelabels").toggleClass("nrvbd-button-danger");
    });
    $(document).on('click', '.btn-driver', function(ev){
        let driverId = $(ev.target).data('element');
        changeCurrentDriver(null);
        if (driverId && driverId != '') {
            changeCurrentDriver(driverId, true)
        }
    });
    $(document).on('click', '.btn-hide', function(ev){
		let target = $(ev.target);
		if(!target.is('button')){
			target = target.parents('button');
		}
        let hidden = $(target).data('hidden');
        let driverId = $(target).data('element');
        if (getConcernedDestinations(driverId).length > 0) {
            if (hidden == true) {
                updateElements(target, driverId, false, 'Cacher', true, false);
            } else {
                updateElements(target, driverId,true, 'Afficher', false, true);
            }
			$(target).toggleClass("on");
			$(target).toggleClass("off");
        }
    });
    $(document).on('click','.btn-label', function(ev){
		let target = $(ev.target);
		if(!target.is('button')){
			target = target.parents('button');
		}
		let displayLabels = $(target).data('label');
		let driverId = $(target).data('element');
		let concernedElements = getConcernedDestinations(driverId);
		if (concernedElements.length > 0) {
			concernedElements.forEach( (el) => {
			$(".map-label-"+el.id).toggle();
			});
			$(target).toggleClass("on");
			$(target).toggleClass("off");
		}
    });
    $(document).on('click','#submit-btn', function(e){
	   e.preventDefault();
       submitElements();
    });
	$(document).on('click','#save-btn', function(e){
		e.preventDefault();
		saveAsDraft();
	});
    $(document).ready( () => {
      const groups = document.querySelectorAll('.sortable');
      groups.forEach(group => {
        new Sortable(group, {
          //group: 'shared',
          handle: '.btn-drag',
          invertSwap: true,
          animation: 150,
          onStart: (evt) => {
            tmp_points = getPoints($(evt.from).data('driver'));
          },
          onEnd: (evt) => {
            changeRouteDisposition(evt)
          }
        });
      });
    });
}
function changeRouteDisposition(evt) {
  let driverDroppedGroup = evt.to;
  let draggedRow = evt.item;
  let driverId = $(driverDroppedGroup).data('driver');

  setCurrentDriverSelected(driverId);
  removeDistanceAndTime(driverId)
  removeGlobaleDistanceAndTime(driverId)
  removeDirectionLines(tmp_points);
  recomposeDirectionLines(driverId, true);
  tmp_points = [];
}
function btnRmvDestinationClickListener() {
    $(document).on('click', '.btn-rmv-destination', function(ev){
        let btn = $(ev.target).is('button') ? $(ev.target) : $(ev.target).parents('button');
        let destinationId = btn.data('destination');
        let driverId = btn.data('driver');
        if (destinationId && driverId) {
            let destMarker = getSelectedDestinationMarkerById(destinationId);
            decomposeDirectionLines(driverId);
            removeMarkerColor(destMarker.itemObject);
            removeDestinationToDriver(destMarker.itemObject);
            checkElementsForSubmitButton();
            setCurrentDriverSelected(driverId);
            recomposeDirectionLines(driverId, true);
        }
    });
}
function getlabelMarkerContent(element, defaultColor) {
    if (!defaultColor) {
        defaultColor = MARKER_COLOR;
    }
    let content = document.createElement('div');
    content.className = "map-label map-label-"+element.id;
    content.setAttribute('style', 'background: white; padding: 2px; border: 1px solid '+defaultColor+';');
    let timeLabel = document.createElement('div');
    timeLabel.innerText = element.nom;
    timeLabel.setAttribute('style', 'font-weight: bold; font-size: 11px; color: '+defaultColor+';');
    content.appendChild(timeLabel);
    return content;
}
function removeMapMarkerByIndex(index) {
  MARKERS_DESTINATIONS[index].setMap(null);
}
function createMarkerOptions (element, icon) {
  return {
    map: MAP,
    position: element.position,
    icon: icon,
    itemObject: element
  };
}
function createMarker(markerOptions, element, color) {
  //markerOptions.labelAnchor = new google.maps.Point(-30, -30);
  markerOptions.labelContent = getlabelMarkerContent(element, color);
  let marker = new markerWithLabel.MarkerWithLabel(markerOptions);
  addMarkerDestinationClickListener(marker);
  return marker;
}
function attributesNewColorToMarker(index, element, defaultColor) {
    removeMapMarkerByIndex(index);
    let markerOptions = createMarkerOptions(element, createIconForDest(defaultColor));
    let marker = createMarker(markerOptions, element, defaultColor);
    MARKERS_DESTINATIONS[index] = marker;
}
function attributesDriverColorToMarker(index, element) {
    removeMapMarkerByIndex(index);
    let driverSelected = getSelectedDriverMarkerById(CURRENT_DRIVER_SELECTED_ID);
    let markerOptions = createMarkerOptions(element, createIconForDest(driverSelected.itemObject.color))
    let marker = createMarker(markerOptions, element, driverSelected.itemObject.color);
    MARKERS_DESTINATIONS[index] = marker;
}
function reinitDriverColorToMarker(index, itemObject) {
    removeMapMarkerByIndex(index);
    itemObject.attributed = false;
    let markerOptions = createMarkerOptions(itemObject, createIconForDest(null));
    let marker = createMarker(markerOptions, itemObject, null);
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
        html += '<div class="row-destination row-destination-'+itemObject.id+' shared" id="row-destination-'+itemObject.id+'" data-destination="'+itemObject.id+'">';



        html += '<div class="left-part">';

        html += '<div class="row-destination-item">';
        html += '<button class="btn-drag" data-driver="'+CURRENT_DRIVER_SELECTED_ID+'" data-destination="'+itemObject.id+'"><span class="material-symbols-outlined">drag_indicator</span></button>';
        html += '</div>';
        html += '<div class="row-destination-item">'+itemObject.id+'</div>';
        html += '<div class="row-destination-item">'+itemObject.nom+'</div>';
        html += '<div class="row-destination-item distance"></div>';
        html += '<div class="row-destination-item duration"></div>';
        html += '</div>';

        html += '<div class="right-part">';
        html += '<div class="row-destination-item"><button class="btn-rmv-destination nrvbd-button-danger-outline" data-driver="'+CURRENT_DRIVER_SELECTED_ID+'" data-destination="'+itemObject.id+'"><span class="material-symbols-outlined">delete</span></button></div>';
        html += '</div>';

        html += '</div>';
        locationContainer.append(html);
    }
}
function timeInHourText(time) {
  if (time == 0) {
    return '0 min';
  }
  var reste = time;
  var nbJours = Math.floor(reste / (3600 * 24));
  reste -= nbJours * 24 * 3600;
  var nbHours = Math.floor(reste / 3600);
  reste -= nbHours * 3600;
  var nbMinutes = Math.floor(reste / 60);
  var nbSeconds = reste % 60;
  if (nbSeconds >= 30) {
    nbMinutes++;
  }
  var result = '';
  if (nbJours > 0) {
    result = result + nbJours + 'j ';
  }
  if (nbHours > 0) {
    result = result + nbHours + 'h ';
  }
  if (nbMinutes > 0 || (nbJours == 0 && nbHours == 0)) {
    result = result + nbMinutes + ' min';
  }
  return result.trim();
}
function displayDistance(totalDistance, p){
    let prefix = p ? 'Distance : ':'';
    let dist = prefix + (totalDistance > 2000 ? (totalDistance/1000).toFixed(2) + ' Kms' : totalDistance.toFixed(2) + ' metres');
   return dist;
}
function displayDuration(totalDuration, p){
    let prefix = p ? 'Durée : ':'';
    return prefix + timeInHourText(totalDuration);
}
function displayDistanceAndDuration(toElement, totalDistance, totalDuration) {
    if (toElement.type == 'adresse') {
        let ctx = $('.row-destination-'+toElement.id);
        $('.distance',ctx).attr('data-distance',totalDistance);
        $('.duration', ctx).attr('data-duration', totalDuration);
        $('.distance',ctx).html(displayDistance(totalDistance, true));
        $('.duration',ctx).html(displayDuration(totalDuration, true));
    }
}
function removeDestinationToDriver(itemObject) {
    if (itemObject.type == 'adresse') {
        $('#row-destination-'+itemObject.id).remove();
    }
}

function getDistanceAndTimeToGo(fromElement, toElement) {
    let fromMarker = getMarkerByElementId(fromElement.id);
    let toMarker = getMarkerByElementId(toElement.id);
    let fromPos = {         lat : fromMarker.position.lat(),        lng : fromMarker.position.lng()    };
    let toPos = {        lat : toMarker.position.lat(),        lng : toMarker.position.lng()    };
    calculateAndDisplayRoute(fromPos, toPos, fromElement, toElement);
}
function calculateAndDisplayRoute(fromPos, toPos, fromElement, toElement) {
    let fromId = fromElement.id, toId = toElement.id;
    const selectedMode = "DRIVING"; // "WALKING" | "BICYCLING" | "TRANSIT"
    let currentDriver = getSelectedDriverElementById(CURRENT_DRIVER_SELECTED_ID);

    if (FROMTO[fromId] && FROMTO[fromId][toId]) {
        let stockedResponse = FROMTO[fromId][toId].response;
        createDirectionLine(stockedResponse, currentDriver, fromElement, toElement);
        calculateDrivingDistanceAndTime(stockedResponse, toElement);
        //console.log('stocked response : '+ fromId +' '+toId);
        return ;
    }
    return directionsService.route(
        {
            origin: fromPos,
            destination: toPos,
            travelMode: google.maps.TravelMode[selectedMode],
        },
        (response, status) => {
            if (status === "OK") {
                FROMTO[fromId] = {};
                FROMTO[fromId][toId] = { response };
                createDirectionLine(response, currentDriver, fromElement, toElement);
                calculateDrivingDistanceAndTime(response, toElement);
            } else {
                console.error("Directions request failed due to " + status);
            }
            //console.log('new response : '+ fromId +' '+toId);
        }
    );
}
function removeDirectionLine(fromPointId, toPointId){
    //console.log(fromPointId, toPointId);
    if (DIRECTIONLINES_FROMTO[fromPointId] && DIRECTIONLINES_FROMTO[fromPointId][toPointId]) {
        DIRECTIONLINES_FROMTO[fromPointId][toPointId].setMap(null);
        delete DIRECTIONLINES_FROMTO[fromPointId][toPointId];
    }
}
function createDirectionLine(directionResult, currentDriver, fromElement, toElement) {
    let directionLine = new google.maps.Polyline({
        path: directionResult.routes[0].overview_path,
        strokeColor: currentDriver.color,
        strokeOpacity: 1,
        strokeWeight: 1
    });

    DIRECTIONLINES_FROMTO[fromElement.id]={};
    DIRECTIONLINES_FROMTO[fromElement.id][toElement.id] = directionLine;
    directionLine.setMap(MAP);
}
function calculateDrivingDistanceAndTime(directionsResult, toElement){
    var legs = directionsResult.routes[0].legs;
    let totalDistance = 0, totalDuration = 0;
    for(var i=0; i<legs.length; ++i) {
        totalDistance += legs[i].distance.value;
        totalDuration += legs[i].duration.value;
    }
    displayDistanceAndDuration(toElement, totalDistance, totalDuration);
}
function removeGlobaleDistanceAndTime(driverId) {
  let ctxInfos = $('.infos-'+driverId);
  $('.distance-total',ctxInfos).html(displayDistance(0));
  $('.duration-total',ctxInfos).html(displayDuration(0));
}
function calculateGlobaleDistanceAndTime(driverId) {

    let ctx = $(".locations-driver-"+driverId);
    let globaleDistance = 0;
    let globaleDuration = 0;
    $(".row-destination", ctx).each( (i, el) => {
        let row = $(el);
        let dest = row.data('destination');
        let distanceRow = $(".distance", row).attr('data-distance');
        let durationRow = $(".duration", row).attr('data-duration');
        if (distanceRow && distanceRow != '') {
          globaleDistance += parseInt(distanceRow, 10);
        }
        if (durationRow && durationRow != '') {
          globaleDuration += parseInt(durationRow, 10);
        }
    });

    let ctxInfos = $('.infos-'+driverId);
    $('.distance-total',ctxInfos).html(displayDistance(globaleDistance));
    $('.duration-total',ctxInfos).html(displayDuration(globaleDuration));
}
function getPoints(driverId){
  let ctx = $(".locations-driver-"+driverId);
  let points = [ driverId ];
  $('.row-destination', ctx).each( (i, el) => {
    points.push( $(el).data('destination') );
  });
  return points;
}
function removeDirectionLines(points) {

  points.reduce((accumulation, valeurActuelle, index, tableau) => {
    fromPointId = valeurActuelle;
    toPointId = tableau[index + 1] ? tableau[index + 1] : null;
    removeDirectionLine(fromPointId,toPointId );
    return accumulation;
  }, []);
}
function removeDistanceAndTime(driverId) {
  let ctx = $(".locations-driver-"+driverId);
  $('.distance',ctx).attr('data-distance',0);
  $('.duration', ctx).attr('data-duration', 0);
  $('.distance',ctx).html(displayDistance(0));
  $('.duration',ctx).html(displayDuration(0));
}
function decomposeDirectionLines(driverId) {
    let ctx = $(".locations-driver-"+driverId);
    let points = [ driverId ];
    removeDistanceAndTime(driverId)


    $('.row-destination', ctx).each( (i, el) => {
        points.push( $(el).data('destination') );
    });
    points.reduce((accumulation, valeurActuelle, index, tableau) => {
            fromPointId = valeurActuelle;
            toPointId = tableau[index + 1] ? tableau[index + 1] : null;
            removeDirectionLine(fromPointId,toPointId );
        return accumulation;
    }, []);
}
function recomposeDirectionLines(driverId, globaleCalculation) {
    let ctx = $(".locations-driver-"+driverId);
    let points = [ driverId ];
    $('.row-destination', ctx).each( (i, el) => {
        points.push( $(el).data('destination') );
    });
    points.reduce((accumulation, valeurActuelle, index, tableau) => {
        fromPointId = valeurActuelle;
        toPointId = tableau[index + 1] ? tableau[index + 1] : null;
        if (fromPointId && toPointId) {
            let fromElement = getElementById(fromPointId);
            let toElement = getElementById(toPointId);
            getDistanceAndTimeToGo(fromElement, toElement);
        }
        return accumulation;
    }, []);
    if (globaleCalculation) {
      (function(driverIdClosed){
        setTimeout(()=>{
          calculateGlobaleDistanceAndTime(driverIdClosed);
        },500);
      })(driverId);
    }
}
function markerDestinationClickHandler(marker, globaleCalculation) {
    if (CURRENT_DRIVER_SELECTED_ID) {
        let itemObject = marker.itemObject || {};
        let itemObjectId = itemObject.id;
        let elementIndex = getIndexOfSelectedElementById(itemObjectId);
        if (ELEMENTS[elementIndex].attributed === false) {
            let fromElement = getCurrentProposalElement(CURRENT_DRIVER_SELECTED_ID);
            let toElement = ELEMENTS[elementIndex];
            changeElementDriverAttribution(elementIndex, itemObject);
            changeMarkerColor(itemObject);
            addDestinationToDriver(itemObject);
            getDistanceAndTimeToGo(fromElement, toElement);
        } else {
            let driverId = itemObject.attributed.id;
            setCurrentDriverSelected(driverId);
            decomposeDirectionLines(driverId);
            resetElementDriverAttribution(elementIndex, itemObject);
            removeMarkerColor(itemObject);
            removeDestinationToDriver(itemObject);
            recomposeDirectionLines(driverId, false);
        }
        checkElementsForSubmitButton();
        if (globaleCalculation) {
          (function(driverIdClosed){
            setTimeout(()=>{
              calculateGlobaleDistanceAndTime(driverIdClosed);
            },500);
          })(CURRENT_DRIVER_SELECTED_ID);
        }
    } else {
        $('.label-drivers').addClass('selected');
    }

}
function addMarkerDestinationClickListener(marker) {
    google.maps.event.addListener(marker, 'click', function() {
        let selectedMarker = getSelectedDestinationMarkerById( marker.itemObject.id );
        markerDestinationClickHandler(selectedMarker, true);
    });
}


function createIconForDest(color) {
    return {
        path: MARKER_DESTINATION_PATH,
        fillColor: color ? color : MARKER_COLOR,
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
        let icon = createIconForDest(defaultColor);

        if (element.type == 'driver') {
            defaultColor = element.color;
            icon = createIconForDriver(element.color);
        }

        let markerOptions = {
            map: MAP,
            position: element.position,
            icon: icon,
            itemObject: element
        }

        //markerOptions.labelAnchor = new google.maps.Point(-30, -30);
        markerOptions.labelContent = getlabelMarkerContent(element, defaultColor);

        return new markerWithLabel.MarkerWithLabel(markerOptions);
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
    return Promise.resolve();
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
        return createMarkersAndBoundsMap().then( () => {
            remakeSavedObject();
        });
    }).catch( (e) => { console.error(e)});
}
function initMap(mapstyleid) {
    return new Promise( (resolve, reject) => {
        MAP = new google.maps.Map(document.getElementById("googleMap"), {
            zoom: DEFAULT_MAP_ZOOM,
            center: DEFAULT_MAP_CENTER,
            streetViewControl: false,
            mapTypeControl: false,
            mapId: mapstyleid,
            clickableIcons: false,
        });
        MAP.addListener('click', function(event) {
            event.stop(); // Empêchez la propagation de l'événement
        });
        return resolve();
    });
}
function launchMap(mapstyleid) {
    geocoder = new google.maps.Geocoder();
    directionsService = new google.maps.DirectionsService();
    return initMap(mapstyleid).then( () => {
        return initMarkers();
    }).catch( (e) => { console.error(e)});;
}

loadGoogleApiAndJson(API_KEY, MAP_STYLE_ID)
    .then( () => {
        createDriverForms();
        launchMap(MAP_STYLE_ID).catch( (e) => { console.error(e)});
    });





function saveAsDraft() {
	SAVE = [];
	despawnMessage();
	$('.nrvbd-loader').addClass('active');
	$(".driver").each( (i, el) => {
		let ctx = $(el);
		let driverId = ctx.data('driver');
		let adresses = [];
		$('.row-destination', ctx).each( (ir, elrow) => {
			let adresseId = $(elrow).data('destination');
			adresses.push( { adresse : adresseId, order : ir } );
		});
		SAVE.push({ driver : driverId, adresses : adresses });
	});

	let data = {
		'action': 'nrvbd-save-shipping-map',
		'data': JSON.stringify(SAVE),
		'date': $('#nrvbd-selected-date').val()
	};
	let call_success = (res) => {
		$('.nrvbd-loader').removeClass('active');
		spawnMessage(res.data.message, 'success');
	};
	let call_error = (xhr, status, error) => {
		spawnMessage(xhr.responseJSON.data.message ?? 'Error', 'error');
		console.error(xhr.responseJSON.data.message);	
		$('.nrvbd-loader').removeClass('active');
	};

	nrvbd_ajax(nrvbd_shipping_ajax, 
			   'POST', 
			   data,
			   call_success,
			   call_error);
}
function submitElements() {
    SAVE = [];

	despawnMessage();
	$('.nrvbd-loader').addClass('active');
    $(".driver").each( (i, el) => {
        let ctx = $(el);
        let driverId = ctx.data('driver');
        let adresses = [];
        $('.row-destination', ctx).each( (ir, elrow) => {
            let adresseId = $(elrow).data('destination');
            adresses.push( { adresse : adresseId, order : ir } );
        });
        SAVE.push({ driver : driverId, adresses : adresses });
    });

	
	let data = {
		'action': 'nrvbd-send-shipping',
		'data': JSON.stringify(SAVE),
		'date': $('#nrvbd-selected-date').val()
	};


	let call_success = (res) => {
		$('.nrvbd-loader').removeClass('active');
		let msg = res.data.message + '<br/>';
		msg += 'Total envoyés : ' + res.data.total_sent + '<br/>';
		msg += 'Total non envoyés : ' + res.data.total_failed;
		spawnMessage(msg, res.data.type);
	};

	let call_error = (xhr, status, error) => {
		spawnMessage(xhr.responseJSON.data.message ?? 'Error', 'error');
		console.error(xhr.responseJSON.data.message);	
		$('.nrvbd-loader').removeClass('active');
	}
	nrvbd_ajax(nrvbd_shipping_ajax, 
			   'POST', 
			   data,
			   call_success,
			   call_error);
}
function remakeSavedObject() {
    let saved = nrvbd_shipping_draft;
    if (saved) {
        saved.forEach( (driverObj) => {
            let driverElement = getElementById(driverObj.driver);
            if (driverElement) {
                setCurrentDriverSelected(driverElement.id);
                let driverAdresses = driverObj.adresses || [];
                driverAdresses.forEach( (adresseObj) => {
                    let marker = getMarkerByElementId(adresseObj.adresse);
                    if (marker) {
                        markerDestinationClickHandler(marker, false);
                    }
                });
                (function(driverIdClosed){
                  setTimeout(()=>{
                    calculateGlobaleDistanceAndTime(driverIdClosed);
                  },500);
                })(driverElement.id);

            }
        });
        setCurrentDriverSelected(null);
    }
}

function spawnMessage(message = "", type="success")
{
	$('#message-area').removeClass('notice-success');
	$('#message-area').removeClass('notice-error');
	$('#message-area').removeClass('notice-info');
	$('#message-area').removeClass('notice-warning');
	$('#message-area').addClass('notice-'+type);
	$('#message-area').html(message);
	$('#message-area').show(300);
}

function despawnMessage()
{
	$('#message-area').hide(300);
}