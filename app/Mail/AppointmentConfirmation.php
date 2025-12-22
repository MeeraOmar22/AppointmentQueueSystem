<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public Appointment $appointment, public string $patientName)
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Dental Appointment Confirmation',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $trackingUrl = url('/track/' . $this->appointment->visit_code);
        $checkinUrl = url('/checkin?code=' . $this->appointment->visit_code);
        
        return new Content(
            view: 'emails.appointment-confirmation',
            with: [
                'name' => $this->patientName,
                'appointment' => $this->appointment,
                'trackingUrl' => $trackingUrl,
                'checkinUrl' => $checkinUrl,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
