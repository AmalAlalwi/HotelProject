<?php

namespace App\Repository\User;

use App\Interfaces\User\InvoiceInterface;
use App\Models\Invoice;
use App\Traits\GeneralTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\Mail;
use App\Notifications\InvoicePaymentNotification;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Notifications\DatabaseNotification;

class InvoiceRepository implements InvoiceInterface
{
    use GeneralTrait;
    protected $invoiceService;
    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }
    public function getUnpaidInvoices($request){
        $user = auth()->user();
        $unpaidInvoices = Invoice::where('user_id', $user->id)
            ->where('payment_status', 'unpaid')
            ->with('items')
            ->paginate($request->per_page??10);

        return $this->returnData('invoices', $unpaidInvoices,"Invoices returned successfully");
    }
    public function getPaidInvoices($request){
        $user = auth()->user();
        $paidInvoices = Invoice::where('user_id', $user->id)
            ->where('payment_status', 'paid')
            ->with('items')
            ->paginate($request->per_page??10);

        return $this->returnData('invoices', $paidInvoices,"Invoices returned successfully");
    }
    public function downloadPDF($id)
    {
        $invoice = Invoice::with('items')->findOrFail($id);

        $pdf = PDF::loadView('invoices.pdf', compact('invoice'));
        return $pdf->download("invoice_{$invoice->id}.pdf");
    }
    public function simulatePayment($request, $invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);
        $request->validate([
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:' . $invoice->total_price,
            ],
        ]);

        $amount = (float) $request->amount;

        // Instead of processing payment, notify admin about the payment request
        $admin = User::where('role', -1)->first(); // Assuming an 'is_admin' flag on the User model
        $notificationId = null;
        
        if ($admin) {
            $notification = new InvoicePaymentNotification($invoice, $invoice->user, $amount, 'requested');
            // Send the notification
            $admin->notify($notification);
            
            // Give the notification a moment to be saved to the database
            usleep(100000); // Sleep for 0.1 seconds
            
            // Get the notification ID by finding the most recent notification with matching data
            // Add a small time window to make the query more reliable
            $notificationRecord = $admin->notifications()
                ->where('type', InvoicePaymentNotification::class)
                ->where('data->invoice_id', $invoice->id)
                ->where('data->amount', $amount)
                ->where('created_at', '>=', now()->subSeconds(5)) // Only look at notifications created in the last 5 seconds
                ->latest()
                ->first();
                
            $notificationId = $notificationRecord ? $notificationRecord->id : null;
        }

        return response()->json([
            'message' => 'Payment request submitted successfully. Awaiting admin confirmation.',
            'requested_amount' => $amount,
            'invoice_id' => $invoice->id,
            'notification_id' => $notificationId // Return the notification ID
        ]);
    }
    public function getPartialInvoices($request){
        $user = auth()->user();
        $partialInvoices = Invoice::where('user_id', $user->id)
            ->where('payment_status', 'partial')
            ->with('items')
            ->paginate($request->per_page??10);

        return $this->returnData('invoices', $partialInvoices,"Invoices returned successfully");
    }
public function showInvoice($id)
{
   $invoice = Invoice::with('items')->findOrFail($id);
   return $this->returnData('invoice', $invoice,"Invoice returned successfully");
}

    public function confirmPayment($notificationId, $invoiceId)
    {
        $invoice = Invoice::find($invoiceId);
        if(!$invoice){
            return response()->json([
                'message' => 'Invoice not found.'
            ], 404);
        }
        $notification = DatabaseNotification::find($notificationId);
        if(!$notification){
            return response()->json([
                'message' => 'Notification not found.'
            ], 404);
        }
        $amount = $notification->data['amount'];

        $invoice->paid_amount += $amount;

        if ($invoice->paid_amount >= $invoice->total_price) {
            $invoice->payment_status = 'paid';
        } elseif ($invoice->paid_amount > 0) {
            $invoice->payment_status = 'partial';
        }

        $invoice->save();

        // Mark the notification as read after processing
        $notification->markAsRead();

        // Optionally notify the user that their payment has been confirmed by admin
        // Mail::to($invoice->user->email)->send(new PaymentConfirmedMail($invoice));

        return response()->json([
            'message' => 'Payment confirmed successfully by admin.',
            'status' => $invoice->payment_status,
            'paid_amount' => $invoice->paid_amount,
            'remaining' => $invoice->total_price - $invoice->paid_amount
        ]);
    }
}
