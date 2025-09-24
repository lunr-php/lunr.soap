<?php

/**
 * This file contains the LunrSoapClientDoRequestTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2025 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Spark\Tests;

use Lunr\Ticks\AnalyticsDetailLevel;
use RuntimeException;
use SoapClient;

/**
 * This class contains tests for the LunrSoapClient class.
 *
 * @covers Lunr\Spark\LunrSoapClient
 */
class LunrSoapClientDoRequestTest extends LunrSoapClientTestCase
{

    /**
     * Test that __doRequest succeeds with analytics disabled.
     *
     * @covers Lunr\Spark\LunrSoapClient::__doRequest
     */
    public function testDoRequestSucceedsWithAnalyticsDisabled(): void
    {
        $request = json_decode(file_get_contents(TEST_STATICS . '/Spark/request.json'), TRUE)[0];

        $this->setReflectionPropertyValue('options', []);

        $this->mockMethod([ SoapClient::class, '__doRequest' ], fn() => $request['responseBody']);

        $this->eventLogger->expects($this->never())
                          ->method('newEvent');

        $this->class->__doRequest($request['requestBody'], 'https://www.example.com', 'action', 1);

        $this->unmockMethod([ SoapClient::class, '__doRequest' ]);
    }

    /**
     * Test that __doRequest succeeds.
     *
     * @covers Lunr\Spark\LunrSoapClient::__doRequest
     */
    public function testDoRequestWithTraceIDUnavailable(): void
    {
        $request = json_decode(file_get_contents(TEST_STATICS . '/Spark/request.json'), TRUE)[0];

        $parent = $this->reflection->getParentClass();

        $requestHeaders = $parent->getProperty('__last_request_headers');
        $requestHeaders->setAccessible(TRUE);
        $requestHeaders->setValue($this->class, $request['requestHeaders']);

        $responseHeaders = $parent->getProperty('__last_response_headers');
        $responseHeaders->setAccessible(TRUE);
        $responseHeaders->setValue($this->class, $request['responseHeaders']);

        $this->setReflectionPropertyValue('options', []);
        $this->setReflectionPropertyValue('header', $this->header);
        $this->setReflectionPropertyValue('analyticsDetailLevel', AnalyticsDetailLevel::Info);
        $this->setReflectionPropertyValue('tracingController', $this->controller);
        $this->setReflectionPropertyValue('eventLogger', $this->eventLogger);

        $expectedRequestHeaders = [
            'Host'           => 'www.xyz.org',
            'Content-Type'   => 'text/xml; charset=utf-8',
            'Content-Length' => 'nnn',
        ];

        $expectedResponseHeaders = [
            'Content-Type'   => 'text/xml; charset=utf-8',
            'Content-Length' => '402',
        ];

        $floatval  = 1734352683.3516;
        $stringval = '0.35160200 1734352683';

        $this->mockFunction('microtime', fn(bool $float) => $float ? $floatval : $stringval);

        $this->mockMethod([ SoapClient::class, '__doRequest' ], fn() => $request['responseBody']);

        $parseHeaders = function (string $headers) use ($expectedRequestHeaders, $expectedResponseHeaders) {
            if (strpos($headers, 'xyz') !== FALSE)
            {
                return $expectedRequestHeaders;
            }

            if ($headers === '')
            {
                return [];
            }

            return $expectedResponseHeaders;
        };

        $this->mockMethod([ $this->header, 'parse' ], $parseHeaders);

        $this->controller->shouldReceive('startChildSpan')
                         ->once();

        $this->controller->shouldReceive('getTraceId')
                         ->once()
                         ->andReturn(NULL);

        $this->controller->shouldNotReceive('getSpanId');

        $this->controller->shouldNotReceive('getParentSpanId');

        $this->controller->shouldNotReceive('getSpanSpecifictags');

        $this->controller->shouldReceive('stopChildSpan')
                         ->once();

        $this->eventLogger->expects($this->once())
                          ->method('newEvent')
                          ->with('outbound_requests_log')
                          ->willReturn($this->event);

        $this->event->expects($this->once())
                    ->method('recordTimestamp');

        $this->event->expects($this->never())
                    ->method('setTraceId');

        $this->event->expects($this->never())
                    ->method('setSpanId');

        $this->event->expects($this->never())
                    ->method('setParentSpanId');

        $this->event->expects($this->never())
                    ->method('addTags');

        $this->event->expects($this->never())
                    ->method('addFields');

        $this->event->expects($this->never())
                    ->method('record');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Trace ID not available!');

        $this->class->__doRequest($request['requestBody'], 'https://www.example.com', 'action', 1);

        $this->unmockFunction('microtime');
        $this->unmockMethod([ SoapClient::class, '__doRequest' ]);
        $this->unmockMethod([ $this->header, 'parse' ]);
    }

