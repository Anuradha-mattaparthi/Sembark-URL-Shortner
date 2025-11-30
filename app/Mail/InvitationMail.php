<?php

namespace App\Mail;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $invitation;

    public function __construct(Invitation $invitation)
    {
        $this->invitation = $invitation;
    }

    public function build()
    {
        $url = route('invitations.accept', $this->invitation->token);

        return $this->subject('Invitation to Join Sembark')
                    ->markdown('emails.invitations.invite')
                    ->with([
                        'inv' => $this->invitation,
                        'url' => $url
                    ]);
    }
}
