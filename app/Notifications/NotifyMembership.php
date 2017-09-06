<?php

namespace App\Notifications;

use App\Mail\emailMembership;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NotifyMembership extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public $user;
    public $membership;
    public $useMail=false;
    public $typeMail=['request','activate'];

    public function __construct($user,$membership,$type)
    {
        $this->user= $user;
        if(in_array($type,$this->typeMail))
            $this->useMail= true;
        $this->membership= $membership;
        $this->type= $type;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        if($this->useMail)
            return ['mail',VowChannel::class];
        else
            return [VowChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {

        switch($this->type)
        {
            case 'request':
            {
                return (new emailMembership($this->user,$this->membership,$this->type))->to('dev@cappellidesign.com', 'admin');
            }
                break;

            case 'activate':
            {
                return (new emailMembership($this->user,$this->membership,$this->type))->to($this->user->email, $this->user->name.''.$this->user->surname);
            }
                break;

            default: break;
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [


        ];
    }

    public function toDatabase($notifiable)
    {
        switch($this->type)
        {
            case 'request':
            {
                return [
                    'subject_id'=> $this->user->_id,
                    'predicate' => 'USER_REQUEST_MEMBERSHIP',
                    'complement'=> $this->membership->info()
                ];
            }
                break;

            case 'activate':
            {
                return [
                    'subject_id'=> $this->user->_id,
                    'predicate' => 'ADMIN_ACTIVATE_MEMBERSHIP',
                    'complement'=> $this->membership->info()
                ];
            }
                break;

            default: break;
        }
    }
}
