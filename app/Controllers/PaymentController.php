<?php 
namespace App\Controllers;

use App\Utilitarian\{FG};
use App\Model\{Payment};

class PaymentController extends BaseController
{
    public function index($request) {
		$payment = new Payment();
		$rsp = $payment->index($request);
		return $this->renderHTML('admin/payment/index.twig', compact('rsp'));
	}

    public function save($request) {
		$payment = new Payment();
		return $payment->save($request);
	}

    public function transfer($request) {
		$payment = new Payment();
		return $payment->transfer($request);
	}
    
    public function bitacoras($request) {
		$payment = new Payment();
		$rsp = $payment->bitacoras($request);
		return $this->renderHTML('admin/payment/bitacora_venta.twig', compact('rsp'));
	}

    public function checkout($request) {
		$payment = new Payment();
		return $payment->checkout($request);
	}
}

?>