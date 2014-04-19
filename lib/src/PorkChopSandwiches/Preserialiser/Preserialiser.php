<?php

/*
 * This file is part of the Preserialiser library.
 *
 * Copyright (c) Cam Morrow
 *
 * Please view the LICENSE file that was distributed with this source code For the full copyright and license information.
 */

namespace PorkChopSandwiches\Preserialiser;
use \Iterator;


/**
 * Class Preserialiser
 *
 * Provides a generic recursive pre-serialiser.
 * When it encounters objects that implement the Preserialisable interface, it invokes preserialise() on them to get their serialisable value (ala JsonSerializable::jsonSerialize).
 * Also allows the passing of custom arguments (either at invocation or before), which are then passed on to preserialise(), allowing the object to customise the value it returns.
 *
 * @author Cam Morrow
 *
 * @package PorkChopSandwiches\Preserialiser
 */
class Preserialiser {

    /* @var array $default_args */
    private $default_args = array();
    /* @var integer $max_depth */
    private $max_depth = 20;

    /**
     * @constructor
     *
     * @param array     [$default_args] The initial value for the additional args that are passed to preserialise().
     * @param integer   [$max_depth]    How deep to recurse
     */
    public function __construct (array $default_args = array(), $max_depth = 20) {
        $this -> default_args = $default_args;
        $this -> max_depth = $max_depth;
    }

    /**
     * @public clearDefaultArgs() empties the array of default args. Chainable.
     *
     * @return Preserialiser
     */
    public function clearDefaultArgs () {
        $this -> default_args = array();
        return $this;
    }

    /**
     * @public setDefaultArgs() sets the default args, removing any existing ones. Chainable.
     *
     * @param array $default_args
     *
     * @return Preserialiser
     */
    public function setDefaultArgs (array $default_args) {
        $this -> default_args = $default_args;
        return $this;
    }

    /**
     * @public addDefaultArgs() adds additional default args, merged with the existing ones. Chainable.
     *
     * @param array $more_default_args
     *
     * @return Preserialiser
     */
    public function addDefaultArgs (array $more_default_args) {
        $this -> default_args = array_merge($this -> default_args, $more_default_args);
        return $this;
    }

    /**
     * @public getDefaultArgs() gets the current default args.
     *
     * @return array
     */
    public function getDefaultArgs () {
        return $this -> default_args;
    }

    /**
     * @public setMaxDepth() sets the maximum recursion depth. Chainable.
     *
     * @param integer   $max_depth
     *
     * @return Preserialiser
     */
    public function setMaxDepth ($max_depth) {
        $this -> max_depth = (int) abs($max_depth);
        return $this;
    }

    /**
     * @public getMaxDepth() gets the maximum recursion depth.
     *
     * @return integer
     */
    public function getMaxDepth () {
        return $this -> max_depth;
    }

    /**
     * @private isIterable() returns whether the passed variable can be iterated (i.e. is an array or an Iterator instance).
     *
     * @param mixed $v
     *
     * @return bool
     */
    static private function isIterable ($v) {
        return $v instanceof Iterator || is_array($v);
    }

    /**
     * @private serialiseIterable() performs serialisation of an iterable value.
     *
     * @param array|Iterator  $target       The variable to serialize the contents of. Must be an array, or implement Iterator
     * @param array           [$args]       Additional parameters to pass to preserialize() to modify the output
     * @param integer         [$max_depth]  The maximum depth. If the current depth exceeds this, throw an exception.
     * @param integer         [$depth]      The current depth, used to track recursion.
     *
     * @throws PreserialiserMaxDepthException
     *
     * @return array
     */
    static private function serialiseIterable ($target, array $args = array(), $max_depth = 0, $depth = 0) {
        $result = array();

        $depth++;

        if ($depth > $max_depth) {
            throw new PreserialiserMaxDepthException($max_depth);
        }

        foreach ($target as $key => $value) {

            # If value is a non-iterable object
            if (is_object($value) && !self::isIterable($value)) {

                # If value implements the Interface, invoke the method, otherwise just collect the object vars
                $value = ($value instanceof PreSerialisable) ? $value -> preserialise($args) : get_object_vars($value);
            }

            # If the value was Iterable, or was made so by collecting the values above, check its contents too
            if (self::isIterable($value)) {
                $value = self::serialiseIterable($value, $args, $max_depth, $depth);
            }

            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * @public preserialise() pre-serialises a value.
     *
     * @param mixed $target  The value to pre-serialise.
     * @param array [$args]  Optional additional parameters. Merged with (and overrides) any default args.
     *
     * @return mixed
     */
    public function preserialise ($target, array $args = array()) {

        # Include default args, but allow passed ones to override them
        $args = array_merge($this -> default_args, $args);

        # Serialise an array containing the target
        $result = self::serialiseIterable(array($target), $args, $this -> max_depth, 0);
        return array_shift($result);
    }
}