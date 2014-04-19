<?php

namespace PorkChopSandwiches\Preserialiser;

class PreserialiserMaxDepthException extends \Exception {

    public function __construct($max_depth, $code = 0, \Exception $previous = null) {
        parent::__construct("The Preserialiser exceeded the maximum recursion depth of " . $max_depth . ".", $code, $previous);
    }
}