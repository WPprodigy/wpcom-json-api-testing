<?php

namespace WPcomJSONAPITesting;

function application_creation() {
	process_form_submission();

	$application = get_option( 'api_testing_application', [] );

	if ( empty( $application['client_id'] ) || empty( $application['client_secret'] ) ) {
		create_application_form();
	} else {
		delete_application_form( $application );
	}
}

function create_application_form() {
	?>
	<h2>Step 1: Create an OAuth Application</h2>
	<p>
		This can be done by <a href="https://developer.wordpress.com/apps/new/">visiting developer.wordpress.com</a>.
		The "Type" should be <code>web</code> and the "Redirect URL" should be <code><?php echo esc_url( menu_page_url( 'wpcom-json-api-testing', false ) ) ?></code>
	</p>
	<p>Once created, copy over and save the following information:</p>
	<form method="post" action="<?php menu_page_url( 'wpcom-json-api-testing', false ) ?>" novalidate="novalidate">
		<p>
			<label for="clientID">
				Client ID:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" id="clientID" name="clientID">
			</label>
		</p>
		<p>
			<label for="clientSecret">
				Client Secret: <input type="text" id="clientSecret" name="clientSecret">
			</label>
		</p>
		<?php wp_nonce_field( 'apitesting_app', 'apitesting_app_nonce' ); ?>
		<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Application"></p>
	</form>
	<?php
}

function delete_application_form( $application ) {
	?>
	<h2>Step 1: Create an OAuth Application     ✅</h2>
	<p>All set! Move on to step 2, unless something is wrong and you need to delete this application and restart.</p>
	<ul>
		<li>- Client ID: <code><?php echo esc_html( $application['client_id'] ) ?></code></li>
		<li>- Client Secret: <code>redacted</code></li>
	</ul>
	<form method="post" action="<?php menu_page_url( 'wpcom-json-api-testing', false ) ?>" novalidate="novalidate">
		<input type="hidden" id="reset_application" name="reset_application" value="true">
		<?php wp_nonce_field( 'apitesting_app', 'apitesting_app_nonce' ); ?>
		<p class="submit"><input type="submit" name="submit" id="submit" class="button button-secondary" value="Delete Application"></p>
	</form>
	<?php
}

function process_form_submission() {
	if ( ! isset( $_POST['apitesting_app_nonce'] ) || ! wp_verify_nonce( $_POST['apitesting_app_nonce'], 'apitesting_app' ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_POST['reset_application'] ) ) {
		delete_option( 'api_testing_application' );
		// Also delete site authentication.
		delete_option( 'api_testing_auth_token' );
		return;
	}

	update_option( 'api_testing_application', [
		'client_id'     => isset( $_POST['clientID'] ) ? absint( $_POST['clientID'] ) : '',
		'client_secret' => isset( $_POST['clientSecret'] ) ? sanitize_text_field( $_POST['clientSecret'] ) : '',
	],'no' );
}
