<?php

namespace App\Utilitarian;

use Carbon\Carbon;

class FG
{

    public static function getFormatDateTime($fecha, $format = 'Y-m-d H:i:s')
    {
        $date = Carbon::createFromFormat('Y-m-d H:i:s', $fecha);
        $date = $date->format($format);
        return $date;
    }
    public static function getDateFormat($fecha, $format = 'Y-m-d H:i:s')
    {
        return Carbon::parse($fecha)->format($format);
    }
    public static function addZeroDecimal($number)
    {
        if (10 > $number) {
            $number = "0" . $number;
        }
        return $number;
    }

    public static function addZero($number)
    {
        if ($number < 10) {
            $number = '000' . $number;
        } else if ($number < 100) {
            $number = '00' . $number;
        } else if ($number < 1000) {
            $number = '0' . $number;
        }
        return $number;
    }

    public static function randString($lenght = 10)
    {
        $string = '';
        $characters = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $max = strlen($characters) - 1;
        for ($i = 0; $i < $lenght; $i++) {
            $string .= $characters[mt_rand(0, $max)];
        }
        return $string;
    }

    public static function getPriceWithIGV($monto = false)
    {
        if (!$monto) return 0;
        $base = $monto / 1.18;
        $monto_igv = $monto - $base;
        $total = $monto + $monto_igv;
        return number_format($total, 2);;
    }

    public static function getDifferenceDayOfDateString($date_end, $date_now = false)
    {
        date_default_timezone_set('America/Lima');
        $fecha = date("Y-m-d");
        $date_now = $date_now ? $date_now  : $fecha;
        $now_time = strtotime($date_now);
        $end_time = strtotime($date_end);
        if ($now_time < $end_time) {
            $result = 'A tiempo';
        } else {
            $now = Carbon::parse($date_now);
            $end = Carbon::parse($date_end);
            $diff = $end->diffInDays($now);
            $result = $diff . ' días de retraso';
        }
        return $result;
    }

    public static function responseDefault()
    {
        return ['success' => false, 'data' => null, 'message' => 'Lo sentimos, el servicio no está disponible. Intentelo más tartde.'];
    }

    public static function getDateHour($format = 'Y-m-d H:i:s')
    {
        $date = Carbon::now();
        $date->setTimezone('America/Lima');
        $fecha = $date->format($format);
        return $fecha;
    }

