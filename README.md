# porkchopsandwiches/preserialiser
===

A simple PHP Preserialiser, use before serialising data into JSON, XML, etc. Recursively iterates through values where applicable.


	use PorkChopSandwiches\Preserialiser\Preserialiser;
	use PorkChopSandwiches\Preserialiser\Preserialisable;

	class Example implements Preserialisable {
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
	$ex = new Example();
	
	$p -> preserialise($ex); # => array("a" => "foo")
	$p -> preserialise($ex, array("include_b" => true)); # => array("a" => "foo", "b" => "bar")
