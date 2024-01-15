
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
			alert('Please enter address, postcode and city.');
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
				alert('Unable to get coordinates for this address.');
			}
		}).fail(function(jqxhr, textStatus, error) {
			console.error('Error:', error);
		});
	});
});