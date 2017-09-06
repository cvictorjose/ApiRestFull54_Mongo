<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class emailUser extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public $user;

    public function __construct(User $user,$instance,$type, $token = false)
    {
        $this->user= $user;
        $this->type= $type;
        $this->pathInstance=$instance;
        $this->token= $token;
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
            case 'welcome':
            {
                return $this->view('emails.'.$this->user->lang.'.welcome')->subject(trans('messages.subject'))
                    ->with(array(
                        'name'      => $this->user->name.' '.$this->user->surname,
                        'actionText'=> trans('messages.confirmEmail'),
                        'actionUrl' => $this->pathInstance.'/user/verify?t='.$this->user->verify_token,
                    ));
            }
                break;

            case 'changeOrConfirm':
            {
                return $this->view('emails.'.$this->user->lang.'.confirm_email')->subject(trans('messages.confirmEmail'))
                    ->with(array(
                        'name'      => $this->user->name.' '.$this->user->surname,
                        'actionText'=> trans('messages.confirmEmail'),
                        'actionUrl' => $this->pathInstance.'/user/verify?t='.$this->user->verify_token,
                    ));

            }
                break;

            case 'resetPsw':
            {

                return $this->view('emails.'.$this->user->lang.'.password_reset')->subject(trans('messages.resetPassword'))
                    ->with(array(
                        'name'      => $this->user->name.' '.$this->user->surname,
                        'actionText'=> trans('messages.resetPassword'),
                        'actionUrl' => $this->pathInstance.'/user/password/reset?t='.$this->token.'&e='.$this->user->email,
                    ));

            }
                break;


            case 'confirmWebStore':
            {
                return $this->view('emails.en.user')->subject('Web store confirmed by Admin')
                    ->with(array(
                        'level' => '5',
                        'body'=>'The Admin has confirmed your Web Store.'
                    ));

            }
                break;

            case 'changeWebStore':
            {
                return $this->view('emails.en.admin')->subject('Web store changed by User')
                    ->with(array(
                        'level' => '1',
                        'body'=>'The user '. $this->user->name.' '.$this->user->surname .'  has changed his Web Store. Please confirm it'
                    ));

            }
                break;

            default: break;
        }



    }
}
