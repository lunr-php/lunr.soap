<?php

/**
 * This file contains the LunrSoapClientHeaderTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2013 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Spark\Tests;

use SoapHeader;

/**
 * This class contains tests for the header functions of the LunrSoapClient class.
 *
 * @covers Lunr\Spark\LunrSoapClient
 */
class LunrSoapClientHeaderTest extends LunrSoapClientTestCase
{

    /**
     * Test createHeader() creates a header.
     *
     * @covers Lunr\Spark\LunrSoapClient::createHeader
     */
    public function testCreateHeaderCreatesHeader(): void
    {
        $namespace = 'ns';
        $name      = 'name';
        $data      = [ 'data' ];

        $result = $this->class->createHeader($namespace, $name, $data);

        $expectedHeader = new SoapHeader($namespace, $name, $data);

        $this->assertEquals($expectedHeader, $result);
    }

    /**
     * Test setHeaders() sets client headers.
     *
     * @covers Lunr\Spark\LunrSoapClient::setHeaders
     */
    public function testSetHeadersSetsHeaders(): void
    {
        $headers = [
            new SoapHeader('ns1', 'name1', [ 'data1' ]),
            new SoapHeader('ns2', 'name2', [ 'data2' ]),
        ];

        $this->class->setHeaders($headers);

        if (PHP_MAJOR_VERSION >= 8 && PHP_MINOR_VERSION >= 1)
        {
            $parent = $this->reflection->getParentClass();

            $defaultHeaders = $parent->getProperty('__default_headers');
            $defaultHeaders->setAccessible(TRUE);

            $value = $defaultHeaders->getValue($this->class);
            $this->assertCount(2, $value);
        }
        else
        {
            $vars = get_object_vars($this->class);
            $this->assertCount(2, $vars['__default_headers']);
        }
    }

    /**
     * Test setHeaders() returns a self reference.
     *
     * @covers Lunr\Spark\LunrSoapClient::setHeaders
     */
    public function testSetHeadersReturnsSelfReference(): void
    {
        $value = $this->class->setHeaders([]);

        $this->assertInstanceOf('Lunr\Spark\LunrSoapClient', $value);
        $this->assertSame($this->class, $value);
    }

}

?>
