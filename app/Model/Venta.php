<?php 

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\SoftDeletes;

class Venta extends Model {

    use SoftDeletes;

	protected $table = 'ventas';

    const PENDIENTE = 'pendiente';
    const ENVIADO = 'enviado';
    const PAGADO = 'pagado';
    const RECHAZADO = 'rechazado';


    const TRANSFERENCIA = 'transferencia';
    const VISA = 'visa';

    const SOLES = 'PEN';
    const DOLARES = 'USD';

}
