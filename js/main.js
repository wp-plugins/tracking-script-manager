jQuery(document).ready(function($) {
	$('.tracking_script .active_tracking').click(function(e) {
		var active = $(this).siblings('.script_active').attr('value');
		
		$(this).removeClass('fa-circle-o');
		$(this).removeClass('fa-check-circle');
		
		if(active === 'true') {
			$(this).addClass('fa-circle-o');
			$(this).attr('title', 'Activate Script');
			$(this).siblings('.script_active').attr('value', 'false');
		} else {
			$(this).addClass('fa-check-circle');
			$(this).attr('title', 'Deactive Script');
			$(this).siblings('.script_active').attr('value', 'true');
		}
	});
	
	$('.tracking_script .edit_tracking').click(function(e) {
		$(this).removeClass('fa-edit');
		$(this).removeClass('fa-save');	

		if($(this).siblings('.script_info').find('input[type="text"]').attr('readonly') === 'readonly') {
			$(this).siblings('.script_info').find('input[type="text"]').attr('readonly', false);
			$(this).addClass('fa-save');
			$(this).attr('title', 'Save Script');
		} else {
			$('.tracking_scripts_wrap form').submit();
			$(this).siblings('.script_info').find('input[type="text"]').attr('readonly', 'readonly');
			$(this).addClass('fa-edit');
			$(this).attr('title', 'Edit Script');
		}
	});
	
	$('.tracking_script .delete_tracking').click(function(e) {
		var confirmed = confirm("Are you sure you want to delete this script?");
		if(confirmed) {
			$(this).parent().fadeOut(400, function() {
				$(this).find('.script_exists').attr('value', 'false');
				
				var index = 1;
				$(this).parent().find('.tracking_script').each(function(i) {
					if($(this).css('display') === 'block') {
						$(this).find('> p').text(index);
						$(this).find('.script_order').attr('value', index);
						index++;
					}
				});
				
				$('.tracking_scripts_wrap form').submit();
			});
		}
	});
	
	$('.tracking_scripts').sortable({
		update: function(event, ui) {
			$(this).find('.tracking_script').each(function(i) {
				i++;
				$(this).find('> p').text(i);
				$(this).find('.script_order').attr('value', i);
			});
		}
	});
});