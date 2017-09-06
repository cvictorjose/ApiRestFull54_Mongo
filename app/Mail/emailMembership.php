<?php

namespace App\Mail;

use App\Membership;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class emailMembership extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public $user;

    public function __construct(User $user,Membership $membership,$type)
    {
        $this->user= $user;
        $this->membership= $membership;
        $this->type= $type;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        switch($this->type)
        {
            case 'request':
            {
                return $this->view('emails.'.$this->user->lang.'.request_membership')->subject(trans('messages.newMembership'))
                    ->with(array('name' => $this->user->name.''.$this->user->surname));

            }
                break;

            case 'activate':
            {
                return $this->view('emails/'.$this->user->lang.'/activate_membership')->subject(trans('messages.activateMembership'))
                    ->with(array('name' => $this->user->name.''.$this->user->surname));
            }
                break;

            default: break;
        }

    }
}
