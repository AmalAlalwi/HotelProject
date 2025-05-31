<h1 style="text-align:center;">Invoice #{{ $invoice->id }}</h1>

<p><strong>Payment Reference:</strong> {{ $invoice->payment_reference ?? 'N/A' }}</p>
<p><strong>Status:</strong> {{ ucfirst($invoice->payment_status) }}</p>
<p><strong>Total:</strong> ${{ number_format($invoice->total_price, 2) }}</p>

<h3>Invoice Items</h3>
<table border="1" width="100%" cellpadding="10" cellspacing="0" style="border-collapse: collapse;">
    <thead style="background-color: #f2f2f2;">
    <tr>
        <th>#</th>
        <th>Description</th>
        <th>Type</th>
        <th>Unit Price</th>
        <th>Number of Days</th>
        <th>Total</th>
    </tr>
    </thead>
    <tbody>
    @foreach($invoice->items as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->description }}</td>
            <td>{{ ucfirst($item->item_type) }}</td>
            <td>${{ number_format($item->unit_price, 2) }}</td>
            <td>{{ $item->quantity }}</td>
            <td>${{ number_format($item->total_price, 2) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
