<?php

/**
 * This file contains the LunrSoapClientTestCase class.
 *
 * SPDX-FileCopyrightText: Copyright 2013 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Spark\Tests;

use http\Header;
use Lunr\Halo\LunrBaseTestCase;
use Lunr\Spark\LunrSoapClient;
use Lunr\Ticks\EventLogging\EventInterface;
use Lunr\Ticks\EventLogging\EventLoggerInterface;
use Lunr\Ticks\TracingControllerInterface;
use Lunr\Ticks\TracingInfoInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the LunrSoapClient class.
 *
 * @covers Lunr\Spark\LunrSoapClient
 */
abstract class LunrSoapClientTestCase extends LunrBaseTestCase
{

    use MockeryPHPUnitIntegration;

    /**
     * Mock Instance of an event logger.
     * @var EventLoggerInterface&MockObject
     */
    protected EventLoggerInterface&MockObject $eventLogger;

    /**
     * Mock instance of a Controller
     * @var TracingControllerInterface&TracingInfoInterface&MockInterface
     */
    protected TracingControllerInterface&TracingInfoInterface&MockInterface $controller;

    /**
     * Mock Instance of an analytics event.
     * @var EventInterface&MockObject
     */
    protected EventInterface&MockObject $event;

    /**
     * Mock Instance of the Header class.
     * @var Header&MockObject
     */
    protected Header&MockObject $header;

    /**
     * Instance of the tested class.
     * @var LunrSoapClient
     */
    protected LunrSoapClient $class;

    /**
     * Testcase Constructor.
     */
    public function setUp(): void
    {
        $this->eventLogger = $this->getMockBuilder(EventLoggerInterface::class)
                                  ->getMock();

        $this->event = $this->getMockBuilder(EventInterface::class)
                            ->getMock();

        $this->controller = Mockery::mock(
                                TracingControllerInterface::class,
                                TracingInfoInterface::class,
                            );

        $this->header = $this->getMockBuilder(Header::class)
                             ->getMock();

        $this->class = new LunrSoapClient();

        parent::baseSetUp($this->class);
    }

    /**
     * Testcase Destructor.
     */
    public function tearDown(): void
    {
        unset($this->controller);
        unset($this->eventLogger);
        unset($this->event);
        unset($this->controller);
        unset($this->class);

        parent::tearDown();
    }

}

?>
