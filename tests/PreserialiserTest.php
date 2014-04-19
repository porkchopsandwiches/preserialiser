<?php

/*
 * This file is part of the Preserialiser library.
 *
 * Copyright (c) Cam Morrow
 *
 * Please view the LICENSE file that was distributed with this source code For the full copyright and license information.
 */

use PorkChopSandwiches\Preserialiser\Preserialiser;
use PorkChopSandwiches\Preserialiser\Preserialisable;

class PreserialiserImplementer implements Preserialisable {
    private $v = null;

    public function __construct ($v) {
        $this -> v = $v;
    }

    public function preserialise (array $args = array()) {
        return array("v" => $this -> v, "args" => $args);
    }
}

class PreserialiserTest extends \PHPUnit_Framework_TestCase {

    const COMP_VALUE_1  = "sick.liasons.raised.this.monumental.mark";
    const COMP_VALUE_2  = "the.sun.sets.forever.over.blackwater.park";

    public function testExists () {
        $preserialiser = new Preserialiser();
        $this -> assertTrue($preserialiser instanceof PreSerialiser);
    }

    /**
     * @depends testExists
     */
    public function testDefaultArgs () {
        $preserialiser = new Preserialiser(array("constructor_default_arg" => self::COMP_VALUE_1));

        $args = $preserialiser -> getDefaultArgs();
        $this -> assertArrayHasKey("constructor_default_arg", $args); # Has constructor arg
        $this -> assertEquals(self::COMP_VALUE_1, $args["constructor_default_arg"]); # Constructor arg has correct value
        $this -> assertEquals(1, count($args)); # 1 arg

        $preserialiser -> addDefaultArgs(array("added_default_arg" => self::COMP_VALUE_2));
        $args = $preserialiser -> getDefaultArgs();
        $this -> assertArrayHasKey("constructor_default_arg", $args); # Still has constructor arg
        $this -> assertEquals(self::COMP_VALUE_1, $args["constructor_default_arg"]); # Constructor arg still has original value
        $this -> assertArrayHasKey("added_default_arg", $args); # Has added arg
        $this -> assertEquals(self::COMP_VALUE_2, $args["added_default_arg"]); # Added arg has corect value
        $this -> assertEquals(2, count($args)); # 2 args

        $preserialiser -> setDefaultArgs(array("setted_default_arg" => self::COMP_VALUE_1)); # 'setted'?!
        $args = $preserialiser -> getDefaultArgs();
        $this -> assertArrayNotHasKey("constructor_default_arg", $args); # Constructor arg gone
        $this -> assertArrayHasKey("setted_default_arg", $args); # Has set arg
        $this -> assertEquals(self::COMP_VALUE_1, $args["setted_default_arg"]); # Set arg has corect value
        $this -> assertEquals(1, count($args)); # Only 1 arg

        $preserialiser -> clearDefaultArgs();
        $args = $preserialiser -> getDefaultArgs();
        $this -> assertEquals(0, count($args)); # No args any more
    }

    /**
     * @depends testDefaultArgs
     */
    public function testSimpleSerialising () {
        $preserialiser = new Preserialiser();

        $this -> assertEquals(1, $preserialiser -> preSerialise(1));
        $this -> assertEquals(true, $preserialiser -> preSerialise(true));
        $this -> assertEquals("test", $preserialiser -> preSerialise("test"));
        $this -> assertEquals(array(1, 2, 3), $preserialiser -> preSerialise(array(1, 2, 3)));

        $object = new stdClass;
        $object -> foo = self::COMP_VALUE_1;
        $this -> assertEquals(array("foo" => self::COMP_VALUE_1), $preserialiser -> preSerialise($object));

        $instance = new PreserialiserImplementer(self::COMP_VALUE_2);
        $result = $preserialiser -> preSerialise($instance);
        $this -> assertArrayHasKey("v", $result);
        $this -> assertArrayHasKey("args", $result);
        $this -> assertEquals(self::COMP_VALUE_2, $result["v"]);
        $this -> assertEquals(array(), $result["args"]);
    }
}