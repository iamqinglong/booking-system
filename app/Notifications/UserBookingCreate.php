<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserBookingCreate extends Notification
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
        $schedule = Carbon::parse($this->booking->selected_schedule)->setTimezone('UTC')->toCookieString();
        $name = $this->booker->name;
        $table = $this->booking->table->name;
        $id = $this->booking->id;

        return (new MailMessage)
            ->line("You have a new booking request from $name")
            ->line("Table: $table")
            ->line("Schedule: $schedule")
            ->action('CHECK IT NOW', route('admin.bookings.show', $id))
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
