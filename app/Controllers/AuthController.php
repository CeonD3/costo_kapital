<?php 
namespace App\Controllers;

use App\Dows\AuthDow;

class AuthController extends BaseController {

    public function signin($request) {
		$dow = new AuthDow();
		return $dow->signin($request);
	}

	public function signup($request) {
		$dow = new AuthDow();
		return $dow->signup($request);
	}
	
	public function authcms($request) {
		$dow = new AuthDow();
		return $dow->authcms($request);
	}
}

?>