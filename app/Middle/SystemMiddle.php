<?php

namespace App\Middle;

use App\Utilitarian\{Crypt, FG};
use App\Model\{User, Report, Template};

trait SystemMiddle
{
    private function verifyReportFile($request) {
        $code = $request->getAttribute('code');
        if (!$code) {
            $args = $request->getParsedBody();
            $code = $args['code'];
            if (!$code) {
                header('Location: /');
                // throw new \Exception('The code required');
            }
        }
        $user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
        if ($user) {
            $report = Report::where('user_id', $user->id)->where('code', $code)->first();
            if (!$report) {
                header('Location: /');
                // throw new \Exception('The file no found');
            }
            return FG::fullPathUser($user->folder, $report->file);
        } else {
            $report = Report::where('code', $code)->first();
            if (!$report) {
                header('Location: /');
                // throw new \Exception('The file no found');
            }
            if ($report->user_id > 0) {
                header('Location: /');
                // throw new \Exception('The file no found');
            }
            return FG::fullPathGuest($report->file);
        }
    }

}