var melisSiteHelper = (function(){
	
	// CACHE SELECTORS
	var $body = $("body");
	
	function hasValue(){
		if($(this).val().length){
			var element = $("#"+$(this).attr('id'));
			element.parent().removeClass("has-error");
			element.next().remove();
		}
	}
	
	function melisSiteShowFormResult(errors , form = 'form'){
		
		$("#"+form+" div").removeClass("has-error").find(".text-danger").remove();
		
		$.each( errors, function( key, error ) {
			var element = $('#'+key);
			
			// char counter in seo title
			element.on("keydown change", hasValue);
			
			element.next().remove();
			
			var errorTexts = '';
			
			// catch error level of object
			try {
				$.each( error, function( key, value ) {
					if(key !== 'label'){
						 errorTexts = value;
					}
				});
			} catch(Tryerror) {
				if(key !== 'label'){
					 errorTexts = error;
				} 
			}	
			
			element.parent().removeClass("has-success").addClass("has-error").after().append('<label for="'+key+'" class="text-danger">'+errorTexts+'</label>');
			
		});
	}
	
	return{
		melisSiteShowFormResult		:		melisSiteShowFormResult,
	}
})();