<?php

/**
 * This file contains the LunrSoapClientPrepareLogHeaderTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2025 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Spark\Tests;

use Error;

/**
 * This class contains tests for the LunrSoapClient class.
 *
 * @covers Lunr\Spark\LunrSoapClient
 */
class LunrSoapClientPrepareLogHeaderTest extends LunrSoapClientTestCase
{

    /**
     * Test that prepareLogHeader() returns NULL in case of NULL input.
     *
     * @requires extension http
     * @requires function http\Header::parse
     * @covers   Lunr\Spark\LunrSoapClient::prepareLogHeader
     */
    public function testPrepareLogHeaderReturnsNullIfHeaderIsNull(): void
    {
        $this->setReflectionPropertyValue('header', $this->header);

        $this->mockMethod([ $this->header, 'parse' ], fn() => []);

        $method = $this->getReflectionMethod('prepareLogHeader');
        $this->assertNull($method->invoke($this->class, NULL));

        $this->unmockMethod([ $this->header, 'parse' ]);
    }

    /**
     * Test that prepareLogHeader() returns NULL in case of invalid input.
     *
     * @requires extension http
     * @requires function http\Header::parse
     * @covers   Lunr\Spark\LunrSoapClient::prepareLogHeader
     */
    public function testPrepareLogHeaderReturnsNullIfHeaderIsInvalid(): void
    {
        $this->setReflectionPropertyValue('header', $this->header);

        $message = "http\Header::parse(): Failed to parse headers: unexpected end of input at pos 3 of 'Foo'";
        $this->mockMethod([ $this->header, 'parse' ], function () use ($message) { throw new Error($message); });

        $method = $this->getReflectionMethod('prepareLogHeader');
        $this->assertNull($method->invoke($this->class, 'Foo'));

        $this->unmockMethod([ $this->header, 'parse' ]);
    }

    /**
     * Test that prepareLogHeader() succeeds.
     *
     * @requires extension http
     * @requires function http\Header::parse
     * @covers   Lunr\Spark\LunrSoapClient::prepareLogHeader
     */
    public function testPrepareLogHeaderSucceeds(): void
    {
        $this->setReflectionPropertyValue('header', $this->header);

        $request = json_decode(file_get_contents(TEST_STATICS . '/Spark/request.json'), TRUE)[0];

        $expected = [
            'Host'           => 'www.xyz.org',
            'Content-Type'   => 'text/xml; charset=utf-8',
            'Content-Length' => 'nnn',
        ];

        $this->mockMethod([ $this->header, 'parse' ], fn() => $expected);

        $method = $this->getReflectionMethod('prepareLogHeader');
        $this->assertSame(json_encode($expected), $method->invoke($this->class, $request['requestHeaders']));

        $this->unmockMethod([ $this->header, 'parse' ]);
    }

}

?>
