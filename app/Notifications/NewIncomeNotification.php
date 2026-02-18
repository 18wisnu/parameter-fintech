<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewIncomeNotification extends Notification
{
    use Queueable;
    public $income;

    public function __construct($income)
    {
        $this->income = $income;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Setoran Masuk!',
            'message' => 'Diterima Rp ' . number_format($this->income->amount) . ' (' . $this->income->description . ')',
            'link' => route('reports.history'), // Arahkan ke history
            'icon' => 'money',
            'color' => 'bg-emerald-500',
        ];
    }
}