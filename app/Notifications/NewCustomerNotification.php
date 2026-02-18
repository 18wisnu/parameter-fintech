<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewCustomerNotification extends Notification
{
    use Queueable;
    public $customer;

    public function __construct($customer)
    {
        $this->customer = $customer;
    }

    public function via($notifiable)
    {
        return ['database']; // Simpan ke database saja
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Pelanggan Baru!',
            'message' => 'Pelanggan a.n ' . $this->customer->name . ' baru saja didaftarkan.',
            'link' => route('customers.edit', $this->customer->id),
            'icon' => 'user-add',
            'color' => 'bg-blue-500',
        ];
    }
}