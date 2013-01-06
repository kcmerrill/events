<?php

/*
 * Copyright (c) 2012 kc merrill
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/*
 * Events is a quick and easy way to adding filtering and hooks into your system.
 * Much like wordpress filters/plugins.
 * kcmerrill/9.9.2012
 */

namespace kcmerrill\events;

class events {

    /**
     * Holds our triggers and our actions.
     * @var array $trigger
     */
    private $trigger = array();

    /**
     *  Executes a specific trigger, given a paramater.
     *  Dry means to perform a dry run, which will return
     *  an array of what is to be run. __CUSTOM__ returned for closures.
     *
     * @param string $trigger
     * @param mixed $param
     * @param boolean $dry
     * @return mixed $dry/$param. | If dry=true, an array with what will be run will be returned.
     */
    public function run($trigger, $param = false, $dry = false) {
        //If nothing is set for this trigger, then just return what we have
        if (!isset($this->trigger[$trigger])) {
            return $param;
        } else {
            $dry_run = array();
            //It's already in order, so just cycle through the callbacks.
            foreach ($this->trigger[$trigger] as $priority => $callbacks) {
                foreach ($callbacks as $callback) {
                    //Give our callback a name .. sortof.
                    $dry_run[] = is_string($callback) ? $callback : '__CALLBACK__';
                    if (!$dry) {
                        //Will run the call back, passing in $param.
                        $param = $callback($param);
                    }
                }
            }
            //Return $dry or return $param based on if it's a dry run or not.
            return $dry ? $dry_run : $param;
        }
    }

    /**
     * Registers a call back with a given trigger and priority.
     *
     * @param string $trigger
     * @param callback $callback | string or closure. As long as is_callable() passes.
     * @param int $priority
     */
    function register($trigger, $callback, $priority = 10, $unregister = false) {
        $unregister = $unregister ? $this->unRegister($trigger) : $unregister;
        //Lets setup the priority. Make sure it's an integer. If not, default to 10
        $priority = is_numeric($priority) ? (int) $priority : 10;
        //Is what you gave me callable? String or closure?
        if (is_callable($callback)) {
            //Yes, ok, so lets just set it and forget it.
            @$this->trigger[$trigger][$priority][] = $callback;
            //Is this a multi register? With Pipes? If so, lets break it up.
        } else if (is_string($callback) && stristr($callback, '|')) {
            //Multiple regester called. IE 'md5|strtolower|strtoupper'
            $callbacks = explode('|', $callback);
            //Regester each one specifically
                $this->register($trigger, $callbacks, $priority);
        } else if (is_array($callback)) {
            foreach ($callback as $cb) {
                $this->register($trigger, $cb, $priority);
            }
        } else {
            //No clue what you gave me. Lets just continue on.
        }
        //Lets sort our triggers by numbers ...
        if(isset($this->trigger[$trigger]) && is_array($this->trigger[$trigger])){
            //Sort based on priority(and priority is an numeric)
            ksort($this->trigger[$trigger], SORT_NUMERIC);
        }
    }

    /**
     * UnRegisters a call back(all, regardless of priorities)
     * @param string $trigger
     */
    function unRegister($trigger) {
        if (isset($this->trigger[$trigger])) {
            unset($this->trigger[$trigger]);
        }
        return true;
    }

    /**
     * Magical. Setup so we can use triggers as function names.
     *
     * @param string $trigger
     * @param mixed $param
     * @return mixed $param
     */
    function __call($trigger, $params) {
        $param = isset($params[0]) ? $params[0] : false;
        $dry_run = isset($params[1]) && is_bool($params[1]) ? $params[1] : false;
        return $this->run($trigger, $param, $dry_run);
    }

}