<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Invoice;
use App\Models\User;

class InvoicePaymentNotification extends Notification
{
    use Queueable;

    protected $invoice;
    protected $user;
    protected $amount;
    protected $type;

    /**
     * Create a new notification instance.
     */
    public function __construct(Invoice $invoice, User $user, $amount = 0, $type = 'confirmed')
    {
        $this->invoice = $invoice;
        $this->user = $user;
        $this->amount = (float) $amount;
        $this->type = $type;
    }
    
    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'invoice_id' => $this->invoice->id,
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'amount' => $this->amount,
            'payment_status' => $this->invoice->payment_status,
            'type' => $this->type,
            'message' => $this->getMessage(),
        ];
    }

    private function getMessage()
    {
        if ($this->type === 'requested') {
            return "Payment request for Invoice #{$this->invoice->id} of amount \${$this->amount} from {$this->user->name}. Please review and confirm.";
        } else {
            return "Invoice #{$this->invoice->id} has received a payment from {$this->user->name}. New status: {$this->invoice->payment_status}.";
        }
    }
}
