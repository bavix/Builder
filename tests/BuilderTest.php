<?php

namespace Tests;

use Bavix\Flow\Flow;
use Bavix\Builder\Builder;
use Bavix\Config\Config;
use Bavix\Context\Cookies;
use Bavix\Context\Session;
use Bavix\Processors\Factory;
use Bavix\Router\Router;
use Bavix\Tests\Unit;
use Psr\Http\Message\ServerRequestInterface;

class BuilderTest extends Unit
{

    /**
     * @var Builder
     */
    protected $builder;

    public function setUp()
    {
        parent::setUp();

        $this->builder = new Builder(__DIR__);
    }

    public function testConfig()
    {
        $this->assertInstanceOf(Config::class, $this->builder->config());
    }

    public function testRouter()
    {
        $this->assertInstanceOf(Router::class, $this->builder->router());
    }

    public function testRequest()
    {
        $this->assertInstanceOf(ServerRequestInterface::class, $this->builder->request());
    }

    public function testSession()
    {
        $this->assertInstanceOf(Session::class, $this->builder->session());
    }

    public function testCookies()
    {
        $this->assertInstanceOf(Cookies::class, $this->builder->cookies());
    }

    public function testFactory()
    {
        $this->assertInstanceOf(Factory::class, $this->builder->factory());
    }

    public function testFlow()
    {
        $this->assertInstanceOf(Flow::class, $this->builder->flow());
    }

}
