<?php 

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transfer extends Model {

    use SoftDeletes;

	protected $table = 'transfers';

    const TYPE_BANK    = 1;
    const TYPE_MOBILE  = 2;
}
