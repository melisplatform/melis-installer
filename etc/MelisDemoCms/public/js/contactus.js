$(function(){
	$("#submit-contactus-form-btn").click(function(){
		// Hidding all alerts
		$(".contact-info .alert").addClass("hidden");
		
		// convert the serialized form values into an array
		var datastring = $("form#contactus").serializeArray();
		
		// Adding the Site Id manually from button data
		var siteId = $(this).data("siteid");
		datastring.push({
			name : 'pros_site_id',
			value : siteId
		});
		// serialize the new array and send it to server
		datastring = $.param(datastring);;
		$.ajax({
			type        : 'POST', 
	        url         : '/MelisDemoCms/Contact/submit',
	        data        : datastring,
	        dataType    : 'json',
	        encode		: true
		}).success(function(data){
			if(data.success){
				// Showing the Success result for submitting form
				$(".contact-info .alert-success").removeClass("hidden");
				// Reseting/make empty the Contact form
				$('form#contactus')[0].reset();
			}else{
				// Showing the Error result for submitting form
				$(".contact-info .alert-danger").removeClass("hidden");
				// Highlighting the input fields that has an error using the custom helper
				melisSiteHelper.melisSiteShowFormResult(data.errors);
			}
		});
	});
})