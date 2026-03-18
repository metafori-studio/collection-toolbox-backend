<?php

namespace Metafori\Core\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class SetPassword extends Notification
{
    /**
     * The password set token.
     *
     * @var string
     */
    public $token;

    /**
     * The callback that should be used to create the set password URL.
     *
     * @var (\Closure(mixed, string): string)|null
     */
    public static $createUrlCallback;

    /**
     * The callback that should be used to build the mail message.
     *
     * @var (\Closure(mixed, string): MailMessage|\Illuminate\Contracts\Mail\Mailable)|null
     */
    public static $toMailCallback;

    /**
     * Create a notification instance.
     *
     * @param  string  $token
     */
    public function __construct(#[\SensitiveParameter] $token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's channels.
     *
     * @param  mixed  $notifiable
     * @return array|string
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $this->token);
        }

        return $this->buildMailMessage($this->setUrl($notifiable));
    }

    /**
     * Get the set password notification mail message for the given URL.
     *
     * @param  string  $url
     * @return MailMessage
     */
    protected function buildMailMessage($url)
    {
        return (new MailMessage)
            ->subject(Lang::get('Set Password Notification'))
            ->line(Lang::get('You are receiving this email because a password needs to be set for your account.'))
            ->action(Lang::get('Set Password'), $url)
            ->line(Lang::get('This password set link will expire in :count minutes.', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]))
            ->line(Lang::get('If you believe this email was sent in error, no further action is required.'));
    }

    /**
     * Get the set password URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function setUrl($notifiable)
    {
        if (static::$createUrlCallback) {
            return call_user_func(static::$createUrlCallback, $notifiable, $this->token);
        }

        return url(route('password.set', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordSet(),
        ], false));
    }

    /**
     * Set a callback that should be used when creating the set password button URL.
     *
     * @param  \Closure(mixed, string): string  $callback
     * @return void
     */
    public static function createUrlUsing($callback)
    {
        static::$createUrlCallback = $callback;
    }

    /**
     * Set a callback that should be used when building the notification mail message.
     *
     * @param  \Closure(mixed, string): (MailMessage|\Illuminate\Contracts\Mail\Mailable)  $callback
     * @return void
     */
    public static function toMailUsing($callback)
    {
        static::$toMailCallback = $callback;
    }
}
