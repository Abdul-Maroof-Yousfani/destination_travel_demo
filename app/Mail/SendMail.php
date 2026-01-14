<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class SendMail extends Mailable
{
    use Queueable, SerializesModels;

    public $username;
    public $bookingRefID;
    public $ticketMsg;

    /**
     * Create a new message instance.
     */
    public function __construct($username, $bookingRefID, $ticketMsg)
    {
        $this->username = $username;
        $this->bookingRefID = $bookingRefID;
        $this->ticketMsg = $ticketMsg;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Flight Booking Confirmation',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.sendBookingId',
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
