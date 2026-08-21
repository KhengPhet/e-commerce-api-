<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use KHQR\BakongKHQR;
use KHQR\Helpers\KHQRData;
use KHQR\Models\IndividualInfo;

class PaymentController extends Controller
{
    // CREATE KHQR
    public function create(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'amount'   => 'required|numeric|min:1',
        ]);

        $order = Order::findOrFail($request->order_id);
        if ($order->status === 'paid') {
            return response()->json(['error'=>'Order already paid'], 400);
        }

        $amount = max(1, (int) round((float) $request->amount));

        $merchant = new IndividualInfo(
            bakongAccountID: env('BAKONG_ACCOUNT_ID'),
            merchantName: env('BAKONG_MERCHANT_NAME'),
            merchantCity: env('BAKONG_CITY'),
            currency: KHQRData::CURRENCY_KHR,
            amount: (float) $amount
        );

        $qrResponse = BakongKHQR::generateIndividual($merchant);

        $qrText = $qrResponse->data['qr']
               ?? $qrResponse->data['qrString']
               ?? null;

        if (!$qrText || !isset($qrResponse->data['md5'])) {
            return response()->json(['error'=>'KHQR failed'], 500);
        }

        $payment = Payment::create([
            'order_id' => $order->id,
            'md5'      => $qrResponse->data['md5'],
            'qr_code'  => $qrText,
            'amount'   => $amount,
            'status'   => 'PENDING',
        ]);

        return response()->json([
            'md5' => $payment->md5,
            'qr_text' => $payment->qr_code,
        ]);
    }

    // VERIFY PAYMENT
    public function verify(Request $request)
    {
        $request->validate(['md5'=>'required|string']);

        $payment = Payment::where('md5',$request->md5)->firstOrFail();

        if ($payment->status === 'PAID') {
            return response()->json(['status'=>'PAID']);
        }

        try {
            $bakong = new BakongKHQR(env('BAKONG_TOKEN'));
            $result = $bakong->checkTransactionByMD5($payment->md5);

            if ((string)($result['responseCode'] ?? '1') === '0') {
                $payment->update(['status'=>'PAID']);
                $payment->order->update(['status'=>'paid']);
                return response()->json(['status'=>'PAID']);
            }
        } catch (\Throwable $e) {
            \Log::error('Bakong verify failed: '.$e->getMessage());
        }

        return response()->json(['status'=>'PENDING']);
    }
}
