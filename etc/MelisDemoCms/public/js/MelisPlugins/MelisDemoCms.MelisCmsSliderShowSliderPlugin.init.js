/**
 * Init function for the plugin to be rendered
 * This function is used when reloading a plugin in the back office after
 * changing parameters or drag/dropping it.
 * This function is automatically called and must be nammed PluginName_init
 * It will always receive the id of the plugin as a parameter, in case multiple
 * same plugin are on the page.
 */

function MelisCmsSliderShowSliderPlugin_init(idPlugin){
	
	var idPlugin = typeof idPlugin != "undefined" ? idPlugin : '';
	var	$plugin = $('#'+idPlugin);
	
	if($plugin.length){
		if($plugin.hasClass("homepage-slider-owl")){
			
			$plugin.owlCarousel({
		    	lazyLoad:true,
		        items: 1,
		        smartSpeed:1500,
		        loop:true,
		        autoplay:true,
		        autoplayTimeout:8000
		    });
			
		}else if($plugin.hasClass("aboutus-slider-owl")){
			
			$plugin.owlCarousel({
		    	margin: 30,
		    	dots: false,
		        responsiveClass:true,
		        responsive:{
		            0:{
		                items: 1,
		                nav:false
		            },
		            480:{
		                items: 2,
		                nav:false
		            },
		            768:{
		                items: 3,
		                nav:false,
		    			dots: true,
		            },
		            1200:{
		                items: 4,
		                nav:false,
		    			dots: true,
		            },
		            1400:{
		                items: 4,
		                nav: true,
		                navText: ["<i class='fa fa-angle-left'>","<i class='fa fa-angle-right'>"],
		            }
		        }
		    });
		}
	}
}