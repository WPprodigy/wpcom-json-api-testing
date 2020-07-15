<?php

namespace WPcomJSONAPITesting;

class Site_Testing {
	public $ready_to_test = false;

	public $useragent = 'wpcom-json-api-testing';
	public $timeout = 45;

	public function __construct() {
		$application   = get_option( 'api_testing_application', [] );
		$authorization = get_option( 'api_testing_auth_token', [] );

		$this->ready_to_test = ! empty( $application ) && ! empty( $authorization );
		if ( $this->ready_to_test ) {
			$this->endpoint = 'https://public-api.wordpress.com/rest/v1.2/sites/' . $authorization['blog_id'];
			$this->access_token = $authorization['access_token'];
		}
	}

	public function output() {
		if ( ! $this->ready_to_test ) {
			echo '<h2>Step 3: Testing time!     ❌</h2>';
			echo '<p>Please complete steps 1 and 2 first.</p>';
			return;
		}

		echo '<h2>Step 3: Testing time!     🎉</h2>';
		echo '<p>Feel free to edit/play around in the <code>3-site-testing.php</code> file to test what you need to.</p>';

		$this->get_latest_post();

		// $this->create_new_post();
	}

	public function get_latest_post() {
		$response = wp_remote_get( $this->endpoint . '/posts/?number=1', array(
			'timeout'    => $this->timeout,
			'user-agent' => $this->useragent,
			'sslverify'  => false,
			'headers'    => array (
				'authorization' => 'Bearer ' . $this->access_token,
			),
		) );

		$response = json_decode( wp_remote_retrieve_body( $response ) );

		echo '<h3>Latest Post</h3>';
		if ( ! empty( $response->error ) ) {
			print_r( $response->error );
		} else {
			$latest_post = $response->posts[0];
			?>
				<ul>
					<li>- ID: <?php echo esc_html( $latest_post->ID ) ?></li>
					<li>- Title: <?php echo esc_html( $latest_post->title ) ?></li>
				</ul>
			<?php
		}
	}

	public function create_new_post() {
		$response = wp_remote_post( $this->endpoint . '/posts/new/', array(
			'timeout'    => $this->timeout,
			'user-agent' => $this->useragent,
			'sslverify'  => false,
			'headers'    => array (
				'authorization' => 'Bearer ' . $this->access_token,
				'Content-Type'  => 'application/x-www-form-urlencoded'
			),
			'body' => [
				'title' => 'Test Post: ' . date( 'h:i:sa' ),
				'content' => 'Created with the WPcom JSON API Testing plugin.',
				'status' => 'draft', // Safety :)
				'type' => 'post',
			],
		) );

		$response = json_decode( wp_remote_retrieve_body( $response ) );

		echo '<h3>Create New Post</h3>';
		print_r($response );
	}
}
