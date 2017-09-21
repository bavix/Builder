<?php

namespace Bavix\Builder;

use Bavix\Config\Config;
use Bavix\Context\Cookies;
use Bavix\Context\Session;
use Bavix\Lumper\Lumper;
use Bavix\Processors\Factory;
use Bavix\Router\Router;
use Bavix\SDK\Path;
use Psr\Http\Message\ServerRequestInterface;

class Builder extends Lumper
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
        return $this->once(function () {
            return new Config($this->root . 'etc');
        }, __METHOD__);
    }

    /**
     * @return Router
     */
    public function router(): Router
    {
        return $this->once(function () {
            return new Router($this->config()->get('resolver'));
        }, __METHOD__);
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
        return $this->once(function () {

            $factory = $this->factory()->request;

            if (method_exists($factory, 'createServerRequestFromGlobals'))
            {
                $request = $factory::createServerRequestFromGlobals();
            }
            else
            {
                $request = $this->_request();
            }

            $request = $request->withCookieParams(
                $this->cookies()->export()
            );

            if (method_exists($request, 'withRouter'))
            {
                return $request->withRouter($this->router());
            }

            return $request;
        });
    }

    /**
     * @return Session
     */
    public function session(): Session
    {
        return $this->once(function () {

            $content = \config('content');
            $slice   = $content->getSlice('session');

            return new Session($slice->getData('password'));

        }, __METHOD__);
    }

    /**
     * @return Cookies
     */
    public function cookies(): Cookies
    {
        return $this->once(function () {

            $content = \config('content');
            $slice   = $content->getSlice('cookies');

            return new Cookies($slice->getData('password'));

        }, __METHOD__);
    }

    /**
     * @return Factory
     */
    public function factory(): Factory
    {
        return $this->once(function () {
            return new Factory(\config('factory'));
        }, __METHOD__);
    }

}
