<?php 
namespace App\Controllers;

use App\Dows\FinanceDow;

class FinanceController extends BaseController {

    public function industries($request) {
		$dow = new FinanceDow();
		return $dow->industries($request);
	}

	public function removeProject($request) {
		$dow = new FinanceDow();
		return $dow->removeProject($request);
	}
	
}

?>