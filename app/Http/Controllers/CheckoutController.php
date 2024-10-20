<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Helpers\Cart;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\CartItem;
use App\Models\OrderItem;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CheckoutController extends Controller
{
    private $stripe;
    private $paymentStatusPending;
    private $paymentStatusPaid;
    private $paymentStatusFailed;

    private $rderStatusUnpaid;
    private $rderStatusPaid;
    private $rderStatusCompleted;

    public function  __construct()
    {
        $this->stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY'));

        $this->paymentStatusPending = PaymentStatus::Pending;
        $this->paymentStatusPaid = PaymentStatus::Paid;
        $this->paymentStatusFailed = PaymentStatus::Failed;

        $this->rderStatusUnpaid = OrderStatus::Unpaid;
        $this->rderStatusPaid = OrderStatus::Paid;
        $this->rderStatusCompleted = OrderStatus::Completed;
    }

    public function checkout(Request $request)
    {
       /** @var \App\Models\User $user */
        $user = $request->user();

        $stripeClient = $this->stripe;

        list($products,$cartItems) = Cart::getProductsAndCartItems();
        
        $orderItems = [];
        $lineItems = [];
        $totalPrice = 0;

        foreach ($products as $key => $product) {
            $quantity = $cartItems[$product->id]['quantity'];
            $totalPrice += $product->price * $quantity;

            /* push to array */
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $product->title,
                        // 'images' => [$product->image]
                    ],
                    'unit_amount' => $product->price*100,
                ],
                'quantity' => $cartItems[$product->id]['quantity'],
            ];

            /* push to array */
            $orderItems[] = [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $product->price
            ];
        }

        // dd(route('checkout.success',[],true),route('checkout.failure',[],true));

        $checkoutSession = $stripeClient->checkout->sessions->create([
            'line_items' => $lineItems,
            'mode' => 'payment',
            'customer_creation' => 'always',
            'success_url' => route('checkout.success',[],true).'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout.failure',[],true)
        ]);

        /* create order */
        $orderData = [
            'total_price' => $totalPrice,
            'status' => OrderStatus::Unpaid,
            'created_by' => $user->id,
            'updated_by' => $user->id
        ];
        $order = Order::create($orderData);

        /* create order items */
        foreach ($orderItems as $key => $orderItem) {
            $orderItem['order_id'] = $order->id;
            OrderItem::create($orderItem);
        }

        /* create payment */
        $paymentData = [
            'order_id' => $order->id,
            'amount' => $totalPrice,
            'status' => PaymentStatus::Pending,
            'type' => 'cc',
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'session_id' => $checkoutSession->id
        ];
        Payment::create($paymentData);

        /* delete item from the cart */
        CartItem::where('user_id',$user->id)->delete();

        return redirect($checkoutSession->url);
    }
    
    public function success(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $stripeClient = $this->stripe;

        try {
            $session_id = $request->session_id;
            $session = $stripeClient->checkout->sessions->retrieve($session_id);
            
            /* check session id from payment gateway */
            if (!$session) {
                return view('checkout.failure',['message' => 'Invalid Session ID']);
            }

            $payment = Payment::query()
                ->where('session_id',$session_id)
                ->whereIn('status',[$this->paymentStatusPending->value, $this->paymentStatusPaid->value])
                ->first();
            /* check payment does not exist */
            if (!$payment) {
                throw new NotFoundHttpException();
            }

            if ($payment->status === $this->paymentStatusPending->value) {
                $this->updateOrderAndSession($payment);
            }

            $customer = $stripeClient->customers->retrieve($session->customer);
            
            return view('checkout.success',compact('customer'));
        } catch (NotFoundHttpException $e) {
            throw $e;
        }catch (\Exception $e) {
            return view('checkout.failure',['message' => $e->getMessage()]);
        }
    }

    public function failure(Request $request)
    {
        return view('checkout.failure',['message' => '']);
    }

    public function checkoutOrder(Request $request, Order $order)
    {
        $stripeClient = $this->stripe;

        /** @var \App\Models\User $user */
        $user = $request->user();

        $lineItems = [];

        foreach ($order->items as $key => $item) {
            /* push to array */
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $item->product->title,
                        // 'images' => [$product->image]
                    ],
                    'unit_amount' => $item->unit_price*100,
                ],
                'quantity' => $item->quantity,
            ];
        }

        $checkoutSession = $stripeClient->checkout->sessions->create([
            'line_items' => $lineItems,
            'mode' => 'payment',
            'customer_creation' => 'always',
            'success_url' => route('checkout.success',[],true).'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout.failure',[],true)
        ]);

        $order->payment->session_id = $checkoutSession->id;
        $order->payment->save();

        return redirect($checkoutSession->url);
    }

    public function webhook()
    {
        // // dd("hello");
        // \Stripe\Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

        // $endpoint_secret = 'whsec_dec333005c45c0a385fcbab04c82531c703be1813e3d47e9b6a8c3d4d5697db1';
        // $payload = @file_get_contents('php://input');
        // $event = null;

        // try {
        //     $event = \Stripe\Event::constructFrom(
        //         json_decode($payload, true)
        //     );
        // } catch(\UnexpectedValueException $e) {
        //     // Invalid payload
        //     echo 'Webhook error while parsing basic request.';
        //     return response('',401);
        // }

        // // dd($payload,$_SERVER);

        // if ($endpoint_secret) {
        //     // Only verify the event if there is an endpoint secret defined
        //     // Otherwise use the basic decoded event
        //     $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        //     try {
        //         $event = \Stripe\Webhook::constructEvent(
        //             $payload, $sig_header, $endpoint_secret
        //         );
        //     } catch(\Stripe\Exception\SignatureVerificationException $e) {
        //         // Invalid signature
        //         echo 'Webhook error while validating signature.';
        //         return response('',402);
        //     }
        // }

        // // Handle the event
        // switch ($event->type) {
        // case 'checkout.session.completed':
        //     $paymentIntent = $event->data->object; // contains a \Stripe\PaymentIntent
        //     // Then define and call a method to handle the successful payment intent.
        //     // handlePaymentIntentSucceeded($paymentIntent);
        //     $sessionId = $paymentIntent['id'];

        //     $payment = Payment::query()
        //         ->where([
        //             'session_id' => $sessionId,
        //             'status' => PaymentStatus::Pending
        //         ])
        //         ->first();
        //     /* check payment does not exist */
        //     if ($payment) {
        //         $this->updateOrderAndSession($payment);
        //     }
        //     break;
        // case 'payment_method.attached':
        //     $paymentMethod = $event->data->object; // contains a \Stripe\PaymentMethod
        //     // Then define and call a method to handle the successful attachment of a PaymentMethod.
        //     // handlePaymentMethodAttached($paymentMethod);
        //     break;
        // default:
        //     // Unexpected event type
        //     error_log('Received unknown event type');
        // }

        // return response('',200);
    }

    private function updateOrderAndSession(Payment $payment)
    {
        /* update status payment */
        $payment->status = PaymentStatus::Paid;
        $payment->update();

        /* update status order */
        $order = $payment->order;
        $order->status = OrderStatus::Paid;
        $order->update();
    }
}
