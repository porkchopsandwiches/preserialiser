<?php

/*
 * This file is part of the Preserialiser library.
 *
 * Copyright (c) Cam Morrow
 *
 * Please view the LICENSE file that was distributed with this source code For the full copyright and license information.
 */

namespace PorkChopSandwiches\Preserialiser;

/**
 * Class PreserialiserMaxDepthException
 *
 * @author Cam Morrow
 *
 * @package PorkChopSandwiches\Preserialiser
 */
class PreserialiserMaxDepthException extends \Exception {

    /**
     * @constructor
     *
     * @param integer          $max_depth
     * @param int              $code
     * @param \Exception|null  $previous
     */
    public function __construct($max_depth, $code = 0, \Exception $previous = null) {
        parent::__construct("The Preserialiser exceeded the maximum recursion depth of " . $max_depth . ".", $code, $previous);
    }
}