    /**
     * Test that __doRequest succeeds.
     *
     * @covers Lunr\Spark\LunrSoapClient::__doRequest
     */
    public function testDoRequestWithSpanIDUnavailable(): void
    {
        $request = json_decode(file_get_contents(TEST_STATICS . '/Spark/request.json'), TRUE)[0];

        $traceID = '7b333e15-aa78-4957-a402-731aecbb358e';

        $parent = $this->reflection->getParentClass();

        $requestHeaders = $parent->getProperty('__last_request_headers');
        $requestHeaders->setAccessible(TRUE);
        $requestHeaders->setValue($this->class, $request['requestHeaders']);

        $responseHeaders = $parent->getProperty('__last_response_headers');
        $responseHeaders->setAccessible(TRUE);
        $responseHeaders->setValue($this->class, $request['responseHeaders']);

        $this->setReflectionPropertyValue('options', []);
        $this->setReflectionPropertyValue('header', $this->header);
        $this->setReflectionPropertyValue('analyticsDetailLevel', AnalyticsDetailLevel::Info);
        $this->setReflectionPropertyValue('tracingController', $this->controller);
        $this->setReflectionPropertyValue('eventLogger', $this->eventLogger);

        $expectedRequestHeaders = [
            'Host'           => 'www.xyz.org',
            'Content-Type'   => 'text/xml; charset=utf-8',
            'Content-Length' => 'nnn',
        ];

        $expectedResponseHeaders = [
            'Content-Type'   => 'text/xml; charset=utf-8',
            'Content-Length' => '402',
        ];

        $floatval  = 1734352683.3516;
        $stringval = '0.35160200 1734352683';

        $this->mockFunction('microtime', fn(bool $float) => $float ? $floatval : $stringval);

        $this->mockMethod([ SoapClient::class, '__doRequest' ], fn() => $request['responseBody']);

        $parseHeaders = function (string $headers) use ($expectedRequestHeaders, $expectedResponseHeaders) {
            if (strpos($headers, 'xyz') !== FALSE)
            {
                return $expectedRequestHeaders;
            }

            if ($headers === '')
            {
                return [];
            }

            return $expectedResponseHeaders;
        };

        $this->mockMethod([ $this->header, 'parse' ], $parseHeaders);

        $this->controller->shouldReceive('startChildSpan')
                         ->once();

        $this->controller->shouldReceive('getTraceId')
                         ->once()
                         ->andReturn($traceID);

        $this->controller->shouldReceive('getSpanId')
                         ->once()
                         ->andReturn(NULL);

        $this->controller->shouldNotReceive('getParentSpanId');

        $this->controller->shouldNotReceive('getSpanSpecifictags');

        $this->controller->shouldReceive('stopChildSpan')
                         ->once();

        $this->eventLogger->expects($this->once())
                          ->method('newEvent')
                          ->with('outbound_requests_log')
                          ->willReturn($this->event);

        $this->event->expects($this->once())
                    ->method('recordTimestamp');

        $this->event->expects($this->once())
                    ->method('setTraceId')
                    ->with($traceID);

        $this->event->expects($this->never())
                    ->method('setSpanId');

        $this->event->expects($this->never())
                    ->method('setParentSpanId');

        $this->event->expects($this->never())
                    ->method('addTags');

        $this->event->expects($this->never())
                    ->method('addFields');

        $this->event->expects($this->never())
                    ->method('record');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Span ID not available!');

        $this->class->__doRequest($request['requestBody'], 'https://www.example.com', 'action', 1);

        $this->unmockFunction('microtime');
        $this->unmockMethod([ SoapClient::class, '__doRequest' ]);
        $this->unmockMethod([ $this->header, 'parse' ]);
    }

