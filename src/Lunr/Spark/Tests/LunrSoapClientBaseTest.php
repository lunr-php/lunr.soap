<?php

/**
 * This file contains the LunrSoapClientBaseTest class.
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
class LunrSoapClientBaseTest extends LunrSoapClientTestCase
{

    /**
     * Test analytics are disabled by default.
     */
    public function testAnalyticsDisabled(): void
    {
        $this->assertPropertyEquals('analyticsDetailLevel', AnalyticsDetailLevel::None);
    }

}

?>
