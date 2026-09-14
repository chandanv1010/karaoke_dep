<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMail extends Mailable
{
    use Queueable, SerializesModels;


    public $data;
    /**
     * Create a new message instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        // Truoc day tieu de mail ghi cung 'Nội Thất An Hưng' - ten cong ty cua
        // ma nguon goc, nen khach lien he qua karaokedep.com.vn nhan duoc mail
        // mang ten mot cong ty noi that. Lay theo cau hinh trong admin.
        return new Envelope(
            subject: 'Thông báo từ website ' . $this->companyName(),
        );
    }

    /**
     * Ten cong ty lay tu Cau hinh > He thong, khong ghi cung trong ma nguon.
     *
     * Mailable co the chay trong queue (khong co request/view context) nen dung
     * helper doc thang tu bang systems thay vi bien duoc share cho view.
     */
    private function companyName(): string
    {
        return company_name();
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.contact',
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
