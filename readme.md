# WPcom JSON API Testing

This is a WP plugin meant for testing only 🙃. Can just install on a local WP site. Not intended for live use! There is currently no encryption, etc.

It's just an example of the OAuth process for using the WordPress.com REST API: https://developer.wordpress.com/docs/oauth2/, and provides a sort of playground for testing things.

## Usage

1) Once installed and activated, go to `/admin.php?page=wpcom-json-api-testing`.
2) Follow the instructions, namely:
	- Create oAuth App: https://developer.wordpress.com/apps/new/
	- Select a site to authenticate with.
3) Once all is working, go into `3-site-testing.php` and test around with the connection as needed.
	- Endpoints: https://developer.wordpress.com/docs/api/

## Cleanup

When done, delete the application and remove the authenticated sites from your profile:

- https://developer.wordpress.com/apps/
- https://wordpress.com/me/security/connected-applications
