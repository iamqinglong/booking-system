<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeclinedBooking extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $booking;
    private $booker;
    public function __construct($booking, $booker)
    {
        $this->booking = $booking;
        $this->booker = $booker;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $ownerName = $this->booker->name;
        $schedule = Carbon::parse($this->booking->selected_schedule)->setTimezone('UTC')->toCookieString();
        $table = $this->booking->table->name;
        $id = $this->booking->id;

        return (new MailMessage)
            ->greeting("Hello, $ownerName")
            ->line("Sorry to announced, Your booking has been declined.")
            ->line("TABLE: $table")
            ->line("DATE: $schedule")
            ->action('CLICK TO CHECK', route('user.bookings.show', $id))
            ->line('Thank you for using our application!');
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
