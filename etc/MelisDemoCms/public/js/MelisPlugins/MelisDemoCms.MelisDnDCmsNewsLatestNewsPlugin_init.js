/**
 * Init function for the plugin to be rendered
 * This function is used when reloading a plugin in the back office after
 * changing parameters or drag/dropping it.
 * This function is automatically called and must be nammed PluginName_init
 * It will always receive the id of the plugin as a parameter, in case multiple
 * same plugin are on the page.
 */

function MelisDnDCmsNewsLatestNewsPlugin_init(idPlugin){
	var	$plugin = $('#'+idPlugin);
	
	// dragndrop plugin latest-news
	$plugin.owlCarousel({
        items: 3,
        margin: 15,
        dots: true,
        responsiveClass:true,
        responsive:{
            0:{
                items:1,
                nav:false
            },
            768:{
                items:2,
                nav:false
            },
            1400:{
                items:3,
                nav: true,
                navText: ["<i class='fa fa-angle-left'>","<i class='fa fa-angle-right'>"],

            }
        }
    });
}