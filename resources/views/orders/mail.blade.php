<h1>Order #{{ $order->id }} {{ $order->status }}</h1>

<p>{{ $order->user->name }}, your order is {{ $order->status }}.</p>