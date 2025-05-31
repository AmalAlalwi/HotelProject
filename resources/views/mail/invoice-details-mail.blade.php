@component('mail::message')
    # Thank you for booking at our hotel.

    ## Invoice #{{ $invoice->id }}

    **Status:** {{ $invoice->payment_status }}
    **Total:** ${{ $invoice->total_price }}
    **Payment Reference:** {{ $invoice->payment_reference }}

    ---

    ### Items

        | Description       | Number of Days | Unit Price | Total Price |
        |-------------------|----------------|------------|-------------|
    @foreach($invoice->items as $item)
        | {{ $item->description }} | {{ $item->quantity }} | ${{ number_format($item->unit_price, 2) }} | ${{ number_format($item->total_price, 2) }} |
    @endforeach

    ---

    Thanks,
    {{ config('app.name') }}
    Enjoy Your Time
@endcomponent
