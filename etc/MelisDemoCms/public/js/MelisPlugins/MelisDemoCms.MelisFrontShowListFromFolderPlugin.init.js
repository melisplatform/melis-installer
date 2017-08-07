/**
 * Init function for the plugin to be rendered
 * This function is used when reloading a plugin in the back office after
 * changing parameters or drag/dropping it.
 * This function is automatically called and must be nammed PluginName_init
 * It will always receive the id of the plugin as a parameter, in case multiple
 * same plugin are on the page.
 */

function MelisFrontShowListFromFolderPlugin_init(idPlugin){
	console.log('Front Show List ID', idPlugin);
	var idPlugin = typeof idPlugin != "undefined" ? idPlugin : '';
	var	$plugin = $('#'+idPlugin);

	if(idPlugin == "testimonial-show-list-from-folder" || idPlugin.indexOf('testimonial-show-list-from-folder') > -1) {
		$plugin.owlCarousel({items: 1});
	} else {
		// default testimonial slider
		$plugin.owlCarousel({items: 1});
	}
}