<h1>
    Your order status was changed into "{{$order->status}}"
</h1>

<p>
    Link to your order :
    <a href="{{route('order.veiw',$order,true)}}" target="_blank">Order #{{$order->id}}</a>
</p>