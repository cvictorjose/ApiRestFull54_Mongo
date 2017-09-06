<?php

namespace App\Notifications;

use App\Mail\emailUser;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NotifyUser extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */

    public $user;

    public function __construct($user,$url,$type, $optional = false, $useMail = true)
    {
        $this->user= $user;
        $this->pathInstance=$url;
        $this->type= $type;
        $this->opt= $optional;
        $this->useMail= $useMail;
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
            case 'welcome':
            {
                return (new emailUser($this->user,$this->pathInstance,$this->type))->to($this->user->email, $this->user->name." ".$this->user->surname);
            }
                break;

            case 'confirmEmail':
            {
                return (new emailUser($this->user,$this->pathInstance,'changeOrConfirm'))->to($this->user->email, $this->user->name);
            }
                break;

            case 'changeEmail':
            {
                return (new emailUser($this->user,$this->pathInstance,'changeOrConfirm'))->to($this->user->email_verified, $this->user->name);
            }
                break;

            case 'resetPsw':
            {
                return (new emailUser($this->user,$this->pathInstance,'resetPsw',$this->opt))->to($this->user->email, $this->user->name);
            }
                break;


            case 'changeWebStore':
            {
                return (new emailUser($this->user,false, $this->type))->to('dev@cappellidesign.com', 'admin');
            }
                break;


            case 'confirmWebStore':
            {
                return (new emailUser($this->user,false, $this->type))->to('dev@cappellidesign.com', 'admin');
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
            case 'followUser':
            {
                return [
                    'subject_id'=> $this->user->_id,
                    'predicate' => 'USER_FOLLOW_ME',
                    'complement'=> $this->user->notifyInfo(),
                ];
            }
                break;


            case 'yourFollowers':
            {
                return [
                    'subject_id'=> $this->opt,
                    'predicate' => 'USER_FOLLOW_USER',
                    'complement'=> $this->user->notifyInfo(),
                ];
            }
                break;


            case 'responseComment':
            {
                return [
                    'subject_id'=> $this->opt,
                    'predicate' => 'USER_COMMENT_STORY',
                    'complement'=> $this->user->notifyInfo(),
                ];
            }
                break;


            case 'likeStory':
            {
                return [
                    'subject_id'=> $this->user->_id,
                    'predicate' => 'USER_LIKE_STORY',
                    'complement'=> $this->opt->notifyInfo(),
                ];
            }
                break;


            case 'likeComment':
            {
                return [
                    'subject_id'=> $this->user->_id,
                    'predicate' => 'USER_LIKE_COMMENT',
                    'complement'=> $this->opt->notifyInfo(),
                ];
            }
                break;



            case 'changeWebStore':
            {
                return [
                    'subject_id'=> $this->user->_id,
                    'predicate' => 'USER_CHANGED_WEBSTORE',
                    'complement'=> $this->user->web_store,
                ];
            }
                break;


            case 'confirmWebStore':
            {
                return [
                    'subject_id'=> 'Admin',
                    'predicate' => 'ADMIN_CONFIRM_WEBSTORE',
                    'complement'=> $this->user->web_store,
                ];
            }
                break;


            default: break;
        }
    }


}
