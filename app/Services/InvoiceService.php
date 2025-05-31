<?php

namespace App\Services;
use App\Models\Invoice;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceDetailsMail;
class InvoiceService
{


    public function sendInvoiceEmail($id)
    {
        $invoice = Invoice::with('items')->findOrFail($id);

        Mail::to($invoice->user->email)->send(new InvoiceDetailsMail($invoice));

        return response()->json(['message' => 'Invoice sent successfully']);
    }
    public function addItemOrCreateInvoice($userId, $itemType, $itemId, $description, $quantity, $unitPrice)
    {
        $total = $quantity * $unitPrice;

        // نحصل على أول فاتورة غير مدفوعة بالكامل
        $invoice = \App\Models\Invoice::where('user_id', $userId)
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->first();

        // إذا لم توجد فاتورة غير مدفوعة بالكامل، ننشئ واحدة جديدة
        if (!$invoice) {
            $invoice = \App\Models\Invoice::create([
                'user_id' => $userId,
                'total_price' => 0,
                'payment_status' => 'unpaid',
                'issued_at' => now(),
            ]);
        }

        // نضيف البند للفاتورة
        $invoice->items()->create([
            'item_type' => $itemType,
            'item_id' => $itemId,
            'description' => $description,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $total,
        ]);

        $invoice->update([
            'total_price' => $invoice->total_price + $total,
        ]);
        $this->sendInvoiceEmail($invoice->id);

        return $invoice;
    }

}
