<?php

/**
 * This file contains the LunrSoapClient class.
 *
 * SPDX-FileCopyrightText: Copyright 2013 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Spark;

use http\Header;
use Lunr\Ticks\AnalyticsDetailLevel;
use Lunr\Ticks\EventLogging\EventInterface;
use Lunr\Ticks\EventLogging\EventLoggerInterface;
use Lunr\Ticks\TracingControllerInterface;
use Lunr\Ticks\TracingInfoInterface;
use RuntimeException;
use SoapClient;
use SoapHeader;
use Throwable;

/**
 * Wrapper around SoapClient class.
 *
 * @phpstan-type SoapClientOptions array{
 *   authentication?: int,
 *   cache_wsdl?: int,
 *   classmap?: array<string, class-string>,
 *   compression?: int,
 *   connection_timeout?: int,
 *   encoding?: string,
 *   exceptions?: bool,
 *   features?: int,
 *   keep_alive?: bool,
 *   local_cert?: string,
 *   location?: string,
 *   login?: string,
 *   passphrase?: string,
 *   password?: string,
 *   proxy_host?: string,
 *   proxy_login?: string,
 *   proxy_password?: string,
 *   proxy_port?: string,
 *   soap_version?: int,
 *   ssl_method?: string,
 *   stream_context?: resource,
 *   style?: int,
 *   trace?: bool,
 *   typemap?: TypeMap[],
 *   uri?: string,
 *   use?: int,
 *   user_agent?: string,
 * }
 * @phpstan-type TypeMap array{
 *   from_xml: callable(string): object,
 *   to_xml: callable(object): string,
 *   type_name: string,
 *   type_ns: string,
 * }
 * @phpstan-import-type Tags from EventInterface
 * @phpstan-import-type Fields from EventInterface
 */
class LunrSoapClient extends SoapClient
{

    /**
     * Shared instance of the Header class.
     * @var Header
     */
    protected readonly Header $header;

    /**
     * Shared instance of an EventLogger class
     * @var EventLoggerInterface
     */
    protected readonly EventLoggerInterface $eventLogger;

    /**
     * Shared instance of a tracing controller
     * @var TracingControllerInterface&TracingInfoInterface
     */
    protected readonly TracingControllerInterface&TracingInfoInterface $tracingController;

    /**
     * The detail level for query profiling
     * @var AnalyticsDetailLevel
     */
    protected AnalyticsDetailLevel $analyticsDetailLevel;

