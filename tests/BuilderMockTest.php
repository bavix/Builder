<?php

namespace Tests;

use Bavix\Builder\Builder;
use Bavix\Lumper\Bind;
use Bavix\Processors\Factory;
use Bavix\Tests\Unit;
use Psr\Http\Message\ServerRequestInterface;

class BuilderMock extends Builder
{

    /**
     * @return Factory
     */
    public function factory(): Factory
    {
        return Bind::once(__METHOD__ . __LINE__, function () {
            return new Factory($this->config()->get('factory2'));
        });
    }

}

class BuilderMockTest extends Unit
{

    /**
     * @var Builder
     */
    protected $builder;

    public function setUp()
    {
        parent::setUp();

        $this->builder = new BuilderMock(__DIR__);
    }

    public function testMock()
    {
        $this->assertInstanceOf(ServerRequestInterface::class, $this->builder->request());
    }

}
