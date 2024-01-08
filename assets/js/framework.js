(function($){	
	$(document).on('click', '.nrvbd-must-confirm', (event) => {
		event.preventDefault();
		event.stopPropagation();
		let elm = event.target;
		if($(elm).prop('disabled')){
			return;
		}
		if($(elm).hasClass('nrvbd-must-confirm') == false) {
			elm = $(elm).parent('.nrvbd-must-confirm');
		}
		let msg = $(elm).attr("confirm-message");
		let link = $(elm).attr("confirm-href");
		if(confirm(msg)){
			window.location.href = link;
		}
	});
	
    $(document).ready(function(){
        $(".nrvbd-select-box").click(function(e){
            let checkboxes = $(".nrvbd-multiselect-options");
			let expanded = $(this).data('expanded');
            if(expanded == false){
                checkboxes.show();
                $(this).data('expanded', true);
            }else{
                checkboxes.hide();
                $(this).data('expanded', false);
            }
			e.stopPropagation()
        });

		$('.nrvbd-multiselect-options input[type="checkbox"]').change(function(){
			let count = $('.nrvbd-multiselect-options input[type="checkbox"]:checked').length;
    		$('select option[data-message-type="selected"]').prop('selected', count > 0);
			let msg = $('select option[data-message-type="selected"]').data('original-message');
			$('select option[data-message-type="selected"]').text(count + msg);
			$('select option[data-message-type="select"]').prop('selected', count <= 0);
		});

		$(document).click(function(e){
			if(!$(e.target).closest('.nrvbd-select-box, .nrvbd-multiselect-options').length){
				$(".nrvbd-multiselect-options").hide();
				$(".nrvbd-select-box").data('expanded', false);
			}
		});
    });
})(jQuery);


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
