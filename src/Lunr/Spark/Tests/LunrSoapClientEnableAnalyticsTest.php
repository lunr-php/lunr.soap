<?php

/**
 * This file contains the LunrSoapClientEnableAnalyticsTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2025 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Spark\Tests;

use Lunr\Ticks\AnalyticsDetailLevel;

/**
 * This class contains tests for the LunrSoapClient class.
 *
 * @covers Lunr\Spark\LunrSoapClient
 */
class LunrSoapClientEnableAnalyticsTest extends LunrSoapClientTestCase
{

    /**
     * Test enableAnalytics() with the default analytics detail level.
     *
     * @covers Lunr\Spark\LunrSoapClient::enableAnalytics
     */
    public function testEnableAnalyticsWithDefaultAnalyticsDetailLevel(): void
    {
        $this->class->enableAnalytics($this->eventLogger, $this->controller, $this->header);

        $this->assertPropertySame('eventLogger', $this->eventLogger);
        $this->assertPropertySame('tracingController', $this->controller);
        $this->assertPropertySame('header', $this->header);

        $this->assertPropertyEquals('analyticsDetailLevel', AnalyticsDetailLevel::Info);
    }

    /**
     * Test enableAnalytics() with a custom analytics detail level.
     *
     * @covers Lunr\Spark\LunrSoapClient::enableAnalytics
     */
    public function testEnableAnalyticsWithCustomAnalyticsDetailLevel(): void
    {
        $this->class->enableAnalytics($this->eventLogger, $this->controller, $this->header, AnalyticsDetailLevel::Full);

        $this->assertPropertySame('eventLogger', $this->eventLogger);
        $this->assertPropertySame('tracingController', $this->controller);
        $this->assertPropertySame('header', $this->header);

        $this->assertPropertyEquals('analyticsDetailLevel', AnalyticsDetailLevel::Full);
    }

}

?>
