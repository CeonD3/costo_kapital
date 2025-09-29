<?php 
namespace App\Controllers;

use App\Dows\ConfigurationDow;

class ConfigurationController extends BaseController {

	public function show($request) {
		$dow = new ConfigurationDow();
		return $dow->show($request);
	}

	public function update($request) {
		$dow = new ConfigurationDow();
		return $dow->update($request);
	}

}

?>