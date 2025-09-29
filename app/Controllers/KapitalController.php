<?php 
namespace App\Controllers;

use App\Dows\KapitalDow;

class KapitalController extends BaseController {

    public function form($request) {
		$dow = new KapitalDow();
		return $dow->form($request);
	}

	public function store($request) {
		$dow = new KapitalDow();
		return $dow->store($request);
	}

	public function update($request) {
		$dow = new KapitalDow();
		return $dow->update($request);
	}

	public function analysis($request) {
		$dow = new KapitalDow();
		return $dow->analysis($request);
	}

	public function detailResult($request) {
		$dow = new KapitalDow();
		return $dow->detailResult($request);
	}

	public function detailAnalysis($request) {
		$dow = new KapitalDow();
		return $dow->detailAnalysis($request);
	}

	public function costAnalysis($request) {
		$dow = new KapitalDow();
		return $dow->costAnalysis($request);
	}

	public function taxrate($request) {
		$dow = new KapitalDow();
		return $dow->taxrate($request);
	}

	public function indexReport($request) {
		$dow = new KapitalDow();
		return $dow->indexReport($request);
	}

	public function generateReport($request) {
		$dow = new KapitalDow();
		return $dow->generateReport($request);
	}

	public function showReport($request) {
		$dow = new KapitalDow();
		return $this->renderHTML('export/index.twig', $dow->showReport($request));
	}

	public function contentReport($request) {
		$dow = new KapitalDow();
		return $dow->contentReport($request);
	}

	public function graphReport($request) {
		$dow = new KapitalDow();
		return $dow->graphReport($request);
	}

	public function listReport($request) {
		$dow = new KapitalDow();
		return $dow->listReport($request);
	}

	public function projects($request) {
		$dow = new KapitalDow();
		return $dow->projects($request);
	}

}

?>