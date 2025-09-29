<?php
namespace App\Persistence;
/**
 * 
 */
class Utils
{
    public function fecha_tarjeta($fecha){
        $patron = "/^(([1-9]{1})|([0-0]{1}[1-9]{1})|([1-1]{1}[0-2]{1}))([-]|[\/])([0-9]{2})$/";

        if(preg_match($patron, $fecha) === 1){
            return true;
        }else{
            return false;
        }
    }

    public function nombre_apellido($string){
        $patron = "/^[a-z ,.'-]+$/i";

        if(preg_match($patron, $string) === 1){
            return true;
        }else{
            return false;
        }
    }

    public function format_DDMMYYYY($string){
        $patron = "/^(([1-9]{1})|([0]{1}[1-9]{1})|([1-3]{1}[0-1]{1})|([1-2]{1}[0-9]{1}))([-]|[\/])(([1-9]{1})|([0-0]{1}[1-9]{1})|([1-1]{1}[0-2]{1}))([-]|[\/])([0-9]{4})$/";

        if(preg_match($patron, $string) === 1){
            return true;
        }else{
            return false;
        }
    }

    public function mayor_edad($string){
        $patron = "/^(([1-9]{1})|([0]{1}[1-9]{1})|([1-3]{1}[0-1]{1})|([1-2]{1}[0-9]{1}))([-]|[\/])(([1-9]{1})|([0-0]{1}[1-9]{1})|([1-1]{1}[0-2]{1}))([-]|[\/])([0-9]{4})$/";

        if(preg_match($patron, $string) === 1){
            $fecha_array = explode("/",$string); 
            $fecha = $fecha_array[2]."/".$fecha_array[1]."/".$fecha_array[0];
            $fecha_nacimiento = new DateTime($fecha);
            $hoy = new DateTime();
            $edad = $hoy->diff($fecha_nacimiento);
            if ($edad->y < 18) {
                return false;
            }
            return true;
        }else{
            return false;
        }
    }

    public function get_extension($str) 
	{
		return end(explode(".", $str));
    }
    
    function generate_string_alfanumerico($strength = 4) {
        $input = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $input_length = strlen($input);
        $random_string = '';
        for($i = 0; $i < $strength; $i++) {
            $random_character = $input[mt_rand(0, $input_length - 1)];
            $random_string .= $random_character;
        }
     
        return $random_string;
    }

    function validate_corrreo($email_a) {
        if (filter_var($email_a, FILTER_VALIDATE_EMAIL)) {
            return true;
        }else{
            return false;
        }
    }
}

/*patron para DDMMYYY: */ 