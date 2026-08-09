@extends('admin.layouts.app')
@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Contact</th>
                                <th>Quantity</th>
                                <th>Total Price</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                            <tr>
                                <td>{{ $order->id }}</td>
                                <td>{{ $order->name }}</td>
                                <td>{{ $order->contact_no }}</td>
                                <td>{{ $order->quantity }}</td>
                                <td>{{ $order->total_price }}</td>
                                <td>
                                    <span class="badge @if($order->status=='completed') bg-success
                                    @elseif($order->status=='processing') bg-warning
                                    @elseif($order->status=='pending') bg-secondary
                                    @else bg-danger @endif">{{ ucfirst($order->status) }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.multi_express.order.show', [$deal->id,$order->id]) }}" class="btn btn-sm btn-outline-primary">View</a>
                                    <form action="{{ route('admin.multi_express.order.delete', [$deal->id,$order->id]) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="text-center">No orders found</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($orders->hasPages())
            <div class="card-footer">
                {{ paginateLinks($orders) }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
