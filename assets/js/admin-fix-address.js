
jQuery(document).ready(function($) {
	$('#nrvbd-get-coordinates').click(function(e) 
	{
		e.preventDefault();
		let address_1 = $('#address_1').val();
		let address_2 = $('#address_2').val();
		let postcode = $('#postcode').val();
		let city = $('#city').val();
		let address = address_1 + ' ' + address_2 + ' ' + postcode + ' ' + city;
		if(address_1 == '' || postcode == '' || city == ''){
			alert("Veuillez saisir une adresse valide.");
			return false;
		}
		let url = 'https://maps.googleapis.com/maps/api/geocode/json'
					+ '?address=' + encodeURIComponent(address) 
					+ '&key=' + nrvbd_API_KEY;

		$.getJSON(url, function(data) {
			if(data.status === 'OK'){
				let location = data.results[0].geometry.location;
				$('#latitude').val(location.lat);
				$('#longitude').val(location.lng);
			}else{
				alert("Impossible d'obtenir les coordonnées GPS pour cette adresse.");
			}
		}).fail(function(jqxhr, textStatus, error) {
			console.error('Error:', error);
		});
	});


	$('#nrvbd-order-admin-get-coordinates').click(function(e) 
	{
		e.preventDefault();
		let address_1 = $('#_shipping_address_1').val();
		let address_2 = $('#_shipping_address_2').val();
		let postcode = $('#_shipping_postcode').val();
		let city = $('#_shipping_city').val();
		let address = address_1 + ' ' + address_2 + ' ' + postcode + ' ' + city;
		if(address_1 == '' || postcode == '' || city == ''){
			alert("Veuillez saisir une adresse valide.");
			return false;
		}
		let url = 'https://maps.googleapis.com/maps/api/geocode/json'
					+ '?address=' + encodeURIComponent(address) 
					+ '&key=' + nrvbd_API_KEY;

		$.getJSON(url, function(data) {
			if(data.status === 'OK'){
				let location = data.results[0].geometry.location;
				$('#_shipping_latitude').val(location.lat);
				$('#_shipping_longitude').val(location.lng);
			}else{
				alert("Impossible d'obtenir les coordonnées GPS pour cette adresse.");
			}
		}).fail(function(jqxhr, textStatus, error) {
			console.error('Error:', error);
		});
	});

	$('a.edit_address').on('click', function(){
		if($('div.edit_address').is(':visible')){
			$('#nrvbd-admin-latlong-fields').css('display', 'block');
		}else{
			$('#nrvbd-admin-latlong-fields').css('display', 'none');
		}
	});
});