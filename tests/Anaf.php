<?php 
//Use composer autoload to load class files
require_once __DIR__ . "/../vendor/autoload.php";

$provider = new \Mihai3332001\OAuth2\Client\Provider\Anaf([
    'clientId'                => 'XXXXXX',    // The client ID assigned to you by the provider
    'clientSecret'            => 'XXXXXX',    // The client password assigned to you by the provider
    'redirectUri'             => 'https://my.example.com/your-redirect-url/',
    'urlAuthorize'            => 'https://service.example.com/authorize',
    'urlAccessToken'          => 'https://service.example.com/token',
    'urlResourceOwnerDetails' => 'https://service.example.com/resource'
]);

// If we don't have an authorization code then get one
if (!isset($_GET['code'])) {

    // Fetch the authorization URL from the provider; this returns the
    // urlAuthorize option and generates and applies any necessary parameters
    // (e.g. state).
    $authorizationUrl = $provider->getAuthorizationUrl();

    // Get the state generated for you and store it to the session.
    $_SESSION['oauth2state'] = $provider->getState();

    // Optional, only required when PKCE is enabled.
    // Get the PKCE code generated for you and store it to the session.
    $_SESSION['oauth2pkceCode'] = $provider->getPkceCode();

    // Redirect the user to the authorization URL.
    header('Location: ' . $authorizationUrl);
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || empty($_SESSION['oauth2state']) || $_GET['state'] !== $_SESSION['oauth2state']) {

    if (isset($_SESSION['oauth2state'])) {
        unset($_SESSION['oauth2state']);
    }

    exit('Invalid state');

} else {

    try {
    
        // Optional, only required when PKCE is enabled.
        // Restore the PKCE code stored in the session.
        $provider->setPkceCode($_SESSION['oauth2pkceCode']);

        // Try to get an access token using the authorization code grant.
        $accessToken = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code']
        ]);

        // We have an access token, which we may use in authenticated
        // requests against the service provider's API.
        echo 'Access Token: ' . $accessToken->getToken() . "<br>";
        echo 'Refresh Token: ' . $accessToken->getRefreshToken() . "<br>";
        echo 'Expired in: ' . $accessToken->getExpires() . "<br>";
        echo 'Already expired? ' . ($accessToken->hasExpired() ? 'expired' : 'not expired') . "<br>";

        // Using the access token, we may look up details about the
        // resource owner.
        $resourceOwner = $provider->getResourceOwner($accessToken);

        var_export($resourceOwner->toArray());

        // The provider provides a way to get an authenticated API request for
        // the service, using the access token; it returns an object conforming
        // to Psr\Http\Message\RequestInterface.
        $request = $provider->getAuthenticatedRequest(
            'GET',
            'https://service.example.com/resource',
            $accessToken
        );

    } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {

        // Failed to get the access token or user details.
        exit($e->getMessage());

    }

}