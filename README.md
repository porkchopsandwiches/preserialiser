# porkchopsandwiches/preserialiser

A simple PHP Preserialiser, use before serialising data into JSON, XML, etc. Recursively iterates through values where applicable.

## Install via Composer

Add repo and require to composer.json:

```js
{
	"repositories": [
		{
			"type": "vcs",
			"url": "https://github.com/porkchopsandwiches/preserialiser"
		}
	],
	"require": {
		"porkchopsandwiches/preserialiser": "dev-master"
	}
}
```

## Basic usage

```php
use PorkChopSandwiches\Preserialiser\Preserialiser;

$p = new Preserialiser();
$p -> preserialise(1); # => 1
$p -> preserialise("string"); # => "string"
$p -> preserialise(array(1, true, "three")); # => array(1, true, "three")

$obj = new stdClass;
$obj -> prop = "value";
$p -> preserialise($obj); # => array("prop" => "value")

class ExampleA {
	private $a = "foo";
	public $b = "bar";
}
$p -> preserialise(new ExampleA()); # => array("a" => "bar")
```

## `Preserialisable` usage

```php

use PorkChopSandwiches\Preserialiser\Preserialiser;
use PorkChopSandwiches\Preserialiser\Preserialisable;

class ExampleB implements Preserialisable {
	private $a = "foo";
	private $b = "bar";
	
	public function preserialise (array $args = array()) {
		$data = array(
			"a" => $this -> a
		);
			
		if (array_key_exists("include_b", $args) && !!$args["include_b"]) {
			$data["b"] = $this -> b;
		}
			
		return $data;
	}
}
	
$p = new Preserialiser();
$ex = new ExampleB();
	
$p -> preserialise($ex);
# => array("a" => "foo")
$p -> preserialise($ex, array("include_b" => true));
# => array("a" => "foo", "b" => "bar")
```

## Recursive usage

```php
use PorkChopSandwiches\Preserialiser\Preserialiser;
use PorkChopSandwiches\Preserialiser\Preserialisable;

class ExampleParent implements Preserialisable {
	private $children = array();
	
	public function addChild(ExampleChild $child) {
		$child -> setParent($this);
		$this -> children[] = $child;
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

class ExampleChild implements Preserialisable {
	private $parent = null;
	
	public function setParent(ExampleParent $parent) {
		$this -> parent = $parent;
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

$p = new Preserialiser();
$parent = new ExampleParent();
$child = new ExampleChild();
$parent -> addChild($child);

$p -> preserialise($parent);
# => array("type" => "parent")
$p -> preserialise($parent, array("include_children" => true));
# => array("type" => "parent", "children" => array(array("type" => "child")))
$p -> preserialise($child, array("include_parent" => true));
# => array("type" => "child", "parent" => array("type" => "parent"))
$p -> preserialise($parent, array("include_children" => true, "include_parent" => true));
# => throws PreserialiserMaxDepthException

```