    public static function getFormatDateString($fecha)
    {
        if (preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $fecha, $partes)) {
            $mes = 'de ' . FG::getDateMonth($partes[2]) . ' del ';
            return $partes[3] . " " . $mes . $partes[1];
        } else {
            // Si hubo problemas en la validación, devolvemos false
            return false;
        }
    }

    public static function fullPathUploadTemplateUser($file)
    {
        return __DIR__ . '/../../public/template/users/' . $file;
    }

    public static function fullPathUserTemplate($file)
    {
        return __DIR__ . '/../../public/template/users/' . $file;
    }

    public static function fullFolderPathUserTemplate()
    {
        return __DIR__ . '/../../public/template/users';
    }

    public static function fullFolderPath($folder)
    {
        return __DIR__ . '/../../public/template/users/' . $folder;
    }

    public static function fullPathUser($folder, $file)
    {
        return __DIR__ . '/../../public/template/users/' . $folder . '/' . $file;
    }

    public static function fullFolderGuestPath()
    {
        return __DIR__ . '/../../public/template/guests';
    }

    public static function fullPathGuest($file)
    {
        return __DIR__ . '/../../public/template/guests/' . $file;
    }

    public static function fullPathMaster()
    {
        return __DIR__ . '/../../public/template/master/master.xlsx';
    }

    public static function getPathMaster($filename)
    {
        return __DIR__ . '/../../public/template/master/' . $filename;
    }

    public static function formatterx100val($value)
    {
        return number_format(($value * 100), 2, '.', '');
    }

    public static function formatterx100p($value)
    {
        $number = number_format(($value * 100), 2, '.', '');
        return $number . '%';
    }

    public static function formatterx100($value)
    {
        $number = $value * 100;
        return $number;
    }
    public static function numberformat($value, $decimal = 2)
    {
        // Si el valor no es numérico (ej. '#DIV/0!', null, texto), devuelve un valor por defecto.
        if (!is_numeric($value)) {
            // Puedes devolver 0, '-', o un cero formateado. Un cero formateado es más seguro.
            return number_format(0, $decimal, '.', ',');
        }
        // Si es numérico, lo formatea normalmente.
        return number_format((float)$value, $decimal, '.', ',');
    }

    public static function getRealIP()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP']))
            return $_SERVER['HTTP_CLIENT_IP'];

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
            return $_SERVER['HTTP_X_FORWARDED_FOR'];

        return $_SERVER['REMOTE_ADDR'];
    }

    public static function getDateMonth($num)
    {
        $meses = array(
            'Error',
            'Enero',
            'Febrero',
            'Marzo',
            'Abril',
            'Mayo',
            'Junio',
            'Julio',
            'Agosto',
            'Septiembre',
            'Octubre',
            'Noviembre',
            'Diciembre'
        );
        $num_limpio = $num >= 1 && $num <= 12 ? intval($num) : 0;
        return $meses[$num_limpio];
    }

    public static function parseDateExcelText($date, $index = 3)
    {
        $mes = substr($date, 0, $index);
        $anio = substr($date, $index, count($date) + 1);
        switch (strtolower($mes)) {
            case 'ene':
                $mes = "01";
                break;
            case 'feb':
                $mes = "02";
                break;
            case 'mar':
                $mes = "03";
                break;
            case 'abr':
                $mes = "04";
                break;
            case 'may':
                $mes = "05";
                break;
            case 'jun':
                $mes = "06";
                break;
            case 'jul':
                $mes = "07";
                break;
            case 'ago':
                $mes = "08";
                break;
            case 'sep':
                $mes = "09";
                break;
            case 'oct':
                $mes = "10";
                break;
            case 'nov':
                $mes = "11";
                break;
            case 'dic':
                $mes = "12";
                break;
            default:
                $mes = -1;
                break;
        }
        return "20" . $anio . "-" . $mes . "-01";
    }

    function trimestre($datetime)
    {
        $mes = date("m", strtotime($datetime)); //Referencias: http://stackoverflow.com/a/3768112/1883256
        $mes = is_null($mes) ? date('m') : $mes;
        $trim = floor(($mes - 1) / 3) + 1;
        return $trim;
    }

    public static function slugify($str)
    {
        $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'Ð', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', '?', '?', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', '?', '?', 'L', 'l', 'N', 'n', 'N', 'n', 'N', 'n', '?', 'O', 'o', 'O', 'o', 'O', 'o', 'Œ', 'œ', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'Š', 'š', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Ÿ', 'Z', 'z', 'Z', 'z', 'Ž', 'ž', '?', 'ƒ', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', '?', '?', '?', '?', '?', '?');
        $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
        return strtolower(preg_replace(array('/[^a-zA-Z0-9 -]/', '/[ -]+/', '/^-|-$/'), array('', '-', ''), str_replace($a, $b, $str)));
    }

    public static function debug($data = "", $debug = false)
    {
        $data = is_object($data) || is_array($data) ? json_encode($data) : $data;
        self::recordErrorLog($data, (($debug) ? $debug : debug_backtrace()));
    }

    public static function recordErrorLog($msg = "", $debug = false)
    {
        try {
            $mydebug = debug_backtrace();
            array_shift($mydebug);
            $debug = ($debug) ? $debug : $mydebug;

            $folder = __DIR__ . "/../../logs/";
            $fullpath = "{$folder}debug.log";
            if (!file_exists($fullpath)) {
                mkdir($folder, 0777); // create folder
                $log = fopen($fullpath, "c");
                fclose($log);
            }
            $debug = $debug[0];
            $date = self::getDateHour();
            $fullmessage = "--- BEGIN " . $date . " ---\r\n";
            $fullmessage .= "FILE: " . $debug["file"] . "\r\n";
            $fullmessage .= "LINE: " . $debug["line"] . "\r\n";
            $fullmessage .= "CLASS: " . $debug["class"] . "\r\n";
            $fullmessage .= "FUNCTION: " . $debug["function"] . "\r\n";
            $fullmessage .= "MESSAGE: " . $msg . "\r\n";
            // $fullmessage .= "BACKTRACE: " . json_encode(debug_backtrace()) . "\r\n";
            $fullmessage .= "--- END " . $date . " ---\r\n\r\n";
            $text = file_get_contents($fullpath);
            $text = $fullmessage . $text;
            file_put_contents($fullpath, $text);
        } catch (Exception $e) {
        }
    }

    public static function obtenerRangoDeLetras($inicio, $fin)
    {
        $letras = [];

        // Convertir la letra de inicio a su valor ASCII
        $codigoInicio = ord($inicio);

        // Recorrer desde el valor ASCII de la letra de inicio hasta el valor final
        for ($i = 0; $i < $fin; $i++) {
            $letras[] = chr($codigoInicio + $i);
        }

        return $letras;
    }
}
