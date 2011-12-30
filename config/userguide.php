<?php defined('SYSPATH') OR die('No direct access allowed.');

return array
(
	// Enable the API browser.  TRUE or FALSE
	'api_browser'  => FALSE,
	
	// Enable these packages in the API browser.  TRUE for all packages, or a string of comma seperated packages, using 'None' for a class with no @package
	// Example: 'api_packages' => 'Kohana,Kohana/Database,Kohana/ORM,None',
	'api_packages' => FALSE,
	
	// Leave this alone
	'modules' => array(

		// This should be the path to this modules userguide pages, without the 'guide/'. Ex: '/guide/modulename/' would be 'modulename'
		'kohana101' => array(

			// Whether this modules userguide pages should be shown
			'enabled' => TRUE,
			
			// The name that should show up on the userguide index page
			'name' => 'Kohana 101',

			// A short description of this module, shown on the index page
			'description' => 'Учебник по Kohana Framework',
			
			// Copyright message, shown in the footer for this module
			'copyright' => '&copy; 2011 Russian Kohana community',
		)	
	)
);
