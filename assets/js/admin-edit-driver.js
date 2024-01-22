var API_KEY = 'AIzaSyDd4O5Px5rp93hmudEebgX0Y1Y5Rs6Myq8';
var DEFAULT_MAP_ZOOM = 12;
var DEFAULT_MAP_CENTER = { "lat": 47.06067911694654, "lng": 2.3193421776980205 };
var MAP_STYLE_ID = '6e735f52d985fd6c';//'f6a6ae74ae22cb64';

let address1Field = document.querySelector("#address1");
let address2Field = document.querySelector("#address2");
let postalField = document.querySelector("#zipcode");
let cityField = document.querySelector("#city");
let latitudeField = document.querySelector("#latitude");
let longitudeField = document.querySelector("#longitude");
let mapElement = document.querySelector("#imap");
//let map;

$(document).ready( () => {

  if (!google.maps) {
    loadGoogleScript(API_KEY, MAP_STYLE_ID);
  } else {
    init();
  }

});

function loadGoogleScript(key, mapstyleid){
    let url = 'https://maps.googleapis.com/maps/api/js?key='+key+'&libraries=places&callback=init';
    if ( mapstyleid && mapstyleid !== '' ) {
        url += "&map_ids="+mapstyleid;
    }
    return $.ajax({
        url: url,
        dataType: "script",
        async: true
    })
}


let autocomplete;
function init() {
  console.log('script ready');
  initAutocomplete();
}

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
  /*if (map) {
    map.setCenter(location);
    map.setZoom(15);
  }*/
}
