<?php
/*!
* This simple example illustrate how to authenticate users with Warwick SSO OAuth.
*/

/**
 * Step 0: Start PHP session
 *
 * Normally this step is not required as Hybridauth will attempt to start the session for you, however
 * in some cases it might be better to call session_start() at top of script to avoid cookie-based sessions
 * issues.
 *
 * See: http://php.net/manual/en/function.session-start.php#refsect1-function.session-start-notes
 *      http://stackoverflow.com/a/8028987
 */

session_start();

/**
 * Step 1: Require the Hybridauth Library and Warwick provider
 *
 * Should be as simple as including Composer's autoloader, and the WarwickOAuth class.
 */

include 'vendor/autoload.php';
include 'WarwickOAuth.php';

/**
 * Step 2: Configuring Your Application
 *
 * To get started with Warwick SSO OAuth authentication, you need to register a new OAuth Application with ITS.
 *
 * You can do this at https://warwick.ac.uk/services/its/servicessupport/web/sign-on/help/oauth/apis/registration,
 * setting key type to HMAC-SHA1. You will then be emailed a consumer key and secret (this is a manual process so you
 * may have to wait).
 *
 * Fill in those details below.
 */

$config = [
    'callback' => Hybridauth\HttpClient\Util::getCurrentUrl(), // or 'https://path/to/example_warwick.php'

    'keys' => ['id' => 'WARWICK_SSO_CONSUMER_KEY', 'secret' => 'WARWICK_SSO_CONSUMER_SECRET'], // Your Github application credentials

];

/**
 * Step 3: Instantiate Warwick OAuth Adapter
 *
 * This example instantiates a Warwick OAuth adapter using the array $config we just built.
 */

$warwick = new Hybridauth\Provider\WarwickOAuth($config);

/**
 * Step 4: Authenticating Users
 *
 * When invoked, `authenticate()` will redirect users to Warwick SSO login page where they
 * will be asked to grant access to your application. If they do, WSSO will redirect
 * the users back to Authorization callback URL (i.e., this script).
 */

$warwick->authenticate();

/**
 * Step 5: Retrieve Users Profiles
 *
 * Calling getUserProfile returns an instance of class Hybridauth\User\Profile which contain the
 * connected user's profile in simple and standardized structure across all the social APIs supported
 * by Hybridauth.
 */
$userProfile = $warwick->getUserProfile();

echo 'Hi ' . $userProfile->displayName;
