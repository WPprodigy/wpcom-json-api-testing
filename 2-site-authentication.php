<?php

namespace WPcomJSONAPITesting;

function site_authentication() {
	$application = get_option( 'api_testing_application', [] );

	if ( empty( $application['client_id'] ) || empty( $application['client_secret'] ) ) {
		echo '<h2>Step 2: Setup authorization for a specific site     ❌</h2>';
		echo '<p>Please complete step 1 first.</p>';
		return;
	}

	$oauth_application = [
		'client_id' => $application['client_id'],
		'redirect_uri' => menu_page_url( 'wpcom-json-api-testing', false ),
		'client_secret' => $application['client_secret'],
	];

	$auth_endpoint = add_query_arg( [
		'client_id' => rawurlencode( $oauth_application['client_id'] ),
		'redirect_uri' => rawurlencode( $oauth_application['redirect_uri'] ),
		'response_type' => 'code',
	], 'https://public-api.wordpress.com/oauth2/authorize' );

	// Listen for the auth process to begin, starting with WPcom responding to the redirect url with a query string.
	$result = process_authentication( $oauth_application );

	$authorization = get_option( 'api_testing_auth_token', [] );
	$auth_exists = ! empty( $authorization['access_token'] ) && ! empty( $authorization['blog_id'] ) && ! empty( $authorization['blog_url'] );

	if ( ! $auth_exists ) {
		if ( is_wp_error( $result ) ) {
			// Something went wrong, try again.
			restart_authorization_form( $result->get_error_message(), $auth_endpoint );
		} else {
			start_authorization_form( $auth_endpoint );
		}
	} else {
		delete_authorization_form( $authorization );
	}
}

function start_authorization_form( $auth_endpoint ) {
	?>
		<h2>Step 2: Setup authorization for a specific site</h2>
		<p>Click the authorize button to generate an api token for the target remote site.</p>
		<p>Note that for Jetpack-connected sites, your WordPress.com user account will need to be linked to the local WP account on the Jetpack site.</p>
		<input type='button' class='button-primary' onClick="parent.location='<?php echo esc_url( $auth_endpoint ); ?>'" value='Authorize'>
	<?php
}

function restart_authorization_form( $error_message, $auth_endpoint ) {
	?>
		<h2>Step 2: Setup authorization for a specific site     ❌</h2>
		<p>Error retrieving API tokens. Please authorize again. Error: <?php echo esc_html( $error_message ) ?></p>
		<input type='button' class='button-primary' onClick="parent.location='<?php echo esc_url( $auth_endpoint ) ?>'" value='Authorize'>
	<?php
}

function process_authentication( $oauth_application ) {
	// Delete site authentication if requested.
	if ( isset( $_POST['reset_authentication'] ) ) {
		if ( isset( $_POST['apitesting_auth_nonce'] ) && wp_verify_nonce( $_POST['apitesting_auth_nonce'], 'apitesting_auth_delete' ) && current_user_can( 'manage_options' ) ) {
			delete_option( 'api_testing_auth_token' );
			return;
		}
	}

	// Not processing yet. The process starts when WPcom returns with a code to the "redirect url".
	if ( empty( $_GET['code'] ) ) {
		return;
	}

	// Generate an authentication token from the previous WPcom response using the recieved code.
	$generate_token = wp_remote_post( 'https://public-api.wordpress.com/oauth2/token', array(
		'sslverify' => false,
		'body'      => [
			'client_id'     => $oauth_application['client_id'],
			'redirect_uri'  => $oauth_application['redirect_uri'],
			'client_secret' => $oauth_application['client_secret'],
			'code'          => sanitize_text_field( $_GET['code'] ),
			'grant_type'    => 'authorization_code'
		],
	) );

	$response = json_decode( $generate_token['body'] );

	// Something went wrong, will have to try again.
	if ( ! isset( $response->access_token, $response->blog_id, $response->blog_url ) || ! empty( $response->error ) ) {
		return new \WP_Error( 'authentication_failed', isset( $response->error ) ? $response->error_description : 'unknown' );
	}

	// Save the new auth token.
	update_option( 'api_testing_auth_token', [
		'access_token' => sanitize_text_field( $response->access_token ),
		'blog_id'      => sanitize_text_field( $response->blog_id ),
		'blog_url'     => sanitize_text_field( $response->blog_url ),
	], 'no' );
}

function delete_authorization_form( $authorization ) {
	?>
	<h2>Step 2: Setup authorization for a specific site     ✅</h2>
	<p>All set! Below are the new site auth tokens. Move on to step 3, unless you'd like to delete this connection.</p>
	<p><strong>Note:</strong> Be sure to cleanup/remove these authentications when you are finshed <a href='https://wordpress.com/me/security/connected-applications'>here</a>.
	<ul>
		<li>- Blog ID: <code><?php echo esc_html( $authorization['blog_id'] ) ?></code></li>
		<li>- Blog URL: <code><?php echo esc_html( $authorization['blog_url'] ) ?></code></li>
		<li>- Access Token: <code>redacted</code></li>
	</ul>

	<form method="post" action="<?php menu_page_url( 'wpcom-json-api-testing', false ) ?>" novalidate="novalidate">
		<input type="hidden" id="reset_authentication" name="reset_authentication" value="true">
		<?php wp_nonce_field( 'apitesting_auth_delete', 'apitesting_auth_nonce' ); ?>
		<p class="submit"><input type="submit" name="submit" id="submit" class="button button-secondary" value="Delete Site Authorization"></p>
	</form>
	<?php
}
