<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;

    public function __construct(string $otp)
    {
        $this->otp = $otp;
    }

    public function build()
    {
        return $this->subject('Your LMS Login Verification OTP')
                    ->html("
                        <h2>LMS Email Verification</h2>
                        <p>Your OTP for login verification is:</p>
                        <h1>{$this->otp}</h1>
                        <p>This OTP is valid for 5 minutes.</p>
                        <p>Please do not share this OTP with anyone.</p>
                    ");
    }
}
