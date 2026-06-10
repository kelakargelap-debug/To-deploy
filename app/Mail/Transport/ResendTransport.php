<?php

namespace App\Mail\Transport;

use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Email;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ResendTransport extends AbstractTransport
{
    protected string $apiKey;

    public function __construct(string $apiKey)
    {
        parent::__construct();
        $this->apiKey = $apiKey;
    }

    protected function doSend(SentMessage $message): void
    {
        $email = $message->getOriginalMessage();

        if (!$email instanceof Email) {
            throw new \Exception('Resend transport only supports Symfony\Component\Mime\Email instances.');
        }

        $to = collect($email->getTo())->map(fn($addr) => $addr->getAddress())->toArray();
        $fromAddress = collect($email->getFrom())->first();
        $from = $fromAddress ? ($fromAddress->getName() ? "{$fromAddress->getName()} <{$fromAddress->getAddress()}>" : $fromAddress->getAddress()) : config('mail.from.address');

        // Resend API payload
        $payload = [
            'from' => $from,
            'to' => count($to) === 1 ? $to[0] : $to,
            'subject' => $email->getSubject(),
            'html' => $email->getHtmlBody() ?? $email->getTextBody(),
        ];

        $response = Http::withToken($this->apiKey)
            ->acceptJson()
            ->post('https://api.resend.com/emails', $payload);

        if ($response->failed()) {
            $error = $response->json('message') ?? $response->body();
            Log::error('Resend email delivery failed: ' . $error);
            throw new \Exception('Resend email delivery failed: ' . $error);
        }
    }

    public function __toString(): string
    {
        return 'resend';
    }
}
