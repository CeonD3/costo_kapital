<?php 

namespace App\Dows;

use Illuminate\Database\Capsule\Manager as DB;
use App\Utilitarian\FG;
use App\Middlewares\Application;
use App\Services\OnedriveService;

class OnedriveDow {
    
    public function signin($request) {
		$rsp = FG::responseDefault();
        try {

            $query  = $request->getQueryParams();
            $code   = $query['code'];
            $state  = $query['state'];
            $update = [];

            if (!isset($_COOKIE['AUTH_ONEDRIVE_MICROSOFT'])) {            
                header('Location: /'); exit;
            }

            $cookie = json_decode($_COOKIE['AUTH_ONEDRIVE_MICROSOFT']);

            if ($cookie->state != $state) {            
                header('Location: /'); exit;
            }

            if (!$code) {            
                header('Location: /'); exit;
            }

            $company = DB::table('empresas')->where('deleted_at')->where('id', $cookie->companyId)->first();
            if (!$company) {
                header('Location: /'); exit;
            }

            $onedrive  = new OnedriveService();
            $azure      = $onedrive->getAzure();
            $token      = $azure->getAccessToken('authorization_code', [
                'scope' => $azure->scope,
                'code'  => $code
            ]);
            
            $newtoken = json_encode($token);
            $update['token_onedrive'] = $newtoken;

            $onedrive->setToken($newtoken);
            $me = $onedrive->me();

            if (isset($me['userPrincipalName'])) {
                $update['email_onedrive'] = $me['userPrincipalName'];
            }

            DB::table('empresas')->where('id', $company->id)->update($update);

            if ($cookie->redirect) {
                header('Location: ' . $cookie->redirect); exit;
            }

        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        header('Location: /'); exit;
	}

    public function authUrl($request) {
		$rsp = FG::responseDefault();
        try {

            $input     = $request->getParsedBody();
            $redirect  = $input['redirect'];

            $company = DB::table('empresas AS US')->where('US.deleted_at')->first();
            if (!$company) {
                throw new \Exception('No se encontró la empresa');
            }

            $companyId = $company->id;

            $onedrive  = new OnedriveService();
            $azure     = $onedrive->getAzure();
            $url       = $azure->getAuthorizationUrl();
            $state     = $azure->getState();

            if (isset($_COOKIE['AUTH_ONEDRIVE_MICROSOFT'])) {
                unset($_COOKIE['AUTH_ONEDRIVE_MICROSOFT']);
            }

            setcookie('AUTH_ONEDRIVE_MICROSOFT', json_encode([
                'state'     => $state, 
                'companyId' => $companyId,
                'redirect'  => $redirect
            ]), time () + 60 * 5, "/"); // to 5 minuts 

            header('Location: ' . $url); exit;
            
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
	}

    public function signout($request) {
		$rsp = FG::responseDefault();
        try {

            $input = $request->getParsedBody();
            $domain    = $input['domain'];
            $logoutUrl = "";

            $company = DB::table('empresas AS EMP')->where('EMP.deleted_at')->first();
            if (!$company) {
                throw new \Exception('No se encontró la empresa');
            }

            if ($company->token_onedrive) {

                /*$microsoft  = new OnedriveService();
                $token      = $microsoft->getAccessToken($user->token_microsoft, $user->id);
                $result = $graph->createRequest("GET", "/me")->execute();
                $account = $result->getBody();
                
                $username = $account["userPrincipalName"];*/
                    

                DB::table("empresas")->where('id', $company->id)->update(['token_onedrive' => NULL]);
            }

            /*$microsoft  = new OnedriveService();
            $token      = $microsoft->getAccessToken($user->token_microsoft, $user->id);

            $graph = new Graph();
            $graph->setAccessToken($token['access_token']);
            $result = $graph->createRequest("POST", "/users/joseant_1294@hotmail.com")->execute();
            $account = $result->getBody();
            $status = true;*/
            
            $rsp['success'] = true;
            $rsp['message'] = 'Se desvinculó correctamente';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
	}
    
    public function overview($request) {
		$rsp = FG::responseDefault();
        try {

            $input  = $request->getParsedBody();
            $account = []; $status = false;

            $company = DB::table('empresas AS EMP')->where('EMP.deleted_at')->first();
            if (!$company) {
                throw new \Exception('No se encontró la empresa');
            }

            if ($company->token_onedrive) {
                $onedrive = new OnedriveService();
                $onedrive = $this->handleToken($company->token_onedrive, $onedrive);
                $account = $onedrive->me();
                $status = true;
            }
            
            $rsp['success'] = true;
            $rsp['data']    = compact('account', 'status');
            $rsp['message'] = 'Datos de la cuenta microsoft';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
	}

    public function handleToken($token, $onedriveService) {
        $handle = $onedriveService->handleToken($token);
        if ($handle['update']) {
            $token = $handle['token'];
            DB::table('empresas')->where('deleted_at')->update(['token_onedrive' => $token]);
        }
        return $onedriveService;
    }
}