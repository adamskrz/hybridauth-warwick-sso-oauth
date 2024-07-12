<?php
namespace Hybridauth\Provider;

use Hybridauth\Adapter\OAuth1;
use Hybridauth\Exception\UnexpectedApiResponseException;
use Hybridauth\Data;
use Hybridauth\User;

/**
 * Warwick SSO OAuth1 provider adapter.
 */
class WarwickOAuth extends OAuth1
{
    /**
     * {@inheritdoc}
     */
    protected $apiBaseUrl = 'https://websignon.warwick.ac.uk';

    /**
     * {@inheritdoc}
     */
    protected $authorizeUrl = 'https://websignon.warwick.ac.uk/oauth/authorise';

    /**
     * {@inheritdoc}
     */
    protected $requestTokenUrl = 'https://websignon.warwick.ac.uk/oauth/requestToken';

    protected $scope = 'urn:websignon.warwick.ac.uk:sso:service';

    /**
     * {@inheritdoc}
     */
    protected $accessTokenUrl = 'https://websignon.warwick.ac.uk/oauth/accessToken';

    /**
     * {@inheritdoc}
     */
    protected $apiDocumentation = 'https://warwick.ac.uk/services/its/servicessupport/web/sign-on/help/oauth/apis/';

    /**
     * {@inheritdoc}
     */
    protected $oauth1Version = '1.0';

    public function configure()
    {
        parent::configure();

        // Add scope to the request token (as used in OAuth2)
        $this->requestTokenParameters['scope'] = $this->config->exists('scope') ? $this->config->get('scope') : $this->scope;

        // Add callback to authrisation instead of requestToken as Warwick implements accoring to OAuth 1.0 not 1.0a
        $this->AuthorizeUrlParameters['oauth_callback'] = $this->callback;
    }

    protected function exchangeAuthTokenForAccessToken($oauth_token, $oauth_verifier = '')
    {
        $response = $this->oauthRequest(
            $this->accessTokenUrl,
            $this->tokenExchangeMethod,
            $this->tokenExchangeParameters,
            $this->tokenExchangeHeaders
        );

        return $response;
    }

    protected function getUserAttributes()
    {
        // attributes endpoint is in key=value form, which is unsupported by Data\Parser, so we can't use apiRequest
        $this->maintainToken();

        $url = $url = rtrim($this->apiBaseUrl, '/') . '/oauth/authenticate/attributes';
        $response = $this->oauthRequest($url, 'POST');

        $data = new Data\Collection();

        foreach (explode("\n", trim($response)) as $item) {
            if (strpos($item, '=') === false) {
                continue;
            }
            list($key, $value) = explode('=', $item, 2);
            $data->set($key, $value);
        }

        if (!$data->exists('user')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserProfile()
    {

        $attributes = $this->getUserAttributes();
        $userProfile = new User\Profile();

        $userProfile->identifier = $attributes->get('id');
        $userProfile->displayName = $attributes->get('name');
        $userProfile->firstName = $attributes->get('firstname');
        $userProfile->lastName = $attributes->get('lastname');
        $userProfile->description = $attributes->get('title');
        $userProfile->email = $attributes->get('email');
        $userProfile->emailVerified = $attributes->get('email');
        $userProfile->data = $attributes->toArray();

        return $userProfile;
    }
}
