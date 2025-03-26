<?php

/**
 * This file contains the LunrSoapClient class.
 *
 * SPDX-FileCopyrightText: Copyright 2013 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Spark;

use SoapClient;
use SoapHeader;

/**
 * Wrapper around SoapClient class.
 */
class LunrSoapClient extends SoapClient
{

    /**
     * Constructor.
     */
    public function __construct()
    {
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
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
        parent::__construct($wsdl, $options);
        return $this;
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
