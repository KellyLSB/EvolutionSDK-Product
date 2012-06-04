<?php

namespace Bundles\Packages;
use Exception;
use e;

class Bundle {

	public function _on_router_route($path) {
		return;

		echo json_encode("NEED TO BUILD PACKAGE LIST");

		e\complete();
	}

}