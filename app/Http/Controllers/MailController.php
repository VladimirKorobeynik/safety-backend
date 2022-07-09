<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ActivationSmartDevice;

class MailController extends Controller
{
    public static function sendEmail($email, $userName, $smartDeviceNumber, $sensorsActivationKey)
    {
       Mail::to($email)->send(new ActivationSmartDevice($userName, $smartDeviceNumber, $sensorsActivationKey));
    }
}
