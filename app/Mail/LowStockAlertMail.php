<?php

namespace App\Mail;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LowStockAlertMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  array<array{name:string, quantity:int, threshold:int}>  $products
     */
    public function __construct(
        public string $companyName,
        public string $recipientName,
        public array  $products
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Alerte Stock] — ' . $this->companyName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.low_stock_alert',
        );
    }
}
