<?php

namespace App\Repository\User;

use App\Interfaces\User\InvoiceInterface;
use App\Models\Invoice;
use App\Traits\GeneralTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\Mail;

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
            'amount' => 'required|numeric|min:0.01',
        ]);

        $amount = $request->amount;

        $invoice->paid_amount += $amount;

        if ($invoice->paid_amount >= $invoice->total_price) {
            $invoice->payment_status = 'paid';
            $this->invoiceService->sendInvoiceEmail($invoice->id);
        } elseif ($invoice->paid_amount > 0) {
            $invoice->payment_status = 'partial';
        }

        $invoice->save();

        return response()->json([
            'message' => 'Payment recorded successfully.',
            'status' => $invoice->payment_status,
            'paid_amount' => $invoice->paid_amount,
            'remaining' => $invoice->total_price - $invoice->paid_amount
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
}
