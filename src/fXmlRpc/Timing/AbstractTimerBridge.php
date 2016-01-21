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
namespace fXmlRpc\Timing;

/**
 * Base class for timing bridges
 *
 * Base class for bridging between timing information and various logger
 * implementations.
 */
abstract class AbstractTimerBridge implements TimerInterface
{
    /** @var object */
    protected $logger;

    /** @var array|integer */
    protected $level;

    /** @var string */
    protected $messageTemplate = 'fXmlRpc call took %01.10Fs';

    /**
     * Set log level
     *
     * @param mixed $level
     * @param mixed $default
     */
    protected function setLevel($level, $default)
    {
        if (is_array($level)) {
            krsort($level);
        }

        $this->level = $level ?: $default;
    }

    /**
     * Get log level by callTime
     *
     * @param  float $callTime
     * @return integer
     */
    protected function getLevel($callTime)
    {
        if (!is_array($this->level)) {
            return $this->level;
        }

        $level = null;
        foreach ($this->level as $threshold => $level) {
            if ($callTime >= $threshold) {
                return $level;
            }
        }

        return $level;
    }
}
