<?php

return array(
	'site' => array(
		'MelisDemoCms' => array(
			'conf' => array(
				'id' => 'id_MelisDemoCms',
			    'home_page' => '[:homePageId]'
			),
			'datas' => array(
			    // Submenu limit
			    'sub_menu_limit' => null,
			    // News Page Id
			    'news_menu_page_id' => '[:newsPageId]',
			    // News Details Page Id
			    'news_details_page_id' => '[:newsDetailsPageId]',
			    // Testimonial parent id
			    'testimonial_id' => '[:testimonialPageId]',
			    // Homepage header slider
			    'homepage_header_slider' => '[:homepageHeaderSlider]',
			    // Aboutus slider
			    'aboutus_slider' => '[:aboutusSlider]',
			    // Search results page
			    'search_result_page_id' => '[:searchResultsPageId]',
			    /**
			     * Required Modaules for installation,
			     * to trigger services that needed to install the MelisDemoCms
			     * and to avoid deselect from selecting modules during installations.
			     */
			    'required_modules' => array(
			        'MelisCmsNews',
			        'MelisCmsSlider',
			        'MelisCmsProspects',
			    )
			)	
		)
	)
);
