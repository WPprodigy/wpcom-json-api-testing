<?php

/**
 * Plugin Name: WPcom JSON API Testing
 * Version:     1.0.0
 * Description: Testing for OAuth and remote calls through the WPcom JSON API.
 * Author:      Caleb Burks
 * License:     GPL v3
 */

namespace WPcomJSONAPITesting;

require_once '1-oauth-application.php';
require_once '2-site-authentication.php';
require_once '3-site-testing.php';

// Create admin menu item.
add_action( 'admin_menu', function() {
	add_menu_page(
		'API Testing',
		'API Testing',
		'manage_options',
		'wpcom-json-api-testing',
		__NAMESPACE__ . '\\admin_menu_page_output',
		'dashicons-rest-api',
		76
);
} );

function admin_menu_page_output() {
	?>
	<div class='wrap'>
		<h1>WPcom JSON API Testing</h1>
		<?php
			// Step 1: Create an OAuth application on WPcom.
			application_creation();

			// Step 2: Authenticate a site using the OAuth application.
			site_authentication();

			// Step 3: Test away!
			$site_testing = new Site_Testing();
			$site_testing->output();
		?>
	</div>
	<?php
}
