let address1Field = document.querySelector("#address_1");
let address2Field = document.querySelector("#address_2");
let postalField = document.querySelector("#postcode");
let cityField = document.querySelector("#city");
let latitudeField = document.querySelector("#latitude");
let longitudeField = document.querySelector("#longitude");
let mapElement = document.querySelector("#imap");
var map = null;
let MarkerSelector;

jQuery(document).ready(function($) {

  function showMapAndFields(show){
    $(".imap-container")[show ? 'show':'hide']();
    if (show) {
      initMap();
    } else {
      resetMap();
    }

  }
  function centerChanged (){
    let center = map.getCenter();
    MarkerSelector.setPosition(center);
    latitudeField.value = center.lat();
    longitudeField.value = center.lng();
  }
  function initMap() {
    if (latitudeField.value != '' && longitudeField.value != '') {
      LatLng = { lat: parseFloat(latitudeField.value), 
				lng: parseFloat(longitudeField.value) };
    } else {
      LatLng = { lat: 43.6012626, lng: 1.437649 };
    }
    map = map || new google.maps.Map(mapElement, {
      center: LatLng,
      streetViewControl: false,
      mapTypeControl: false,
      zoom: 15,
    });
    MarkerSelector = MarkerSelector || new google.maps.Marker({
      position: LatLng,
      map
    });
    map.addListener('center_changed', centerChanged);
    map.addListener('drag', centerChanged);
  }
  function resetMap() {
    if (map) {
      map.addListener('center_changed', function (){});
      map.addListener('drag', function (){});
      map = null;
    }
    if (MarkerSelector) {
      MarkerSelector.setMap(null);
      MarkerSelector = null;
    }
  }

	$('#nrvbd-get-coordinates').click(function(e)
	{
		e.preventDefault();
		let showing = $(e.target).data('show');
		if (showing) {
			showMapAndFields(false);
			$(e.target).data('show', false);
		}else{
			showMapAndFields(true);
			$(e.target).data('show', true);
		}
	});


	$('#nrvbd-order-admin-get-coordinates').click(function(e)
	{
		e.preventDefault();
		let showing = $(e.target).data('show');
		if (showing) {
			showMapAndFields(false);
			$(e.target).data('show', false);
		}else{
			showMapAndFields(true);
			$(e.target).data('show', true);
		}
	});

	$('a.edit_address').on('click', function(){
		if($('div.edit_address').is(':visible')){
			$('#nrvbd-admin-latlong-fields').css('display', 'block');
		}else{
			$('#nrvbd-admin-latlong-fields').css('display', 'none');
		}
	});

  let autocomplete;
  function initAutocomplete() {
    autocomplete = new google.maps.places.Autocomplete(address1Field, {
      componentRestrictions: { country: ["fr"] },
      fields: ["address_components", "geometry"],
      types: ["address"],
    });
    // address1Field.focus();
    autocomplete.addListener("place_changed", fillInAddress);
  }

  function fillInAddress() {
    const place = autocomplete.getPlace();
    const geometry = place.geometry, location = geometry.location;
    let address1 = "";
    let postcode = "";
    for (const component of place.address_components) {
      const componentType = component.types[0];
      switch (componentType) {
        case "street_number": {
          address1 = `${component.long_name} ${address1}`;
          break;
        }
        case "route": {
          address1 += component.short_name;
          break;
        }
        case "postal_code": {
          postcode = `${component.long_name}${postcode}`;
          break;
        }
        case "postal_code_suffix": {
          postcode = `${postcode}-${component.long_name}`;
          break;
        }
        case "locality":
          cityField.value = component.long_name;
          break;
      }
    }
    address1Field.value = address1;
    postalField.value = postcode;
    address2Field.focus();
    latitudeField.value = location.lat();
    longitudeField.value = location.lng();
  }

  function loadScript(key){
    let url = 'https://maps.googleapis.com/maps/api/js?key='+key+'&libraries=places';
    return $.ajax({
      url: url,
      dataType: "script",
      async: true
    }).then(()=>{
      initAutocomplete();
    });
  }
  loadScript(nrvbd_API_KEY);
});
