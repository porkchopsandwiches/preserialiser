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

class PreserialiserParentImplementer implements Preserialisable {

    /* @var PreserialiserChildImplementer[] $children */
    private $children = array();

    /**
     * @param PreserialiserChildImplementer $child
     *
     * @return PreserialiserParentImplementer
     */
    public function addChild (PreserialiserChildImplementer $child) {
        $this -> children[] = $child -> setParent($this);
        return $this;
    }

    public function preserialise (array $args = array()) {
        $data = array(
            "type" => "parent"
        );

        if (array_key_exists("include_children", $args) && !!$args["include_children"]) {
            $data["children"] = $this -> children;
        }

        return $data;
    }
}

class PreserialiserChildImplementer implements Preserialisable {
    /* @var PreserialiserParentImplementer $parent */
    private $parent = null;

    /**
     * @param PreserialiserParentImplementer $parent
     *
     * @return PreserialiserChildImplementer
     */
    public function setParent (PreserialiserParentImplementer $parent) {
        $this -> parent = $parent;
        return $this;
    }

    public function preserialise (array $args = array()) {
        $data = array(
            "type" => "child"
        );

        if (array_key_exists("include_parent", $args) && !!$args["include_parent"]) {
            $data["parent"] = $this -> parent;
        }

        return $data;
    }
}

class PreserialiserTest extends \PHPUnit_Framework_TestCase {

    const COMP_VALUE_1  = "sick.liasons.raised.this.monumental.mark";
    const COMP_VALUE_2  = "the.sun.sets.forever.over.blackwater.park";

    public function testExists () {
        $p = new Preserialiser();
        $this -> assertTrue($p instanceof PreSerialiser);
    }

    /**
     * @depends testExists
     */
    public function testDefaultArgs () {
        $p = new Preserialiser(array("constructor_default_arg" => self::COMP_VALUE_1));

        $args = $p -> getDefaultArgs();
        $this -> assertArrayHasKey("constructor_default_arg", $args); # Has constructor arg
        $this -> assertEquals(self::COMP_VALUE_1, $args["constructor_default_arg"]); # Constructor arg has correct value
        $this -> assertEquals(1, count($args)); # 1 arg

        $p -> addDefaultArgs(array("added_default_arg" => self::COMP_VALUE_2));
        $args = $p -> getDefaultArgs();
        $this -> assertArrayHasKey("constructor_default_arg", $args); # Still has constructor arg
        $this -> assertEquals(self::COMP_VALUE_1, $args["constructor_default_arg"]); # Constructor arg still has original value
        $this -> assertArrayHasKey("added_default_arg", $args); # Has added arg
        $this -> assertEquals(self::COMP_VALUE_2, $args["added_default_arg"]); # Added arg has corect value
        $this -> assertEquals(2, count($args)); # 2 args

        $p -> setDefaultArgs(array("setted_default_arg" => self::COMP_VALUE_1)); # 'setted'?!
        $args = $p -> getDefaultArgs();
        $this -> assertArrayNotHasKey("constructor_default_arg", $args); # Constructor arg gone
        $this -> assertArrayHasKey("setted_default_arg", $args); # Has set arg
        $this -> assertEquals(self::COMP_VALUE_1, $args["setted_default_arg"]); # Set arg has corect value
        $this -> assertEquals(1, count($args)); # Only 1 arg

        $p -> clearDefaultArgs();
        $args = $p -> getDefaultArgs();
        $this -> assertEquals(0, count($args)); # No args any more
    }

    /**
     * @depends testDefaultArgs
     */
    public function testSimpleSerialising () {
        $p = new Preserialiser();

        $this -> assertEquals(1, $p -> preSerialise(1));
        $this -> assertEquals(true, $p -> preSerialise(true));
        $this -> assertEquals("test", $p -> preSerialise("test"));
        $this -> assertEquals(array(1, 2, 3), $p -> preSerialise(array(1, 2, 3)));

        $object = new stdClass;
        $object -> foo = self::COMP_VALUE_1;
        $this -> assertEquals(array("foo" => self::COMP_VALUE_1), $p -> preSerialise($object));

        $instance = new PreserialiserImplementer(self::COMP_VALUE_2);
        $result = $p -> preSerialise($instance);
        $this -> assertArrayHasKey("v", $result);
        $this -> assertArrayHasKey("args", $result);
        $this -> assertEquals(self::COMP_VALUE_2, $result["v"]);
        $this -> assertEquals(array(), $result["args"]);
    }

    /**
     * @depends testSimpleSerialising
     */
    public function testDeepSerialising () {
        $p = new Preserialiser();

        $parent     = new PreserialiserParentImplementer();
        $child_a    = new PreserialiserChildImplementer();
        $child_b    = new PreserialiserChildImplementer();
        $parent -> addChild($child_a) -> addChild($child_b);

        # Render the parent, without children
        # 'include_parent' is on, but will never be used because we do not ask it to descend into the child nodes
        $result = $p -> preserialise($parent, array("include_parent" => true));
        $this -> assertArrayNotHasKey("children", $result); # No children because that arg was not set

        # Now render again, this time with children
        $result = $p -> preserialise($parent, array("include_children" => true));
        $this -> assertArrayHasKey("children", $result); # Children because that arg was set
        $this -> assertEquals(2, count($result["children"]));

        # Now render one of the children
        $result = $p -> preserialise($child_a, array("include_parent" => true));
        $this -> assertArrayHasKey("parent", $result);
        $this -> assertEquals("parent", $result["parent"]["type"]);
    }

    /**
     * @depends testDeepSerialising
     */
    public function testRecursiveSerialising () {

        $p = new Preserialiser();
        $p -> setMaxDepth(10);
        $parent     = new PreserialiserParentImplementer();
        $child_a    = new PreserialiserChildImplementer();
        $child_b    = new PreserialiserChildImplementer();
        $parent -> addChild($child_a) -> addChild($child_b);

        # Render parent/child relationships from both sides, resulting in a recursion exception
        $this -> setExpectedException("PorkChopSandwiches\\Preserialiser\\PreserialiserMaxDepthException");
        $p -> preserialise($parent, array("include_children" => true, "include_parent" => true));
    }
}