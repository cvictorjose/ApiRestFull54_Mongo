<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class VowChannel
{

    public function send($notifiable, Notification $notification)
    {
        $data = $notification->toDatabase($notifiable);
        if (!$data){ return false;}

        $notification = new \App\Notification([
            'subject_id'=> $data['subject_id'],
            'predicate' => $data['predicate'],
            'complement'=> $data['complement']
        ]);

        $notifiable->notification()->save($notification);
        $old=$notifiable->notification()->get()->reverse()->slice(20);

       foreach ($old as $o)
            $o->delete();

        return $notifiable->_id;

    }

}