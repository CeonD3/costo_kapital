<?php 
namespace App\Controllers;

use App\Dows\ReportDow;

class ReportController extends BaseController {

	public function kapital($request) {
		$dow = new ReportDow();
		return $this->renderHTML('export/index.twig', $dow->view($request));
	}

	public function valora($request) {
		$dow = new ReportDow();
		return $this->renderHTML('export/indexValora.twig', $dow->view($request));
	}

}

?>