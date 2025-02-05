<?php

namespace CloudTomatoes\OAuth2\OAuthClients;

use CloudTomatoes\OAuth2\Domain\Repository\AppRepository;
use Flownative\OAuth2\Client\Authorization;
use Flownative\OAuth2\Client\OAuthClient;
use Flownative\OAuth2\Client\OAuthClientException;
use GuzzleHttp\Psr7\Uri;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use CloudTomatoes\OAuth2\Domain\Model\App;
use Neos\Flow\Annotations as Flow;
use CloudTomatoes\OAuth2\Domain\Repository\ProviderRepository;
use Psr\Http\Message\UriInterface;

abstract class AbstractClient extends OAuthClient
{
    /**
     * @var App
     */
    protected $app;

    /**
     * @Flow\Inject()
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * AbstractClient constructor.
     * @param $app
     */
    public function __construct($app)
    {
        if ($app instanceof App) {
            $this->app = $app;
        } elseif (is_string($app)) {
            $appRepository = new AppRepository();
            $this->app = $appRepository->findOneByName($app);
        }
        $serviceName = $this->app ? $this->app->getName() : $app;
        parent::__construct($serviceName);
    }

    /**
     * Returns the OAuth server's base URI
     *
     * @return string For example https://myservice.flownative.com
     */
    public function getBaseUri(): string
    {
        return $this->app->getApiUri();
    }

    /**
     * Returns the OAuth service endpoint for authorizing a token.
     * Override this method if needed.
     *
     * @return string
     */
    public function getAuthorizeTokenUri(): string
    {
        if ($this->app) {
            $provider = $this->app->getProvider();
        } else {
            $providerRepository = $this->objectManager->get(ProviderRepository::class);
            $provider = $providerRepository->findOneByOauthClient('CloudTomatoes\OAuth2\OAuthClients\GCPClient');
        }
        return trim($provider->getAuthenticationEndpoint(), '/') . '/authorize';
    }

    /**
     * Returns the OAuth service endpoint for the access token.
     * Override this method if needed.
     *
     * @return string
     */
    public function getAccessTokenUri(): string
    {
        if ($this->app) {
            $provider = $this->app->getProvider();
        } else {
            $providerRepository = $this->objectManager->get(ProviderRepository::class);
            $provider = $providerRepository->findOneByOauthClient('CloudTomatoes\OAuth2\OAuthClients\AzureClient');
        }
        return trim($provider->getAuthenticationEndpoint(), '/') . '/token';
    }

    /**
     * Returns the OAuth service endpoint for accessing the resource owner details.
     * Override this method if needed.
     *
     * @return string
     */
    public function getResourceOwnerUri(): string
    {
        if ($this->app) {
            $provider = $this->app->getProvider();
        } else {
            $providerRepository = $this->objectManager->get(ProviderRepository::class);
            $provider = $providerRepository->findOneByOauthClient('CloudTomatoes\OAuth2\OAuthClients\AzureClient');
        }
        return trim($provider->getAuthenticationEndpoint(), '/') . '/token/resource';
    }

    /**
     * Returns the current client id (for sending authenticated requests)
     *
     * @return string The client id which is known by the OAuth server
     */
    public function getClientId(): string
    {
        return $this->app->getClientId();
    }

    /**
     * Finish an OAuth authorization with the Authorization Code flow
     *
     * @param string $stateIdentifier The state identifier, passed back by the OAuth server as the "state" parameter
     * @param string $code The authorization code given by the OAuth server
     * @param string $scope The scope granted by the OAuth server
     * @return UriInterface The URI to return to
     * @throws OAuthClientException
     */
    public function finishAuthorization(string $stateIdentifier, string $code, string $scope): UriInterface
    {
        $stateFromCache = $this->stateCache->get($stateIdentifier);
        if (empty($stateFromCache)) {
            throw new OAuthClientException(sprintf('OAuth: Finishing authorization failed because oAuth state %s could not be retrieved from the state cache.', $stateIdentifier), 1558956494);
        }

        $authorizationId = $stateFromCache['authorizationId'];
        $clientId = $stateFromCache['clientId'];
        $clientSecret = $stateFromCache['clientSecret'];
        $oAuthProvider = $this->createOAuthProvider($clientId, $clientSecret);

        $this->logger->info(sprintf('OAuth (%s): Finishing authorization for client "%s", authorization id "%s", using state %s.', $this->getServiceType(), $clientId, $authorizationId, $stateIdentifier));
        try {
            $authorization = $this->entityManager->find(Authorization::class, $authorizationId);

            if (!$authorization instanceof Authorization) {
                throw new OAuthClientException(sprintf('OAuth2 (%s): Finishing authorization failed because authorization %s could not be retrieved from the database.', $this->getServiceType(), $authorizationId), 1568710771);
            }

            $this->logger->debug(sprintf('OAuth (%s): Retrieving an OAuth access token for authorization "%s" in exchange for the code %s', $this->getServiceType(), $authorizationId, str_repeat('*', strlen($code) - 3) . substr($code, -3, 3)));
            $accessToken = $oAuthProvider->getAccessToken(Authorization::GRANT_AUTHORIZATION_CODE, ['code' => $code]);

            $this->logger->info(sprintf('OAuth (%s): Persisting OAuth token for authorization "%s" with expiry time %s.', $this->getServiceType(), $authorizationId, $accessToken->getExpires()));

            $authorization->setAccessToken($accessToken);

            $accessTokenValues = $accessToken->getValues();
            $scope = $accessTokenValues['scope'] ?? $scope;
            $authorization->setScope($scope);
            $this->entityManager->persist($authorization);
            $this->entityManager->flush();

        } catch (IdentityProviderException $exception) {
            throw new OAuthClientException($exception->getMessage() . ' ' . $exception->getResponseBody()['error_description'], 1511187001671, $exception);
        }

        $returnToUri = new Uri($stateFromCache['returnToUri']);
        $returnToUri = $returnToUri->withQuery(trim($returnToUri->getQuery() . '&' . self::AUTHORIZATION_ID_QUERY_PARAMETER_NAME . '=' . $authorizationId, '&'));

        $this->logger->debug(sprintf('OAuth (%s): Finished authorization "%s", $returnToUri is %s.', $this->getServiceType(), $authorizationId, $returnToUri));
        return $returnToUri;
    }
}
