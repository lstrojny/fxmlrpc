<?php
/**
 * Copyright (C) 2012
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

namespace FXMLRPC\Timing;

use Monolog\Logger;

class MonologTimerBridge implements TimerInterface
{
    /**
     * @var \Monolog\Logger
     */
    protected $logger;

    /**
     * @var int
     */
    protected $level;

    /**
     * @var string
     */
    protected $messageTemplate;

    /**
     * Create new monolog bridge
     *
     * Allows passing custom log level and message template (with sprintf() control characters) for log message
     * customization
     *
     * @param \Monolog\Logger $logger
     * @param null|array $level
     * @param null $messageTemplate
     */
    public function __construct(Logger $logger, $level = null, $messageTemplate = null)
    {
        $this->logger = $logger;
        $this->level = $level ?: Logger::DEBUG;
        if (is_array($this->level)) {
            krsort($this->level);
        }
        $this->messageTemplate = $messageTemplate ?: 'FXMLRPC call took %01.10Fs';
    }

    /**
     * @param float $callTime
     * @param string $method
     * @param array $arguments
     */
    public function recordTiming($callTime, $method, array $arguments)
    {
        $level = $this->getLevel($callTime);

        $this->logger->addRecord(
            $level,
            sprintf($this->messageTemplate, $callTime),
            array('xmlrpcMethod' => $method, 'xmlrpcArguments' => $arguments)
        );
    }

    /**
     * Get log level by callTime
     *
     * @param float $callTime
     * @return int
     */
    private function getLevel($callTime)
    {
        if (!is_array($this->level)) {
            return $this->level;
        }

        foreach ($this->level as $threshold => $level) {
            if ($callTime >= $threshold) {
                return $level;
            }
        }

        return $level;
    }
}