    /**
     * Test that __doRequest succeeds.
     *
     * @covers Lunr\Spark\LunrSoapClient::__doRequest
     */
    public function testDoRequestWithParentSpanIDUnavailable(): void
    {
        $request = json_decode(file_get_contents(TEST_STATICS . '/Spark/request.json'), TRUE)[0];

        $traceID = '7b333e15-aa78-4957-a402-731aecbb358e';
        $spanID  = '24ec5f90-7458-4dd5-bb51-7a1e8f4baafe';

        $parent = $this->reflection->getParentClass();

        $requestHeaders = $parent->getProperty('__last_request_headers');
        $requestHeaders->setAccessible(TRUE);
        $requestHeaders->setValue($this->class, $request['requestHeaders']);

        $responseHeaders = $parent->getProperty('__last_response_headers');
        $responseHeaders->setAccessible(TRUE);
        $responseHeaders->setValue($this->class, $request['responseHeaders']);

        $this->setReflectionPropertyValue('options', []);
        $this->setReflectionPropertyValue('header', $this->header);
        $this->setReflectionPropertyValue('analyticsDetailLevel', AnalyticsDetailLevel::Info);
        $this->setReflectionPropertyValue('tracingController', $this->controller);
        $this->setReflectionPropertyValue('eventLogger', $this->eventLogger);

        $expectedRequestHeaders = [
            'Host'           => 'www.xyz.org',
            'Content-Type'   => 'text/xml; charset=utf-8',
            'Content-Length' => 'nnn',
        ];

        $expectedResponseHeaders = [
            'Content-Type'   => 'text/xml; charset=utf-8',
            'Content-Length' => '402',
        ];

        $floatval  = 1734352683.3516;
        $stringval = '0.35160200 1734352683';

        $this->mockFunction('microtime', fn(bool $float) => $float ? $floatval : $stringval);

        $this->mockMethod([ SoapClient::class, '__doRequest' ], fn() => $request['responseBody']);

        $parseHeaders = function (string $headers) use ($expectedRequestHeaders, $expectedResponseHeaders) {
            if (strpos($headers, 'xyz') !== FALSE)
            {
                return $expectedRequestHeaders;
            }

            if ($headers === '')
            {
                return [];
            }

            return $expectedResponseHeaders;
        };

        $this->mockMethod([ $this->header, 'parse' ], $parseHeaders);

        $this->controller->shouldReceive('startChildSpan')
                         ->once();

        $this->controller->shouldReceive('getTraceId')
                         ->once()
                         ->andReturn($traceID);

        $this->controller->shouldReceive('getSpanId')
                         ->once()
                         ->andReturn($spanID);

        $this->controller->shouldReceive('getParentSpanId')
                         ->once()
                         ->andReturn(NULL);

        $this->controller->shouldReceive('getSpanSpecifictags')
                         ->once()
                         ->andReturn([ 'call' => 'controller/method' ]);

        $this->controller->shouldReceive('stopChildSpan')
                         ->once();

        $this->eventLogger->expects($this->once())
                          ->method('newEvent')
                          ->with('outbound_requests_log')
                          ->willReturn($this->event);

        $this->event->expects($this->once())
                    ->method('recordTimestamp');

        $this->event->expects($this->once())
                    ->method('setTraceId')
                    ->with($traceID);

        $this->event->expects($this->once())
                    ->method('setSpanId')
                    ->with($spanID);

        $this->event->expects($this->never())
                    ->method('setParentSpanId');

        $this->event->expects($this->once())
                    ->method('addTags')
                    ->with([
                        'type'   => 'SOAP',
                        'status' => 200,
                        'domain' => 'www.example.com',
                        'call'   => 'controller/method',
                    ]);

        $this->event->expects($this->once())
                    ->method('addFields')
                    ->with([
                        'url'            => 'https://www.example.com',
                        'startTimestamp' => 1734352683.3516,
                        'endTimestamp'   => 1734352683.3516,
                        'executionTime'  => 0.0,
                    ]);

        $this->event->expects($this->once())
                    ->method('record');

        $this->class->__doRequest($request['requestBody'], 'https://www.example.com', 'action', 1);

        $this->unmockFunction('microtime');
        $this->unmockMethod([ SoapClient::class, '__doRequest' ]);
        $this->unmockMethod([ $this->header, 'parse' ]);
    }

