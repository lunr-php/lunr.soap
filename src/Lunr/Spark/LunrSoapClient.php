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
use Lunr\Ticks\EventLogging\EventLoggerInterface;
use Lunr\Ticks\TracingControllerInterface;
use Lunr\Ticks\TracingInfoInterface;
use SoapClient;
use SoapHeader;

/**
 * Wrapper around SoapClient class.
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
     * @var array
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
     * @param string $wsdl    WSDL url
     * @param array  $options SOAP client options
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
     * @param array  $data      Header data
     *
     * @deprecated Use createHeader() instead
     *
     * @return SoapHeader Header created
     */
    public function create_header(string $namespace, string $name, array $data): SoapHeader
    {
        return $this->createHeader($namespace, $name, $data);
    }

    /**
     * Create a SoapHeader.
     *
     * @param string $namespace Header namespace
     * @param string $name      Header name
     * @param array  $data      Header data
     *
     * @return SoapHeader Header created
     */
    public function createHeader(string $namespace, string $name, array $data): SoapHeader
    {
        return new SoapHeader($namespace, $name, $data);
    }

    /**
     * Set the client headers.
     *
     * @param array|SoapHeader|null $headers Headers to set
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
     * @param array|SoapHeader|null $headers Headers to set
     *
     * @return static Self reference
     */
    public function setHeaders(array|SoapHeader|null $headers = NULL): static
    {
        $this->__setSoapHeaders($headers);

        return $this;
    }

}

?>
