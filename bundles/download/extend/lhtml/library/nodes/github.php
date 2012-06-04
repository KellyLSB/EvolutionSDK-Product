<?php

namespace Bundles\LHTML\Nodes;
use Bundles\LHTML\Node;
use Exception;
use e;

/**
 * LHTML Guthub Tag
 * @author Kelly Becker
 */
class GitHub extends Node {

	/**
	 * Process when the node is ready
	 * @author Kelly Becker
	 */
	public function ready() {
		$this->element = false;
		$repo = $this->attributes['repo'];
		$branch = $this->attributes['branch'];
		$commit = array_shift(array_shift(json_decode(file_get_contents("http://github.com/api/v2/json/commits/list/$repo/$branch"), true)));
		$this->source(':gh', $commit);
	}
	
}