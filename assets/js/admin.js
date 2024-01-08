(function($){
	
	var filters = {};
	
	$(document).on('click', '.navigation .navigation-button', function(e){
		e.preventDefault();
		let target = e.target;
		let button = target;
		if(!$(button).hasClass('navigation-button')){
			button = $(button).parents('.navigation-button');
		}
		
		
	});
	
	
	function nrvbd_ajax(url, method, data, successCallback, errorCallback) 
	{
		jQuery.ajax({
			url: url,
			method: method,
			data: data,
			dataType: 'json',
			success: function(response){
				if(successCallback && typeof successCallback === 'function'){
					successCallback(response);
				}
			},
			error: function(xhr, status, error){
				if(errorCallback && typeof errorCallback === 'function'){
					errorCallback(xhr, status, error);
				}
			}
		});
	}

	function nrvbd_wait_for_input(selector, callback, timeout = 800){
		var typingTimer;
		jQuery(selector).on("keyup", function(){
			clearTimeout(typingTimer);
			typingTimer = setTimeout(callback, timeout);
		});
		jQuery(selector).on('click', function(){
			if(jQuery(selector).val().trim().length > 2){
				setTimeout(()=>{ callback(); }, 150);
			}
		});
	}

})(jQuery)