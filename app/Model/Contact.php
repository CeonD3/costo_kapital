<?php

namespace App\Model;

use App\Utilitarian\{Crypt, FG, MailerFunction, View};
use App\Model\{User};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;

class Contact extends Model
{

    protected $fillable = ['templates'];

    public function sendContact($request)
    {
        $rsp = FG::responseDefault();
        try {
            extract($request->getParsedBody());
            if (!$names) {
                throw new \Exception('The names is required');
            }
            if (!$email) {
                throw new \Exception('The email is required');
            }
            if (!$description) {
                throw new \Exception('The description is required');
            }

            $contact = new Contact();
            $contact->names = $names;
            $contact->email = $email;
            $contact->description = $description;
            $contact->save();

            $users = User::where('deleted_at')->where('perfil', '1')->where('contact_email', '1')->get();
            if (count($users) > 0) {
                $mailer = new MailerFunction();
                $body = View::render('mail/contact.twig', compact('names', 'email', 'description'));
                $params = array('subject' => 'Notificación de nuevo contacto.', 'body' => "$body", 'recipients' => array());
                $recipients = array();
                foreach ($users as $k => $o) {
                    array_push($recipients, array('email' => $o->email, 'name' => ($o->lastname || $o->name ? ($o->name . ' ' . $o->lastname) : $o->email)));
                }
                $params['recipients'] = $recipients;
                $result = $mailer->sendEmail($params);
            }

            $rsp['success'] = true;
            $rsp['message'] = 'Se envío correctamente';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function lists($request)
    {
        $rsp = FG::responseDefault();
        try {
            $contacts = Contact::where('deleted_at')->orderBy('id', 'desc')->get()->toArray();
            $rsp['success'] = true;
            $rsp['data'] = compact('contacts');
            $rsp['message'] = 'List';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }
}
