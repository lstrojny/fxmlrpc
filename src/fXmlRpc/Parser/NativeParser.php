<?php
/**
 * Copyright (C) 2012-2016
 * Lars Strojny, InterNations GmbH <lars.strojny@internations.org>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */
namespace fXmlRpc\Parser;

use DateTime;
use DateTimeZone;
use fXmlRpc\Exception\FaultException;
use fXmlRpc\Exception\MissingExtensionException;
use fXmlRpc\Value\Base64;

final class NativeParser implements ParserInterface
{
    /**
     * @var bool
     */
    private $validateResponse;

    public function __construct($validateResponse = true)
    {
        if (!extension_loaded('xmlrpc')) {
            throw MissingExtensionException::extensionMissing('xmlrpc');
        }
        $this->validateResponse = $validateResponse;
    }

    /** {@inheritdoc} */
    public function parse($xmlString)
    {
        if ($this->validateResponse) {
            XmlChecker::validXml($xmlString);
        }

        $result = xmlrpc_decode($xmlString, 'UTF-8');

        $toBeVisited = [&$result];
        while (isset($toBeVisited[0]) && $value = &$toBeVisited[0]) {

            $type = gettype($value);
            if ($type === 'object') {
                $xmlRpcType = $value->{'xmlrpc_type'};
                if ($xmlRpcType === 'datetime') {
                    $value = DateTime::createFromFormat(
                        'Ymd\TH:i:s',
                        $value->scalar,
                        isset($timezone) ? $timezone : $timezone = new DateTimeZone('UTC')
                    );

                } elseif ($xmlRpcType === 'base64') {
                    if ($value->scalar !== '') {
                        $value = Base64::serialize($value->scalar);
                    } else {
                        $value = null;
                    }
                }

            } elseif ($type === 'array') {
                foreach ($value as &$element) {
                    $toBeVisited[] = &$element;
                }
            }

            array_shift($toBeVisited);
        }

        if (is_array($result)) {
            reset($result);
            if (xmlrpc_is_fault($result)) {
                throw FaultException::fault($result);
            }
        }

        return $result;
    }
}