    /**
     * Test that __doRequest() logs header and option fields when AnalyticsDetailLevel is not Info.
     *
     * @covers Lunr\Spark\LunrSoapClient::__doRequest
     */
    public function testDoRequestLogsHeaderFields(): void
    {
        $request = json_decode(file_get_contents(TEST_STATICS . '/Spark/request.json'), TRUE)[0];

        $traceID      = '7b333e15-aa78-4957-a402-731aecbb358e';
        $spanID       = '24ec5f90-7458-4dd5-bb51-7a1e8f4baafe';
        $parentSpanID = '8b1f87b5-8383-4413-a341-7619cd4b9948';

        $parent = $this->reflection->getParentClass();

        $requestHeaders = $parent->getProperty('__last_request_headers');
        $requestHeaders->setAccessible(TRUE);
        $requestHeaders->setValue($this->class, $request['requestHeaders']);

        $responseHeaders = $parent->getProperty('__last_response_headers');
        $responseHeaders->setAccessible(TRUE);
        $responseHeaders->setValue($this->class, $request['responseHeaders']);

        $this->setReflectionPropertyValue('options', []);
        $this->setReflectionPropertyValue('header', $this->header);
        $this->setReflectionPropertyValue('analyticsDetailLevel', AnalyticsDetailLevel::Detailed);
        $this->setReflectionPropertyValue('tracingController', $this->controller);
        $this->setReflectionPropertyValue('eventLogger', $this->eventLogger);

        $expectedRequestHeaders = [
            'Host'           => 'www.xyz.org',
            'Content-Type'   => 'text/xml; charset=utf-8',
            'Content-Length' => 'nnn',
        ];

        $expectedResponseHeaders = [
            'Content-Type'   => 'text/xml; charset=utf-8',
            'Content-Length' => '402',
        ];

        $floatval  = 1734352683.3516;
        $stringval = '0.35160200 1734352683';

        $this->mockFunction('microtime', fn(bool $float) => $float ? $floatval : $stringval);

        $this->mockMethod([ SoapClient::class, '__doRequest' ], fn() => $request['responseBody']);

        $parseHeaders = function (string $headers) use ($expectedRequestHeaders, $expectedResponseHeaders) {
            if (strpos($headers, 'xyz') !== FALSE)
            {
                return $expectedRequestHeaders;
            }

            if ($headers === '')
            {
                return [];
            }

            return $expectedResponseHeaders;
        };

        $this->mockMethod([ $this->header, 'parse' ], $parseHeaders);

        $this->controller->shouldReceive('startChildSpan')
                         ->once();

        $this->controller->shouldReceive('getTraceId')
                         ->once()
                         ->andReturn($traceID);

        $this->controller->shouldReceive('getSpanId')
                         ->once()
                         ->andReturn($spanID);

        $this->controller->shouldReceive('getParentSpanId')
                         ->once()
                         ->andReturn($parentSpanID);

        $this->controller->shouldReceive('getSpanSpecifictags')
                         ->once()
                         ->andReturn([ 'call' => 'controller/method' ]);

        $this->controller->shouldReceive('stopChildSpan')
                         ->once();

        $this->eventLogger->expects($this->once())
                          ->method('newEvent')
                          ->with('outbound_requests_log')
                          ->willReturn($this->event);

        $this->event->expects($this->once())
                    ->method('recordTimestamp');

        $this->event->expects($this->once())
                    ->method('setTraceId')
                    ->with($traceID);

        $this->event->expects($this->once())
                    ->method('setSpanId')
                    ->with($spanID);

        $this->event->expects($this->once())
                    ->method('setParentSpanId')
                    ->with($parentSpanID);

        $this->event->expects($this->once())
                    ->method('addTags')
                    ->with([
                        'type'   => 'SOAP',
                        'status' => 200,
                        'domain' => 'www.example.com',
                        'call'   => 'controller/method',
                    ]);

        $this->event->expects($this->once())
                    ->method('addFields')
                    ->with([
                        'url'             => 'https://www.example.com',
                        'startTimestamp'  => 1734352683.3516,
                        'endTimestamp'    => 1734352683.3516,
                        'executionTime'   => 0.0,
                        'requestHeaders'  => json_encode($expectedRequestHeaders),
                        'responseHeaders' => json_encode($expectedResponseHeaders),
                        'options'         => '{"oneWay":false,"soapVersion":1}',
                        'requestBody'     => $request['requestBody'],
                        'responseBody'    => $request['responseBody'],
                    ]);

        $this->event->expects($this->once())
                    ->method('record');

        $return = $this->class->__doRequest($request['requestBody'], 'https://www.example.com', 'action', 1);

        $this->assertEquals($return, $request['responseBody']);

        $this->unmockFunction('microtime');
        $this->unmockMethod([ SoapClient::class, '__doRequest' ]);
        $this->unmockMethod([ $this->header, 'parse' ]);
    }

