$(function(){
	$("form#contact-us").submit(function(e) {
		
        var datastring = $(this).serializeArray();
        var url 	 = window.location.href;

        $(".contact-info .alert").addClass("hidden");
        $.ajax({
            type        : 'POST',
            url         : url,
            data        : datastring,
            dataType    : 'json',
            encode		: true
        }).success(function(data){
            if(data.success){
                // Showing the Success result for submitting form
                $(".contact-info .alert-success").removeClass("hidden");
                // Reseting/make empty the Contact form
                $("form#contact-us")[0].reset();
            }else{
                // Showing the Error result for submitting form
                $(".contact-info .alert-danger").removeClass("hidden");
                // Highlighting the input fields that has an error using the custom helper
                melisSiteHelper.melisSiteShowFormResult(data.errors, "contact-us");
            }
        });
		e.preventDefault();
	});
})