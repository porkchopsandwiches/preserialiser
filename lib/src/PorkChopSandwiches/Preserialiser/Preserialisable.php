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
 * Interface Preserialisable
 *
 * Classes should implement this Interface in order to be eligible for Preserialiser processing.
 *
 * @author Cam Morrow
 *
 * @package PorkChopSandwiches\Preserialiser
 */
interface Preserialisable {

	/**
	 * @public preserialise() returns the value that should be serialised for this instance.
     *
     * @param array [$args] Additional parameters specified by the Preserialiser
	 *
	 * @return mixed
	 */
	public function preserialise (array $args = array());
}