    /**
     * Test that __doRequest logs header and option fields when AnalyticsDetailLevel is not Info.
     *
     * @covers Lunr\Spark\LunrSoapClient::__doRequest
     */
    public function testDoRequestLogsEmptyHeaderFields(): void
    {
        $request = json_decode(file_get_contents(TEST_STATICS . '/Spark/request.json'), TRUE)[0];

        $traceID      = '7b333e15-aa78-4957-a402-731aecbb358e';
        $spanID       = '24ec5f90-7458-4dd5-bb51-7a1e8f4baafe';
        $parentSpanID = '8b1f87b5-8383-4413-a341-7619cd4b9948';

        $parent = $this->reflection->getParentClass();

        $requestHeaders = $parent->getProperty('__last_request_headers');
        $requestHeaders->setAccessible(TRUE);
        $requestHeaders->setValue($this->class, NULL);

        $responseHeaders = $parent->getProperty('__last_response_headers');
        $responseHeaders->setAccessible(TRUE);
        $responseHeaders->setValue($this->class, NULL);

        $this->setReflectionPropertyValue('options', []);
        $this->setReflectionPropertyValue('header', $this->header);
        $this->setReflectionPropertyValue('analyticsDetailLevel', AnalyticsDetailLevel::Detailed);
        $this->setReflectionPropertyValue('tracingController', $this->controller);
        $this->setReflectionPropertyValue('eventLogger', $this->eventLogger);

        $expectedRequestHeaders = [
            'Host'           => 'www.xyz.org',
            'Content-Type'   => 'text/xml; charset=utf-8',
            'Content-Length' => 'nnn',
        ];

        $expectedResponseHeaders = [
            'Content-Type'   => 'text/xml; charset=utf-8',
            'Content-Length' => '402',
        ];

        $floatval  = 1734352683.3516;
        $stringval = '0.35160200 1734352683';

        $this->mockFunction('microtime', fn(bool $float) => $float ? $floatval : $stringval);

        $this->mockMethod([ SoapClient::class, '__doRequest' ], fn() => $request['responseBody']);

        $parseHeaders = function (string $headers) use ($expectedRequestHeaders, $expectedResponseHeaders) {
            if (strpos($headers, 'xyz') !== FALSE)
            {
                return $expectedRequestHeaders;
            }

            if ($headers === '')
            {
                return [];
            }

            return $expectedResponseHeaders;
        };

        $this->mockMethod([ $this->header, 'parse' ], $parseHeaders);

        $this->controller->shouldReceive('startChildSpan')
                         ->once();

        $this->controller->shouldReceive('getTraceId')
                         ->once()
                         ->andReturn($traceID);

        $this->controller->shouldReceive('getSpanId')
                         ->once()
                         ->andReturn($spanID);

        $this->controller->shouldReceive('getParentSpanId')
                         ->once()
                         ->andReturn($parentSpanID);

        $this->controller->shouldReceive('getSpanSpecifictags')
                         ->once()
                         ->andReturn([ 'call' => 'controller/method' ]);

        $this->controller->shouldReceive('stopChildSpan')
                         ->once();

        $this->eventLogger->expects($this->once())
                          ->method('newEvent')
                          ->with('outbound_requests_log')
                          ->willReturn($this->event);

        $this->event->expects($this->once())
                    ->method('recordTimestamp');

        $this->event->expects($this->once())
                    ->method('setTraceId')
                    ->with($traceID);

        $this->event->expects($this->once())
                    ->method('setSpanId')
                    ->with($spanID);

        $this->event->expects($this->once())
                    ->method('setParentSpanId')
                    ->with($parentSpanID);

        $this->event->expects($this->once())
                    ->method('addTags')
                    ->with([
                        'type'   => 'SOAP',
                        'status' => NULL,
                        'domain' => 'www.example.com',
                        'call'   => 'controller/method',
                    ]);

        $this->event->expects($this->once())
                    ->method('addFields')
                    ->with([
                        'url'             => 'https://www.example.com',
                        'startTimestamp'  => 1734352683.3516,
                        'endTimestamp'    => 1734352683.3516,
                        'executionTime'   => 0.0,
                        'requestHeaders'  => NULL,
                        'responseHeaders' => NULL,
                        'options'         => '{"oneWay":false,"soapVersion":1}',
                        'requestBody'     => $request['requestBody'],
                        'responseBody'    => $request['responseBody'],
                    ]);

        $this->event->expects($this->once())
                    ->method('record');

        $return = $this->class->__doRequest($request['requestBody'], 'https://www.example.com', 'action', 1);

        $this->assertEquals($return, $request['responseBody']);

        $this->unmockFunction('microtime');
        $this->unmockMethod([ SoapClient::class, '__doRequest' ]);
        $this->unmockMethod([ $this->header, 'parse' ]);
    }

