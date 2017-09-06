<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NotifyStory extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */

    public $user;
    public $story;
    public $subject_id;

    public function __construct($story, $url  = false, $type, $subject_id, $useMail = false)
    {
        $this->subject_id   = $subject_id;
        $this->type         = $type;
        $this->story        = $story;
        $this->useMail      = $useMail;
        $this->pathInstance = $url;
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
            return ['mail'];
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
        return [
        ];
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
            case 'create':
            {
                return [
                    'subject_id'=> $this->subject_id,
                    'predicate' => 'USER_CREATE_STORY',
                    'complement'=> $this->story->notifyInfo(),
                ];
            }
                break;

            default: break;
        }
    }


}
