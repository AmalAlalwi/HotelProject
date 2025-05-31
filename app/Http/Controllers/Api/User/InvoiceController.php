<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Repository\User\InvoiceRepository;
use Illuminate\Http\Request;


class InvoiceController extends Controller
{
    protected $invoice;
    public function __construct(InvoiceRepository $invoice){
        $this->invoice = $invoice;
    }
    public function getPaidInvoices(Request $request){
        $request->validate([
            'per_page' => 'integer|min:1|max:100'
        ]);
        return $this->invoice->getPaidInvoices($request);
    }
    public function getUnpaidInvoices(Request $request)
    {
        $request->validate([
            'per_page' => 'integer|min:1|max:100'
        ]);
        return $this->invoice->getUnpaidInvoices($request);
    }
    public function getPartialInvoices(Request $request){
        $request->validate([
            'per_page' => 'integer|min:1|max:100'
        ]);
        return $this->invoice->getPartialInvoices($request);
    }
    public function downloadPDF($id){

        return $this->invoice->downloadPDF($id);
    }
    public function simulatePayment(Request $request,$id){
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);
        return $this->invoice->simulatePayment($request,$id);
    }
    public function showInvoice($id){
        return $this->invoice->showInvoice($id);
    }

}
