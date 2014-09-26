<?php
/**
 *
 * Error Codes: 101 - 112
 */
namespace AG\JawboneUPInterfaceBundle\Jawbone;

use OAuth\Common\Consumer\Credentials;
use OAuth\ServiceFactory;
use OAuth\OAuth2\Service\JawboneUP as ServiceInterface;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\Common\Http\Client\ClientInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Stopwatch\Stopwatch;
use AG\JawboneUPInterfaceBundle\Jawbone\Exception as JawboneException;

/**
 * Class ApiGatewayFactory
 *
 * @package AG\JawboneUPInterfaceBundle\Jawbone
 *
 * @version 0.0.1
 *
 * @method AuthenticationGateway getAuthenticationGateway()
 * @method GoalGateway getGoalGateway()
 * @method MovesGateway getMovesGateway()
 * @method SleepGateway getSleepGateway()
 */
class ApiGatewayFactory
{
    /**
     * @var string
     */
    protected $clientId;
    /**
     * @var string
     */
    protected $clientSecret;
    /**
     * @var ServiceInterface
     */
    protected $service;
    /**
     * @var TokenStorageInterface
     */
    protected $storageAdapter;
    /**
     * @var string
     */
    protected $callbackURL;
    /**
     * @var array
     */
    protected $scopes;
    /**
     * @var ClientInterface
     */
    protected $httpClient;
    /**
     * @var array
     */
    protected $configuration;
    /**
     * @var Router
     */
    protected $router;

    /**
     * Set the client credentials when this class is instantiated
     *
     * @access public
     *
     * @param string $clientId Client credentials provided by Jawbone UP for the application
     * @param string $clientSecret The application's client_secret issued by Jawbone UP
     * @param string $callbackURL Callback URL to provide to Jawbone UP
     * @param array  $configuration Configurable items
     * @param Router $router
     */
    public function __construct($clientId, $clientSecret, $callbackURL, array $scopes, $configuration, Router $router)
    {
        $this->clientId       = $clientId;
        $this->clientSecret   = $clientSecret;
        $this->callbackURL    = $callbackURL;
        $this->configuration  = $configuration;
        $this->router         = $router;
        $this->scopes         = $scopes;
    }

    /**
     * Set client credentials
     *
     * @access public
     *
     * @param string $clientId Client credentials provided by Jawbone UP for the application
     * @param string $clientSecret The application's client_secret issued by Jawbone UP
     * @return self
     */
    public function setCredentials($clientId, $clientSecret)
    {
        $this->clientId     = $clientId;
        $this->clientSecret = $clientSecret;
        return $this;
    }

    /**
     * Set storage adapter.
     *
     * @access public
     *
     * @param TokenStorageInterface $adapter
     * @return self
     */
    public function setStorageAdapter(TokenStorageInterface $adapter)
    {
        $this->storageAdapter = $adapter;
        return $this;
    }

    /**
     * Get storage adapter.
     *
     * @access public
     *
     * @return TokenStorageInterface
     */
    public function getStorageAdapter()
    {
        return $this->storageAdapter;
    }

    /**
     * Set callback URL.
     *
     * @access public
     * @version 0.5.0
     *
     * @param string $callbackURL
     * @throws JawboneException
     * @return self
     */
    public function setCallbackURL($callbackURL)
    {
        if(substr($callbackURL, 0, 1) == '/' && substr($callbackURL, 0, 2) != '//') $callbackURL = $this->router->getContext()->getBaseUrl().$callbackURL;
        if (!filter_var($callbackURL, FILTER_VALIDATE_URL)) throw new JawboneException('The provided callback URL ('.$callbackURL.') is not a valid URL.', 102);
        $this->callbackURL = $callbackURL;
        return $this;
    }

    /**
     * Set HTTP Client library for Fitbit service.
     *
     * @access public
     *
     * @param  ClientInterface $client
     * @return self
     */
    public function setHttpClient(ClientInterface $client)
    {
        $this->httpClient = $client;
        return $this;
    }

    /**
     * Open a Gateway
     *
     * @access public
     * @version 0.0.1
     *
     * @param $method
     * @param $parameters
     * @throws JawboneException
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        /** @var Stopwatch $timer */
        $timer = new Stopwatch();
        $timer->start('Establishing Gateway', 'Jawbone UP API');
        if (!preg_match('/^get.*Gateway$/', $method))
        {
            throw new JawboneException('Invalid API Gateway interface ('.$method.') requested.', 103);
        }
        if (count($parameters))
        {
            throw new JawboneException('API Gateway interfaces do not accept parameters.', 104);
        }
        $gatewayName = '\\'.__NAMESPACE__.'\\'.substr($method, 3);
        try
        {
            $gateway = new $gatewayName($this->configuration);
        }
        catch (\Exception $e)
        {
            $timer->stop('Establishing Gateway');
            throw new JawboneException('API Gateway could not open a gateway named '.$gatewayName.'.', 105);
        }
        $this->injectGatewayDependencies($gateway);
        $timer->stop('Establishing Gateway');
        return $gateway;
    }

    /**
     * Inject Dependencies into a Gateway Interface
     *
     * @access protected
     * @version 0.5.0
     *
     * @param EndpointGateway $gateway
     * @throws JawboneException
     * @return bool
     */
    protected function injectGatewayDependencies(EndpointGateway $gateway)
    {
        try
        {
            $gateway->setService($this->getService());
        }
        catch (\Exception $e)
        {
            throw new JawboneException('Could not inject gateway dependencies', 112, $e);
        }
        return true;
    }

    /**
     * Get Jawbone UP service
     *
     * @access protected
     * @version 0.0.1
     *
     * @throws JawboneException
     * @return ServiceInterface
     */
    protected function getService()
    {
        if (!$this->clientId)    throw new JawboneException('Cannot get service as the client id is empty.', 106);
        if (!$this->clientSecret) throw new JawboneException('Cannot get service as the client secret is empty.', 107);
        if (!$this->callbackURL)    throw new JawboneException('Cannot get service as the callback URL is empty.', 108);
        if (!$this->storageAdapter) throw new JawboneException('Cannot get service as it is missing a storage adapter.', 109);

        if (!$this->service)
        {
            try
            {
                $credentials = new Credentials(
                    $this->clientId,
                    $this->clientSecret,
                    $this->callbackURL
                );
            }
            catch (\Exception $e)
            {
                throw new JawboneException('Could not initialise the credentials.', 110, $e);
            }

            try
            {
                $factory = new ServiceFactory();
                if ($this->httpClient) $factory->setHttpClient($this->httpClient);
                $this->service = $factory->createService('JawboneUP', $credentials, $this->storageAdapter, $this->scopes);
            }
            catch (\Exception $e)
            {
                throw new JawboneException('Could not initialise service factory.', 111, $e);
            }
        }
        return $this->service;
    }
}
