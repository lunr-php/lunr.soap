<?php

/**
 * This file contains the LunrSoapClientPrepareLogDataTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2025 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Spark\Tests;

use Lunr\Ticks\AnalyticsDetailLevel;

/**
 * This class contains tests for the LunrSoapClient class.
 *
 * @covers Lunr\Spark\LunrSoapClient
 */
class LunrSoapClientPrepareLogDataTest extends LunrSoapClientTestCase
{

    /**
     * Test that prepareLogData returns NULL.
     *
     * @covers Lunr\Spark\LunrSoapClient::prepareLogData
     */
    public function testPrepareLogDataReturnsNullIfDataIsNull(): void
    {
        $method = $this->getReflectionMethod('prepareLogData');

        $this->setReflectionPropertyValue('analyticsDetailLevel', AnalyticsDetailLevel::Info);
        $this->assertNull($method->invoke($this->class, NULL));

        $this->setReflectionPropertyValue('analyticsDetailLevel', AnalyticsDetailLevel::Detailed);
        $this->assertNull($method->invoke($this->class, NULL));

        $this->setReflectionPropertyValue('analyticsDetailLevel', AnalyticsDetailLevel::Full);
        $this->assertNull($method->invoke($this->class, NULL));
    }

    /**
     * Test that prepareLogData returns shortened data if data is too long and level is DEBUG.
     *
     * @covers Lunr\Spark\LunrSoapClient::prepareLogData
     */
    public function testPrepareLogDataReturnsShortenedData(): void
    {
        $this->setReflectionPropertyValue('analyticsDetailLevel', AnalyticsDetailLevel::Detailed);

        $data = '';

        for ($i = 0; $i < 600; $i++)
        {
            $data .= 'a';
        }

        $method = $this->getReflectionMethod('prepareLogData');
        $result = $method->invoke($this->class, $data);

        $this->assertSame(515, strlen($result));
        $this->assertSame('...', substr($result, 512, 515));
    }

    /**
     * Test that prepareLogData returns full data if data is below 512 characters long and level is DEBUG.
     *
     * @covers Lunr\Spark\LunrSoapClient::prepareLogData
     */
    public function testPrepareLogDataReturnsFullDataLevelDebug(): void
    {
        $this->setReflectionPropertyValue('analyticsDetailLevel', AnalyticsDetailLevel::Detailed);

        $data = '';

        for ($i = 0; $i < 400; $i++)
        {
            $data .= 'a';
        }

        $method = $this->getReflectionMethod('prepareLogData');
        $result = $method->invoke($this->class, $data);

        $this->assertSame(400, strlen($result));
        $this->assertNotSame('...', substr($result, 397, 400));
    }

    /**
     * Test that prepareLogData returns full data if level is FULL_DEBUG.
     *
     * @covers Lunr\Spark\LunrSoapClient::prepareLogData
     */
    public function testPrepareLogDataReturnsFullDataLevelFullDebug(): void
    {
        $this->setReflectionPropertyValue('analyticsDetailLevel', AnalyticsDetailLevel::Full);

        $data = '';

        for ($i = 0; $i < 600; $i++)
        {
            $data .= 'a';
        }

        $method = $this->getReflectionMethod('prepareLogData');
        $result = $method->invoke($this->class, $data);

        $this->assertSame(600, strlen($result));
        $this->assertNotSame('...', substr($result, 597, 600));
    }

}

?>
