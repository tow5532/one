<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;

class Deposit extends Notification
{
    //use Queueable;
    use Notifiable;

    protected $data;

    /**
     * Create a new notification instance.
     *
     * @param array $arr
     */
    public function __construct(array $arr)
    {
        $this->data = $arr;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [TelegramChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return TelegramMessage
     */
    public function toTelegram($notifiable)
    {
        //$text = ' Member '. $notifiable->username .' has applied for a deposit of '. $this->data['deposit_amount'] .' points.';
        $text = '회원 아이디 : '. $notifiable->username .
            '
 입금 신청 : ' . number_format($this->data['deposit_amount']);

        return TelegramMessage::create()
            ->to($this->data['admin_telegram_id'])->content($text);
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
            //
        ];
    }
}
