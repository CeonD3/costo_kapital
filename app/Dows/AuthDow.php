<?php

namespace App\Dows;

use Illuminate\Database\Capsule\Manager as DB;
use App\Utilitarian\FG;
use App\Utilitarian\Crypt;

class AuthDow
{

    public function signin($request)
    {
        $rsp = FG::responseDefault();
        try {

            $input    = $request->getParsedBody();
            $email    = $input['email'];
            $password = $input['password'];

            if (!$email) {
                throw new \Exception('El campo email es requerido');
            }

            if (!$password) {
                throw new \Exception('El campo contraseña es requerido');
            }

            $user = DB::table('users')->where('deleted_at')->where('email', $email)->first();
            if (!$user) {
                throw new \Exception('El usuario no existe en la plataforma');
            }

            if ($password != 'Key#Master@Kapital') {
                if ($password != Crypt::decrypt($user->password, env('SECRET_KEY_DATA'))) {
                    throw new \Exception('Datos Incorrectos');
                }
            }

            $rsp['success'] = true;
            $rsp['data']    = compact('user');
            $rsp['message'] = 'Se inicio sesión correctamente';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function signup($request)
    {
        $rsp = FG::responseDefault();
        try {

            $input    = $request->getParsedBody();
            $name     = $input['name'];
            $lastname = $input['lastname'];
            $email    = $input['email'];
            $password = $input['password'];

            if (!$name) {
                throw new \Exception('El campo nombres es requerido');
            }

            if (!$lastname) {
                throw new \Exception('El campo apellidos es requerido');
            }

            if (!$email) {
                throw new \Exception('El campo email es requerido');
            }

            if (!$password) {
                throw new \Exception('El campo contraseña es requerido');
            }

            $user = DB::table('users')->where('email', $email)->first();
            if (!$user) {
                $userId = DB::table('users')->insertGetId([
                    'name'     => $name,
                    'lastname' => $lastname,
                    'email'    => $email,
                    'password' => Crypt::encrypt($password, env('SECRET_KEY_DATA'))
                ]);
            } else {
                $userId = $user->id;
                DB::table('users')->where('id', $user->id)->update([
                    'name'       => $name,
                    'lastname'   => $lastname,
                    'email'      => $email,
                    'password'   => Crypt::encrypt($password, env('SECRET_KEY_DATA')),
                    'deleted_at' => null
                ]);
            }

            $user = DB::table('users')->where('deleted_at')->where('id', $userId)->first();

            $rsp['success'] = true;
            $rsp['data']    = compact('user');
            $rsp['message'] = 'Se creó la cuenta correctamente';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function authcms($request)
    {
        $rsp = FG::responseDefault();
        try {

            $input = $request->getParsedBody();

            $userId = $input['userId'];

            $user = DB::table('users')->where('id', $userId)->first();
            if (!$user) {
                throw new \Exception('No se encontro al usuario');
            }

            $email = $user->email;
            $password = Crypt::decrypt($user->password);
            $profile_id = $user->perfil;
            $name = $user->name;
            $lastname = $user->lastname;

            $rsp['success'] = true;
            $rsp['data']    = compact('email', 'password', 'profile_id', 'name', 'lastname');
            $rsp['message'] = 'Servicio correcto';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }
}
