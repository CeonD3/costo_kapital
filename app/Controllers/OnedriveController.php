<?php

namespace App\Controllers;

use App\Dows\OnedriveDow;

class OnedriveController extends BaseController {

	public function signin($request) {
		$dow = new OnedriveDow();
		return Response::json($dow->signin($request));
	}

	public function authUrl($request) {
		$dow = new OnedriveDow();
		return Response::json($dow->authUrl($request));
	}

	public function signout($request) {
		$dow = new OnedriveDow();
		return Response::json($dow->signout($request));
	}

	public function overview($request) {
		$dow = new OnedriveDow();
		return Response::json($dow->overview($request));
	}

}

?>