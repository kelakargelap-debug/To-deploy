<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $userName;
    public $purpose;
    public $extraData;

    /**
     * Create a new message instance.
     */
    public function __construct($otp, $userName, $purpose, $extraData = [])
    {
        $this->otp = $otp;
        $this->userName = $userName;
        $this->purpose = $purpose;
        $this->extraData = $extraData;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subjects = [
            'register_verification' => 'Kode Verifikasi Akun Tryout Anda',
            'new_device_login'      => 'Kode OTP Login dari Perangkat Baru',
            'password_reset'        => 'Kode OTP Reset Password',
            'logout_all_devices'    => 'Kode OTP Logout Semua Perangkat',
        ];

        return new Envelope(
            subject: $subjects[$this->purpose] ?? 'Kode Verifikasi OTP Anda',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.otp',
            with: [
                'otp' => $this->otp,
                'userName' => $this->userName,
                'purpose' => $this->purpose,
                'extraData' => $this->extraData,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
