<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Repository\User\InvoiceRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;
use App\Traits\GeneralTrait;

class AdminNotificationController extends Controller
{
    use GeneralTrait;

    protected $invoiceRepository;

    public function __construct(InvoiceRepository $invoiceRepository)
    {
        $this->invoiceRepository = $invoiceRepository;
    }

    public function index()
    {
        $admin = Auth::user();
        if (!$admin || $admin->role !== -1) {
            return $this->returnError('403', 'Unauthorized');
        }
        $notifications = $admin->notifications()->get();
        if(!$notifications)
        return $this->returnError('404', 'Notification not found');
        else return $this->returnData('notifications', $notifications, 'Admin notifications retrieved successfully');
    }

    public function unreadNotifications()
    {
        $admin = Auth::user();
        if (!$admin || $admin->role !== -1) {
            return $this->returnError('403', 'Unauthorized');
        }

        $unreadNotifications = $admin->unreadNotifications()->get();
        return $this->returnData('notifications', $unreadNotifications, 'Unread admin notifications retrieved successfully');
    }

    public function markAsRead($id)
    {
        $admin = Auth::user();
        if (!$admin || $admin->role !== -1) {
            return $this->returnError('403', 'Unauthorized');
        }

        $notification = $admin->notifications()->find($id);
        if (!$notification) {
            return $this->returnError('404', 'Notification not found');
        }

        // Handle payment confirmation if it's an invoice payment notification
        if ($notification->type === 'App\Notifications\InvoicePaymentNotification') {
            $data = $notification->data;
            if (isset($data['invoice_id'])) {
                $result = $this->invoiceRepository->confirmPayment($notification->id, $data['invoice_id']);
                
                // If there was an error with the payment confirmation, return the error
                if (isset($result->original['message']) && $result->getStatusCode() !== 200) {
                    return $this->returnError($result->getStatusCode(), $result->original['message']);
                }
                
                return $this->returnSuccess('Payment confirmed successfully', $result->original);
            }
        }

        $notification->markAsRead();
        return $this->returnSuccess('Notification marked as read');
    }

    public function markAllAsRead()
    {
        $admin = Auth::user();
        if (!$admin || $admin->role !== -1) {
            return $this->returnError('403', 'Unauthorized');
        }

        // Get all unread notifications
        $notifications = $admin->unreadNotifications;
        $results = [];
        
        // Process invoice payment notifications first
        foreach ($notifications as $notification) {
            if ($notification->type === 'App\\Notifications\\InvoicePaymentNotification') {
                $data = $notification->data;
                if (isset($data['invoice_id'])) {
                    $result = $this->invoiceRepository->confirmPayment($notification->id, $data['invoice_id']);
                    if (isset($result->original['message']) && $result->getStatusCode() === 200) {
                        $results[] = [
                            'notification_id' => $notification->id,
                            'message' => $result->original['message'],
                            'status' => $result->original['status'] ?? null,
                            'paid_amount' => $result->original['paid_amount'] ?? null,
                            'remaining' => $result->original['remaining'] ?? null
                        ];
                    }
                }
            }
        }

        // Mark all as read
        $admin->unreadNotifications->markAsRead();
        
        return $this->returnData(
            'results', 
            $results ?: null, 
            $results ? 'All notifications processed successfully' : 'No payment notifications to process',
            $results ? 200 : 204
        );
    }
}
