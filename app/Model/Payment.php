<?php

namespace App\Model;

use App\Utilitarian\{FG, MailerFunction, View, Crypt};
use Illuminate\Database\Capsule\Manager as Capsule;
use App\Model\{Transfer, BitacoraVenta, Venta, Desgin, Report};

class Payment
{

    public function index($request)
    {
        $rsp = FG::responseDefault();
        try {
            $mobiles = array();
            $banks = array();
            $transfers = Transfer::get();
            foreach ($transfers as $k => $o) {
                if (!$o->deleted_at) {
                    if ($o->type_id == Transfer::TYPE_BANK) {
                        array_push($banks, $o);
                    } else if ($o->type_id == Transfer::TYPE_MOBILE) {
                        array_push($mobiles, $o);
                    }
                }
            }

            $rsp['success'] = true;
            $rsp['data'] = compact('banks', 'mobiles');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function save($request)
    {
        $rsp = FG::responseDefault();
        try {

            $param = $request->getParsedBody();
            $panel = $param['panel'];

            if (!$panel) {
                throw new \Exception('El parametro panel es obligatorio');
            }
            $uploads = $request->getUploadedFiles();

            $baseurl = "upload/payment/";
            $basepath = __DIR__ . "/../../public/";
            $fullpath = $basepath . $baseurl;
            if (!is_dir($fullpath)) {
                mkdir($fullpath, 0777, true);
            }

            switch ($panel) {
                case 'transfer-bank':
                    $id = $param['id'];
                    $account = $param['account'];
                    $number = $param['number'];
                    $cci = $param['cci'];
                    $upload = $request->getUploadedFiles();
                    $bank_ids = array();
                    foreach ($account as $k => $o) {
                        $transfer = @$id[$k] > 0 ? Transfer::where('id', $id[$k])->where('deleted_at')->first() : new Transfer();
                        $transfer->account = $account[$k];
                        $transfer->number = $number[$k];
                        $transfer->cci = $cci[$k];
                        $transfer->type_id = Transfer::TYPE_BANK;
                        if (count($upload['image'][$k]) > 0) {
                            $file = $upload['image'][$k];
                            if ($file->getError() == UPLOAD_ERR_OK) {
                                $uniqid = uniqid(time());
                                $fileName = strtolower($file->getClientFilename());
                                $path = $baseurl . "$uniqid-$fileName";
                                $file->moveTo($path);
                                unlink($basepath . $transfer->image);
                                $transfer->image = "/$path";
                            }
                        }
                        $transfer->save();
                        array_push($bank_ids, $transfer->id);
                    }
                    $transfers = Transfer::where('type_id', Transfer::TYPE_BANK)->where('deleted_at')->get();
                    foreach ($transfers as $k => $o) {
                        if (!in_array($o->id, $bank_ids)) {
                            $o->deleted_at = FG::getDateHour();
                            $o->save();
                            unlink($basepath . $o->image);
                        }
                    }
                    break;
                case 'transfer-mobile':
                    $id = $param['id'];
                    $account = $param['account'];
                    $number = $param['number'];
                    $upload = $request->getUploadedFiles();

                    $mobile_ids = array();
                    foreach ($account as $k => $o) {
                        $transfer = $id[$k] > 0 ? Transfer::where('id', $id[$k])->where('deleted_at')->first() : new Transfer();
                        $transfer->account = $account[$k];
                        $transfer->number = $number[$k];
                        $transfer->type_id = Transfer::TYPE_MOBILE;
                        if (count($upload['image'][$k]) > 0) {
                            $file = $upload['image'][$k];
                            if ($file->getError() == UPLOAD_ERR_OK) {
                                $uniqid = uniqid(time());
                                $fileName = strtolower($file->getClientFilename());
                                $path = $baseurl . "$uniqid-$fileName";
                                $file->moveTo($path);
                                unlink($basepath . $transfer->image);
                                $transfer->image = "/$path";
                            }
                        }
                        $transfer->save();
                        array_push($mobile_ids, $transfer->id);
                    }
                    $transfers = Transfer::where('type_id', Transfer::TYPE_MOBILE)->where('deleted_at')->get();
                    foreach ($transfers as $k => $o) {
                        if (!in_array($o->id, $mobile_ids)) {
                            $o->deleted_at = FG::getDateHour();
                            $o->save();
                            unlink($basepath . $o->image);
                        }
                    }
                    break;
            }

            $rsp['success'] = true;
            $rsp['message'] = 'Se guardo correctamente';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function transfer($request)
    {
        $rsp = FG::responseDefault();
        try {

            $param = $request->getParsedBody();

            $entity = $param['entity'];
            $project = $param['project'];

            $report = Report::where('code', $param['report'])->first();
            if (!$report) {
                throw new \Exception('El reporte no existe');
            }

            if ($report->type_id == 1) {
                if (!$entity) {
                    throw new \Exception('El nombre de la empresa es obligatorio');
                }
                if (!$project) {
                    throw new \Exception('El nombre del proyecto es obligatorio');
                }
            }

            $document = Design::where('id', $param['document'])->first();
            if (!$document) {
                throw new \Exception('El documento no existe');
            }

            $user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
            if ($user) {
                $email = $user->email;
            } else {
                if (!$param['email']) {
                    throw new \Exception('El campo email es obligatorio');
                }
                if (!$param['password']) {
                    throw new \Exception('El campo contraseña es obligatorio');
                }
                $email = $param['email'];
                $password = $param['password'];
            }

            $verify = Capsule::table('bitacoras_ventas')
                ->join('users', 'users.id', '=', 'bitacoras_ventas.user_id')
                ->select('bitacoras_ventas.report_id', 'bitacoras_ventas.document_id', 'users.email', 'bitacoras_ventas.status')
                ->where('bitacoras_ventas.report_id', '=', $report->id)
                ->where('bitacoras_ventas.document_id', '=', $document->id)
                ->where('users.email', '=', $email)
                ->orderby('bitacoras_ventas.id', 'desc')
                ->first();
            if ($verify) {
                if ($verify->status == Venta::PAGADO) {
                    throw new \Exception('Estimado, ya tiene un documento comprado con este correo ' . $email);
                } else if ($verify->status == Venta::ENVIADO) {
                    throw new \Exception('Estimado, ya tiene un documento en proceso de revisión de su transacción con este correo ' . $email);
                }
            }

            $user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
            if (!$user) {
                $user = User::where('deleted_at')->where('email', $email)->first();
                if ($user) {
                    if ($password != Crypt::decrypt($user->password, env('SECRET_KEY_DATA'))) {
                        throw new \Exception('Datos Incorrectos del usuario');
                    }
                } else {
                    $user = new User();
                    $user->email = $email;
                    $user->folder = uniqid(time());
                    $user->password = Crypt::encrypt($password, env('SECRET_KEY_DATA'));
                    $user->save();

                    $path = FG::fullFolderPath($user->folder);
                    if (!is_dir($path)) {
                        mkdir($path, 0777, true);
                    }
                }
                $user->registerRecord($report->code, $user);
                $_SESSION['user'] = json_decode(json_encode($user->toArray()));
            }

            $report->entity = $entity;
            $report->name = $project;
            $report->save();

            $bitacoraVenta = new BitacoraVenta();
            $bitacoraVenta->report_id = $report->id;
            $bitacoraVenta->document_id = $document->id;
            $bitacoraVenta->email = $email;
            $bitacoraVenta->user_id = $user->id;
            $bitacoraVenta->payment = VENTA::TRANSFERENCIA;
            $bitacoraVenta->code = strtoupper(FG::randString(10) . uniqid(time()));
            $bitacoraVenta->price = $document->price;
            $bitacoraVenta->currency = $document->currency;
            $bitacoraVenta->status = VENTA::ENVIADO;
            $bitacoraVenta->ip = FG::getRealIP();
            $bitacoraVenta->comment = $param['comment'];

            $baseurl = "upload/pagos/";
            $basepath = __DIR__ . "/../../public/";
            $fullpath = $basepath . $baseurl;
            if (!is_dir($fullpath)) {
                mkdir($fullpath, 0777, true);
            }

            $upload = $request->getUploadedFiles();
            $file = $upload['file'];
            if ($file->getError() == UPLOAD_ERR_OK) {
                $uniqid = uniqid(time());
                $fileName = strtolower($file->getClientFilename());
                $path = $baseurl . "$uniqid-$fileName";
                $file->moveTo($path);
                $bitacoraVenta->file = "/$path";
            } else {
                throw new \Exception('El archivo del comprobante no existe');
            }

            $bitacoraVenta->save();

            $rsp['success'] = true;
            $rsp['message'] = 'Se registro correctamente';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function bitacoras($request)
    {
        $rsp = FG::responseDefault();
        try {
            $bitacoras_ventas = BitacoraVenta::orderby('id', 'desc')->get();

            $rsp['success'] = true;
            $rsp['data'] = compact('bitacoras_ventas');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function checkout($request)
    {
        $rsp = FG::responseDefault();
        try {
            $param = $request->getParsedBody();
            if (!$param['id']) {
                throw new \Exception('El campo id es obligatorio');
            }
            $bitacoraVenta = BitacoraVenta::where('id', $param['id'])->first();
            $bitacoraVenta->status = $param['status'];
            $bitacoraVenta->save();
            if (@$param['send']) {
                $mailer = new MailerFunction();
                $comment = $param['comment'];
                $link = @$param['link'];
                $email = $bitacoraVenta->email;
                $code = $bitacoraVenta->code;
                $body = View::render('mail/document.twig', compact('email', 'comment', 'code', 'link'));
                $params = array('subject' => 'Notificación de Transacción.', 'body' => "$body", 'recipients' => array());
                $recipients = array('email' => $email, 'name' => null);
                $params['recipients'][] = $recipients;
                $result = $mailer->sendEmail($params);
            }

            $rsp['success'] = true;
            $rsp['data'] = compact('result');
            $rsp['message'] = 'Se guardo correctamente';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }
}
