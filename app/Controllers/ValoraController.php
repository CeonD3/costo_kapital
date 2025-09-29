<?php 
namespace App\Controllers;

use App\Dows\ValoraDow;

class ValoraController extends BaseController {

    public function form($request) {
		$dow = new ValoraDow();
		return $dow->form($request);
	}

	public function store($request) {
		$dow = new ValoraDow();
		return $dow->store($request);
	}

	public function bvl($request) {
		$dow = new ValoraDow();
		return $dow->bvl($request);
	}

	public function upload($request) {
		$dow = new ValoraDow();
		return $dow->upload($request);
	}

	public function update($request) {
		$dow = new ValoraDow();
		return $dow->update($request);
	}

	public function balance($request) {
		$dow = new ValoraDow();
		return $dow->balance($request);
	}

	public function detailResult($request) {
		$dow = new ValoraDow();
		return $dow->detailResult($request);
	}

	public function analysis($request) {
		$dow = new ValoraDow();
		return $dow->analysis($request);
	}

	public function detailAnalysis($request) {
		$dow = new ValoraDow();
		return $dow->detailAnalysis($request);
	}
	
	public function costAnalysis($request) {
		$dow = new ValoraDow();
		return $dow->costAnalysis($request);
	}
	
	public function projects($request) {
		$dow = new ValoraDow();
		return $dow->projects($request);
	}

	public function indexReport($request) {
		$dow = new ValoraDow();
		return $dow->indexReport($request);
	}

	public function generateReport($request) {
		$dow = new ValoraDow();
		return $dow->generateReport($request);
	}

	public function showReport($request) {
		$dow = new ValoraDow();
		return $this->renderHTML('export/indexValora.twig', $dow->showReport($request));
	}

	public function contentReport($request) {
		$dow = new ValoraDow();
		return $dow->contentReport($request);
	}

	public function graphReport($request) {
		$dow = new ValoraDow();
		return $dow->graphReport($request);
	}

	public function listReport($request) {
		$dow = new ValoraDow();
		return $dow->listReport($request);
	}
}

?>