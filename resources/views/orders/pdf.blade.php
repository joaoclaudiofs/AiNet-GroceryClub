<h1>Order #{{ $order->id }}</h1>

<p>Date: {{ $order->date }}</p>
<p>Status: {{$order->status }}</p>
<p>User: {{ $order->user->name }}</p>

<ul>
    @foreach($order->item_orders as $item)
        <li>
            <img
                src="{{ $item->product->photoEncode64 }}"
                style="
                    width:80px; 
                    height:80px; 
                    object-fit:cover; 
                    margin-right:10px; 
                    vertical-align:middle"
            />

            {{ $item->product->name ?? 'Deleted product' }} (x{{ $item->quantity }}) - €{{ $item->unit_price }}
        </li>
    @endforeach
</ul>

<p>Total: €{{ $order->total }}</p>