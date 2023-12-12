# oauth2-anaf
 About Anaf E-Factura Symfony Plugin

Basic setup:

     // Use:
     $provider = new \League\OAuth2\Client\Provider\GenericProvider([
        'clientId'                => 'XXXXXX',    // The client ID assigned to you by the provider
        'clientSecret'            => 'XXXXXX',    // The client password assigned to you by the provider
        'redirectUri'             => 'https://my.example.com/your-redirect-url/',
        'urlAuthorize'            => 'https://logincert.anaf.ro/anaf-oauth2/v1/authorize',
        'urlAccessToken'          => 'https://logincert.anaf.ro/anaf-oauth2/v1/token',
         'urlResourceOwnerDetails' => 'https://logincert.anaf.ro/anaf-oauth2/v1/revoke'
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
        $refreshToken = $accessToken->getToken();
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

Upload XML E-factura - POST method

         $provider =  new Anaf([
                    'clientId'                => 'XXXXXX',    // The client ID assigned to you by the provider
                    'clientSecret'            => 'XXXXXX',    // The client password assigned to you by the provider
                    'redirectUri'             => 'https://my.example.com/your-redirect-url/',
                    'urlAuthorize'            => 'https://logincert.anaf.ro/anaf-oauth2/v1/authorize',
                    'urlAccessToken'          => 'https://logincert.anaf.ro/anaf-oauth2/v1/token',
                    'urlResourceOwnerDetails' => 'https://logincert.anaf.ro/anaf-oauth2/v1/revoke'
                ]);
    
            //document that you want to send to ANAF;
            $options['body'] = $document;
            //cif code of your company
            // $getAnafRefreshToken your anaf token
            $requestAnafPOST = $provider->getAuthenticatedRequest(
                        'POST',
                        'https://api.anaf.ro/test/FCTEL/rest/upload?standard=UBL&cif=XXX',
                        $getAnafRefreshToken(),
                        $options
                    );
            //your parse response, you can also use getResponse for status code
            $response = $provider->getParsedResponse($requestAnafPOST);

            $crawler = new Crawler($response);
            $indexIncarcare = $crawler->filterXPath('header')->attr('index_incarcare');
            
Stare Mesaj E-factura - GET method

           $provider =  new Anaf([
                    'clientId'                => 'XXXXXX',    // The client ID assigned to you by the provider
                    'clientSecret'            => 'XXXXXX',    // The client password assigned to you by the provider
                    'redirectUri'             => 'https://my.example.com/your-redirect-url/',
                    'urlAuthorize'            => 'https://logincert.anaf.ro/anaf-oauth2/v1/authorize',
                    'urlAccessToken'          => 'https://logincert.anaf.ro/anaf-oauth2/v1/token',
                    'urlResourceOwnerDetails' => 'https://logincert.anaf.ro/anaf-oauth2/v1/revoke'
                ]);

                $idincarcareSPV = 'XXXXXX';

                $requestAnafGET = $provider->getAuthenticatedRequest(
                    'GET',
                    'https://api.anaf.ro/test/FCTEL/rest/stareMesaj?id_incarcare=' . $idIncarcareSPV,
                    $getAnafRefreshToken(),
                );

                $responseGET = $provider->getParsedResponse($requestAnafGET);
                $crawler2 = new Crawler($responseGET);
                $stareIncarcare = $crawler2->filterXPath('header')->attr('stare');
                $idDescarcare = $crawler2->filterXPath('header')->attr('id_descarcare');
    
Descarcare Raspuns E-factura - GET method

              $requestAnafDescarcare = $provider->getAuthenticatedRequest(
                    'GET',
                    'https://api.anaf.ro/test/FCTEL/rest/descarcare?id=' .$idDescarcare,
                    $getAnafRefreshToken(),
               );
               $requestAnafDescarcareDOC = $provider->getParsedResponse($requestAnafDescarcare);