    /**
     * Test that __doRequest succeeds.
     *
     * @covers Lunr\Spark\LunrSoapClient::__doRequest
     */
    public function testDoRequestSucceeds(): void
    {
        $request = json_decode(file_get_contents(TEST_STATICS . '/Spark/request.json'), TRUE)[0];

        $traceID      = '7b333e15-aa78-4957-a402-731aecbb358e';
        $spanID       = '24ec5f90-7458-4dd5-bb51-7a1e8f4baafe';
        $parentSpanID = '8b1f87b5-8383-4413-a341-7619cd4b9948';

        $parent = $this->reflection->getParentClass();

        $requestHeaders = $parent->getProperty('__last_request_headers');
        $requestHeaders->setAccessible(TRUE);
        $requestHeaders->setValue($this->class, $request['requestHeaders']);

        $responseHeaders = $parent->getProperty('__last_response_headers');
        $responseHeaders->setAccessible(TRUE);
        $responseHeaders->setValue($this->class, $request['responseHeaders']);

        $this->setReflectionPropertyValue('options', []);
        $this->setReflectionPropertyValue('header', $this->header);
        $this->setReflectionPropertyValue('analyticsDetailLevel', AnalyticsDetailLevel::Info);
        $this->setReflectionPropertyValue('tracingController', $this->controller);
        $this->setReflectionPropertyValue('eventLogger', $this->eventLogger);

        $expectedRequestHeaders = [
            'Host'           => 'www.xyz.org',
            'Content-Type'   => 'text/xml; charset=utf-8',
            'Content-Length' => 'nnn',
        ];

        $expectedResponseHeaders = [
            'Content-Type'   => 'text/xml; charset=utf-8',
            'Content-Length' => '402',
        ];

        $floatval  = 1734352683.3516;
        $stringval = '0.35160200 1734352683';

        $this->mockFunction('microtime', fn(bool $float) => $float ? $floatval : $stringval);

        $this->mockMethod([ SoapClient::class, '__doRequest' ], fn() => $request['responseBody']);

        $parseHeaders = function (string $headers) use ($expectedRequestHeaders, $expectedResponseHeaders) {
            if (strpos($headers, 'xyz') !== FALSE)
            {
                return $expectedRequestHeaders;
            }

            if ($headers === '')
            {
                return [];
            }

            return $expectedResponseHeaders;
        };

        $this->mockMethod([ $this->header, 'parse' ], $parseHeaders);

        $this->controller->shouldReceive('startChildSpan')
                         ->once();

        $this->controller->shouldReceive('getTraceId')
                         ->once()
                         ->andReturn($traceID);

        $this->controller->shouldReceive('getSpanId')
                         ->once()
                         ->andReturn($spanID);

        $this->controller->shouldReceive('getParentSpanId')
                         ->once()
                         ->andReturn($parentSpanID);

        $this->controller->shouldReceive('getSpanSpecifictags')
                         ->once()
                         ->andReturn([ 'call' => 'controller/method' ]);

        $this->controller->shouldReceive('stopChildSpan')
                         ->once();

        $this->eventLogger->expects($this->once())
                          ->method('newEvent')
                          ->with('outbound_requests_log')
                          ->willReturn($this->event);

        $this->event->expects($this->once())
                    ->method('recordTimestamp');

        $this->event->expects($this->once())
                    ->method('setTraceId')
                    ->with($traceID);

        $this->event->expects($this->once())
                    ->method('setSpanId')
                    ->with($spanID);

        $this->event->expects($this->once())
                    ->method('setParentSpanId')
                    ->with($parentSpanID);

        $this->event->expects($this->once())
                    ->method('addTags')
                    ->with([
                        'type'   => 'SOAP',
                        'status' => 200,
                        'domain' => 'www.example.com',
                        'call'   => 'controller/method',
                    ]);

        $this->event->expects($this->once())
                    ->method('addFields')
                    ->with([
                        'url'            => 'https://www.example.com',
                        'startTimestamp' => 1734352683.3516,
                        'endTimestamp'   => 1734352683.3516,
                        'executionTime'  => 0.0,
                    ]);

        $this->event->expects($this->once())
                    ->method('record');

        $this->class->__doRequest($request['requestBody'], 'https://www.example.com', 'action', 1);

        $this->unmockFunction('microtime');
        $this->unmockMethod([ SoapClient::class, '__doRequest' ]);
        $this->unmockMethod([ $this->header, 'parse' ]);
    }

}

?>