    /**
     * Soap request options
     * @var SoapClientOptions
     */
    protected array $options;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->analyticsDetailLevel = AnalyticsDetailLevel::None;
        $this->options              = [];
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        unset($this->analyticsDetailLevel);
        unset($this->options);
    }

    /**
     * Inits the client.
     *
     * @param string            $wsdl    WSDL url
     * @param SoapClientOptions $options SOAP client options
     *
     * @return LunrSoapClient Self reference
     */
    public function init(string $wsdl, array $options): self
    {
        $this->options = $options;

        parent::__construct($wsdl, $options);
        return $this;
    }

    /**
     * Enable SOAP request analytics.
     *
     * @param EventLoggerInterface                            $eventLogger Instance of an event logger
     * @param TracingControllerInterface&TracingInfoInterface $controller  Instance of a tracing controller
     * @param Header                                          $header      Instance of a header class
     * @param AnalyticsDetailLevel                            $level       Analytics detail level (defaults to Info)
     *
     * @return void
     */
    public function enableAnalytics(
        EventLoggerInterface $eventLogger,
        TracingControllerInterface&TracingInfoInterface $controller,
        Header $header,
        AnalyticsDetailLevel $level = AnalyticsDetailLevel::Info,
    ): void
    {
        $this->eventLogger          = $eventLogger;
        $this->tracingController    = $controller;
        $this->header               = $header;
        $this->analyticsDetailLevel = $level;
    }

    /**
     * Create a SoapHeader.
     *
     * @param string $namespace Header namespace
     * @param string $name      Header name
     * @param mixed  $data      Header data
     *
     * @deprecated Use createHeader() instead
     *
     * @return SoapHeader Header created
     */
    public function create_header(string $namespace, string $name, mixed $data): SoapHeader
    {
        return $this->createHeader($namespace, $name, $data);
    }

    /**
     * Create a SoapHeader.
     *
     * @param string $namespace Header namespace
     * @param string $name      Header name
     * @param mixed  $data      Header data
     *
     * @return SoapHeader Header created
     */
    public function createHeader(string $namespace, string $name, mixed $data): SoapHeader
    {
        return new SoapHeader($namespace, $name, $data);
    }

    /**
     * Set the client headers.
     *
     * @param SoapHeader[]|SoapHeader|null $headers Headers to set
     *
     * @deprecated Use setHeaders() instead
     *
     * @return static Self reference
     */
    public function set_headers(array|SoapHeader|null $headers = NULL): static
    {
        return $this->setHeaders($headers);
    }

    /**
     * Set the client headers.
     *
     * @param SoapHeader[]|SoapHeader|null $headers Headers to set
     *
     * @return static Self reference
     */
    public function setHeaders(array|SoapHeader|null $headers = NULL): static
    {
        $this->__setSoapHeaders($headers);

        return $this;
    }

    /**
     * Performs a SOAP request
     *
     * @param string $request  The XML SOAP request.
     * @param string $location The URL to request.
     * @param string $action   The SOAP action.
     * @param int    $version  The SOAP version.
     * @param bool   $oneWay   If oneWay is set to true, this method returns nothing. Use this where a response is not expected.
     *
     * @return string|null The XML SOAP response.
     */
    public function __doRequest(string $request, string $location, string $action, int $version, bool $oneWay = FALSE): ?string
    {
        if ($this->analyticsDetailLevel === AnalyticsDetailLevel::None)
        {
            return parent::__doRequest($request, $location, $action, $version, $oneWay);
        }

        $this->tracingController->startChildSpan();
        $startTimestamp = microtime(TRUE);

        $response = parent::__doRequest($request, $location, $action, $version, $oneWay);

        $endTimestamp = microtime(TRUE);

        $fields = [
            'startTimestamp' => $startTimestamp,
            'endTimestamp'   => $endTimestamp,
            'executionTime'  => (float) bcsub((string) $endTimestamp, (string) $startTimestamp, 4),
            'url'            => $location,
        ];

        $responseHeaders = $this->__getLastResponseHeaders();

        if ($this->analyticsDetailLevel->atLeast(AnalyticsDetailLevel::Detailed))
        {
            $fields['requestHeaders']  = $this->prepareLogHeader($this->__getLastRequestHeaders());
            $fields['responseHeaders'] = $this->prepareLogHeader($responseHeaders);
            $fields['options']         = json_encode(array_merge($this->options, [ 'oneWay' => $oneWay, 'soapVersion' => $version ]));
            $fields['requestBody']     = $this->prepareLogData($request);
            $fields['responseBody']    = $this->prepareLogData($response);
        }

        preg_match('/HTTP\/\d\.\d\s*\K[\d]+/', $responseHeaders ?? '', $status);

        $tags = [
            'type'   => 'SOAP',
            'status' => (isset($status[0]) && is_numeric($status[0])) ? strval(intval($status[0])) : NULL,
            'domain' => parse_url($location, PHP_URL_HOST),
        ];

        $this->tracingController->stopChildSpan();

        $this->recordEvent($fields, $tags);

        return $response;
    }

    /**
     * Prepare data according to loglevel.
     *
     * @param string|null $data Data to prepare for logging.
     *
     * @return string|null $data
     */
    protected function prepareLogData(?string $data): ?string
    {
        if (is_null($data))
        {
            return NULL;
        }

        if ($this->analyticsDetailLevel === AnalyticsDetailLevel::Detailed && strlen($data) > 512)
        {
            return substr($data, 0, 512) . '...';
        }

        return $data;
    }

    /**
     * Prepare header data.
     *
     * @param string|null $header Headers to prepare for logging.
     *
     * @return string|null JSON encoded headers or NULL when headers empty or invalid
     */
    protected function prepareLogHeader(?string $header): ?string
    {
        try
        {
            $headers = $this->header->parse($header);
        }
        catch (Throwable $e)
        {
            $headers = FALSE;
        }

        if ($headers === FALSE || $headers === [])
        {
            return NULL;
        }

        return json_encode($headers);
    }

    /**
     * Finalize analytics.
     *
     * @param Fields $fields Field data
     * @param Tags   $tags   Tag data
     *
     * @return void
     */
    protected function recordEvent(array $fields, array $tags): void
    {
        $event = $this->eventLogger->newEvent('outbound_requests_log');

        $event->recordTimestamp();
        $event->setTraceId($this->tracingController->getTraceId() ?? throw new RuntimeException('Trace ID not available!'));
        $event->setSpanId($this->tracingController->getSpanId() ?? throw new RuntimeException('Span ID not available!'));
        $event->setParentSpanId($this->tracingController->getParentSpanId() ?? throw new RuntimeException('Parent Span ID not available!'));
        $event->addTags(array_merge($this->tracingController->getSpanSpecificTags(), $tags));
        $event->addFields($fields);
        $event->record();
    }

}

?>
