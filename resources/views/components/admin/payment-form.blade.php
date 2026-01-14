@props(['payment' => null])
<div class="row p-4 g-2">
    <div class="col-md-4">
        <label class="form-label">Payment Method</label>
        <select class="form-select" name="payment_method">
            <option value="cash" selected @selected(old('payment_method', $payment->payment_method ?? '') == 'cash')>Cash</option>
            <option value="creditcard" @selected(old('payment_method', $payment->payment_method ?? '') == 'creditcard')>Credit Card</option>
            <option value="banktransfer" @selected(old('payment_method', $payment->payment_method ?? '') == 'banktransfer')>Bank Transfer</option>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Transaction ID</label>
        <input name="transaction_id" type="text" class="form-control" value="{{ old('transaction_id', $payment->transaction_id ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Tax</label>
        <input name="tax" type="number" step="0.01" class="form-control" value="{{ old('tax', $payment->tax ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Base Price Code</label>
        <input required name="base_price_code" type="text" class="form-control" value="{{ old('base_price_code', $payment->base_price_code ?? 'PKR') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Base Price</label>
        <input required name="base_price" type="number" step="0.01" class="form-control" value="{{ old('base_price', $payment->base_price ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Merchant Fee (%)</label>
        <input name="merchant_fee" type="number" step="0.01" class="form-control" value="{{ old('merchant_fee', $payment->merchant_fee ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Service Fee</label>
        <input name="service_fee" type="number" step="0.01" class="form-control" value="{{ old('service_fee', $payment->service_fee ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            <option value="success" selected @selected(old('status', $payment->status ?? '') == 'success')>Success</option>
            <option value="pending" @selected(old('status', $payment->status ?? '') == 'pending')>Pending</option>
            <option value="failed" @selected(old('status', $payment->status ?? '') == 'failed')>Failed</option>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Refund Status</label>
        <input name="refund_status" type="text" class="form-control" value="{{ old('refund_status', $payment->refund_status ?? '') }}">
    </div>    
</div>
