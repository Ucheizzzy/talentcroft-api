<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaystackService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Paystack;

class PaymentController extends Controller
{

    public function show(Request $request)
    {
        return view('form');
    }

    public function makePayment(Request $request)
    {
        $user = $request->user('api');
        if (!$user) return $this->respondWithError(['open' => 'auth', 'message' => 'Unauthorized'], 403);
        $request->validate([
            'email' => 'required',
            'amount' => 'required'
        ]);
        $paystack = new PaystackService();
        $email = $request->email;
        $amount = $request->amount;
        $payment = $user->payments()->create([
            'reference_id' =>  Paystack::genTranxRef(),
            'email' => $email,
            'amount' => $amount,
        ]);

        if ($payment->amount != 0) {
            $payment->save();
            $data['open'] = 'paid';
            $data['message'] = 'You have successfully purchased talentcroft package';
            $data['payment'] = $payment;
            // return $this->respondWithSuccess($data);
            //TODO: if payment is zero handle auto connect
            // Return a diffent code
        }

        $paystack->setAmount($payment->amount);
        $paystack->setOrderId($payment->id);
        $paystack->addMeta('transaction_id', $payment->id);
        $paystack->addMeta('user_id', $user->id);
        // dd($paystack);
        $gateway = $paystack->initializePayment($data, $payment);
        $status = $gateway->getTargetUrl();
        dd($status);
        if (!$status) return $this->respondWithError($gateway->message);
        $data = $gateway->data;
        $data['open'] = 'gateway';
        return $this->respondWithSuccess($data);
        // return $this->respondWithSuccess(['posts' => ["id" => $payment_id, "user_id" => $user_id, "email" => $payment->email, "amount" => $payment->amount, "reference" => $reference]], 201);
    }

    /**
     * Redirect the User to Paystack Payment Page
     * @return Url
     */


    public function redirectToGateway()
    {
        try{
            return Paystack::getAuthorizationUrl()->redirectNow();
        }catch(\Exception $e) {
            return Redirect::back()->withMessage(['msg'=>'The paystack token has expired. Please refresh the page and try again.', 'type'=>'error']);
        }        
    }

    /**
     * Obtain Paystack payment information
     * @return void
     */
    public function handleGatewayCallback()
    {
       $paymentDetails = Paystack::getPaymentData();
        // dd($paymentDetails);
     $payment = new Payment();
     $payment->user_id = auth()->user()->id;
     $payment->email = $paymentDetails['data']['email'];
     $payment->amount = $paymentDetails['data']['amount'];
     $payment->reference_id = $paymentDetails['data']['reference'];
     $payment->status = $paymentDetails['data']['status'];
     $payment->paid_at = $paymentDetails['data']['paid_at'];
     $payment->save();

     return "paid";
//         $paystack = (new PaystackService())->verifyPayment();
//         $status = 'unknown';
//         $message = $paystack['data']['message'];
//         if ($paystack['data']) {
//             $data = $paystack['data'];
//             $status = $data['status'];

// //            $meta = $data['metadata'];
// //            $metaReference = $meta['reference'];
// //            $amount = $data['amount'];
//             $reference = $data['reference'];
// //            $paidAmount = $amount / 100;
//             $transaction = Payment::whereReference_id($reference)->firstOrFail();
//             $transaction->paid_at = now();
//             $transaction->status = "Success";
//             $transaction->save();
//         }
//         return view('payment.callback', compact('status', 'message'));

    }



//     public function callback(Request $request)
//     {
//         $paystack = (new PaystackService())->verifyPayment();
//         $status = 'unknown';
//         $message = $paystack['data']['message'];
//         if ($paystack['data']) {
//             $data = $paystack['data'];
//             $status = $data['status'];

// //            $meta = $data['metadata'];
// //            $metaReference = $meta['reference'];
// //            $amount = $data['amount'];
//             $reference = $data['reference'];
// //            $paidAmount = $amount / 100;

//             $transaction = Payment::whereReference($reference)->firstOrFail();
//             $transaction->paid_at = now();
//             $transaction->status = $status;
//             $transaction->save();
//         }
//         return view('payment.callback', compact('status', 'message'));
//     }
}