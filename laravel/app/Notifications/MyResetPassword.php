<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Setting;
use Mail;
use Session;
class MyResetPassword extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
     public $token;
     public function __construct($token)
 {
     $this->token = $token;
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
    // public function toMail($notifiable)
    // {
    //     return (new MailMessage)
    //                 ->line('The introduction to the notification.')
    //                 ->action('Notification Action', url('/'))
    //                 ->line('Thank you for using our application!');
    // }
    public function toMail($notifiable)
{
  $settings =Setting::first();
  // return (new MailMessage)
  //                 ->subject('Reset Password')
  //                 ->greeting('Hello!, '.$notifiable->name)
  //                 ->from($settings->site_email,$settings->site_name)
  //                 ->line('We are sending this email because we recieved a forgot password request.')
  //                 ->action('Reset Password', url('password/reset', $this->token))
  //                 ->line('If you did not request a password reset, no further action is required. Please contact us if you did not submit this request.')
  //                 ->line('Thank you for using'.$settings->site_name);
    $user = $notifiable;
    return (new MailMessage)
                ->subject('Reset Password')
                ->from($settings->site_email,$settings->site_name)
                ->markdown('emails.password_reset', ['user' => $user,
                                                     'token' => $this->token,
                                                     'settings' => $settings]);

// dd($notifiable);
// dd($notifiable->id);
    // return (new MailMessage)
    //     ->view('emails.password_reset')->with('user',$user)
    //     ->subject('Succesfully created new account')
    //     ->line('We are sending this email because we recieved a forgot password request.')
    //     // ->action('Reset Password', url(config('app.url') . url('password/reset', $user->remember_token, false)))
    //     ->action('Reset Password', url('password/reset', $this->token))
    //     ->line('If you did not request a password reset, no further action is required. Please contact us if you did not submit this request.')
    //     // resources/views/emails/password_reset.blade.php will be used instead.
    //     ;
//
//         $settings =Setting::first();
//         $confirmation_code = str_random(60);
//         $email_data = array(
//           'name' => $user['name'],
//           'email' => $user['email'],
//           'confirmation_code' => $confirmation_code,
//           'settings' => $settings,
//         );
//     return    Mail::send('emails.password_reset', $email_data, function($message)use($email_data,$settings)  {
//             $message->from($settings->site_email,$settings->site_name);
//             $message->to($email_data['email'], $email_data['name']);
//             $message->subject('Side Kick');
//         });
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
