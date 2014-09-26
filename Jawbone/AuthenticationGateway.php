<?php
/**
 *
 * Error Codes: 201 - 206
 */
namespace AG\JawboneUPInterfaceBundle\Jawbone;

use OAuth\OAuth2\Token\TokenInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use AG\JawboneUPInterfaceBundle\Jawbone\Exception as JawboneException;

/**
 * Class AuthenticationGateway
 *
 * @package AG\JawboneUPInterfaceBundle\Jawbone
 *
 * @since 0.0.1
 */
class AuthenticationGateway extends EndpointGateway
{
    /**
     * Determine if this user is authorised with Fitbit
     *
     * @access public
     * @version 0.0.1
     *
     * @throws JawboneException
     * @return bool
     */
    public function isAuthorized()
    {
        try
        {
            return $this->service->getStorage()->hasAccessToken('JawboneUP');
        }
        catch (\Exception $e)
        {
            throw new JawboneException('Could not find the access token.', 206, $e);
        }
    }

    /**
     * Initiate the login process
     *
     * @access public
     * @version 0.0.1
     *
     * @throws JawboneException
     * @return void
     */
    public function initiateLogin()
    {
        $url = $this->service->getAuthorizationUri();
        if (!filter_var($url, FILTER_VALIDATE_URL)) throw new JawboneException('Jawbone UP returned an invalid login URL ('.$url.').', 201);
        header('Location: ' . $url);
        exit;
    }

    /**
     * Authenticate user, request access token.
     *
     * @access public
     * @version 0.5.2
     *
     * @param string $code
     * @param string $state
     * @throws JawboneException
     * @return TokenInterface
     */
    public function authenticateUser($code, $state = null)
    {
        /** @var Stopwatch $timer */
        $timer = new Stopwatch();
        $timer->start('Authenticating User', 'Jawbone UP API');

        try
        {
            /** @var TokenInterface $tokenSecret */
            $tokenSecret = $this->service->getStorage()->retrieveAccessToken('JawboneUP');
        }
        catch (\Exception $e)
        {
            $timer->stop('Authenticating User');
            throw new JawboneException('Could not retrieve the access token secret.', 202, $e);
        }

        try
        {
            /** @var TokenInterface $tokenResponse */
            $tokenResponse = $this->service->requestAccessToken(
                $code,
                $state
            );
            $timer->stop('Authenticating User');
            return $tokenResponse;
        }
        catch (\Exception $e)
        {
            $timer->stop('Authenticating User');
            throw new JawboneException('Unable to request the access token.', 203, $e);
        }
    }

    /**
     * Reset session
     *
     * @access public
     * @version 0.0.1
     *
     * @todo Need to add clear to the interface for phpoauthlib (this item was here when this project was branched)
     *
     * @throws JawboneException
     * @return void
     */
    public function resetSession()
    {
        /** @var Stopwatch $timer */
        $timer = new Stopwatch();
        $timer->start('Resetting Session', 'Jawbone UP API');

        try
        {
            $this->service->getStorage()->clearToken('JawboneUP');
        }
        catch (\Exception $e)
        {
            $timer->stop('Resetting Session');
            throw new JawboneException('Could not clear the token.', 204);
        }
        $timer->stop('Resetting Session');
    }

    /**
     * Verify the token
     *
     * @access protected
     * @version 0.0.1
     *
     * @throws Exception
     * @return bool
     */
    protected function verifyToken()
    {
        if (!$this->isAuthorized()) throw new JawboneException('Token could not be verified.', 205);
        return true;
    }
}
