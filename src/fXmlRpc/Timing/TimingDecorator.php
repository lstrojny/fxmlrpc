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

use fXmlRpc\ClientInterface;
use fXmlRpc\AbstractDecorator;

final class TimingDecorator extends AbstractDecorator
{
    /**
     * @var TimerInterface
     */
    private $timer;

    /**
     * Create new client decorator to record timing information
     *
     * @param ClientInterface $wrapped
     * @param TimerInterface  $timer
     */
    public function __construct(ClientInterface $wrapped, TimerInterface $timer)
    {
        parent::__construct($wrapped);
        $this->timer = $timer;
    }

    /** {@inheritdoc} */
    public function call($methodName, array $arguments = [])
    {
        $startTime = microtime(true);
        $result = parent::call($methodName, $arguments);
        $this->timer->recordTiming(microtime(true) - $startTime, $methodName, $arguments);

        return $result;
    }
}
