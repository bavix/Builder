<?php

namespace Bavix\Builder;

use Bavix\Config\Config;
use Bavix\Context\Cookies;
use Bavix\Context\Session;
use Bavix\Flow\Flow;
use Bavix\Http\ServerRequest;
use Bavix\Lumper\Bind;
use Bavix\Processors\Factory;
use Bavix\Router\Router;
use Bavix\SDK\Path;
use Bavix\Security\Password;
use Psr\Http\Message\ServerRequestInterface;

class Builder
{

    /**
     * @var string
     */
    protected $root;

    /**
     * Builder constructor.
     *
     * @param string $root
     */
    public function __construct(string $root)
    {
        $this->root = Path::slash($root);
    }

    /**
     * @return Config
     */
    public function config(): Config
    {
        return Bind::once(__METHOD__, function () {
            return new Config($this->root . 'etc');
        });
    }

    /**
     * @return Router
     */
    public function router(): Router
    {
        return Bind::once(__METHOD__, function () {
            return new Router($this->config()->get('resolver'));
        });
    }

    /**
     * @return ServerRequestInterface
     */
    private function _request(): ServerRequestInterface
    {
        $scheme = filter_input(INPUT_SERVER, 'REQUEST_SCHEME');
        $host   = filter_input(INPUT_SERVER, 'HTTP_HOST');
        $uri    = filter_input(INPUT_SERVER, 'REQUEST_URI');

        $uriObject = $this->factory()->uri
            ->createUri($scheme . '://' . $host . $uri);

        $request = $this->factory()->request->createServerRequest(
            filter_input(INPUT_SERVER, 'REQUEST_METHOD') ?? 'GET',
            $uriObject
        );

        $query = filter_input_array(INPUT_GET, FILTER_UNSAFE_RAW) ?: [];
        $data  = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW) ?: [];

        return $request
            ->withQueryParams($query)
            ->withParsedBody($data)
            ->withUploadedFiles($_FILES);
    }

    /**
     * @return ServerRequestInterface
     */
    public function request(): ServerRequestInterface
    {
        return Bind::once(__METHOD__, function () {

            $factory = $this->factory()->request;

            if (method_exists($factory, 'createServerRequestFromGlobals'))
            {
                $request = $factory::createServerRequestFromGlobals();
            }
            else
            {
                $request = $this->_request();
            }

            if ($request instanceof ServerRequest)
            {
                return $request
                    ->withCookiesContent($this->cookies())
                    ->withSessionContent($this->session())
                    ->withRouter($this->router());
            }

            return $request;
        });
    }

    /**
     * @return Session
     */
    public function session(): Session
    {
        return Bind::once(__METHOD__, function () {

            $content = $this->config()->get('content');
            $slice   = $content->getSlice('session');

            return new Session($slice->getData('password'));

        });
    }

    /**
     * @return Cookies
     */
    public function cookies(): Cookies
    {
        return Bind::once(__METHOD__, function () {

            $content = $this->config()->get('content');
            $slice   = $content->getSlice('cookies');

            return new Cookies($slice->getData('password'));

        });
    }

    /**
     * @return Factory
     */
    public function factory(): Factory
    {
        return Bind::once(__METHOD__, function () {
            return new Factory($this->config()->get('factory'));
        });
    }

    /**
     * @return Flow
     */
    public function flow(): Flow
    {
        return Bind::once(__METHOD__, function () {
            return new Flow(null, $this->config()->get('flow')->asArray());
        });
    }

    /**
     * @return Password
     */
    public function password(): Password
    {
        return Bind::once(__METHOD__, function () {

            $slice = $this->config()->get('password');

            return new Password(
                $slice->getData('algo', PASSWORD_DEFAULT),
                $slice->getData('options')
            );
        });
    }

}
