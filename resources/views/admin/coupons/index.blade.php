@extends('admin.layouts.admin')

@section('title', 'Coupons')

@section('content')
<div class="card-glass p-6 rounded-2xl">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Coupons</h2>
            <p class="text-xs text-slate-400 font-medium">Manage discount codes and rules</p>
        </div>
        <a href="{{ route('admin.coupons.create') }}" class="bg-[#a91b43] text-white px-4 py-1.5 rounded-lg text-xs font-bold hover:bg-[#940437] transition-all">
            <i class="fas fa-plus mr-1.5"></i> Add Coupon
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="text-slate-400 text-[10px] font-bold uppercase tracking-widest border-b border-slate-100">
                    <th class="pb-3 px-2">Code</th>
                    <th class="pb-3">Type</th>
                    <th class="pb-3">Discount</th>
                    <th class="pb-3">Min/Max</th>
                    <th class="pb-3">Usage</th>
                    <th class="pb-3">Validity</th>
                    <th class="pb-3">Status</th>
                    <th class="pb-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                @foreach($coupons as $coupon)
                <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-all">
                    <td class="py-3 px-2 font-bold text-slate-800">{{ $coupon->code }}</td>
                    <td class="py-3 text-slate-500 text-xs uppercase">{{ $coupon->type }}</td>
                    <td class="py-3 text-slate-700 text-xs">
                        @if($coupon->type === 'percentage')
                            {{ rtrim(rtrim(number_format($coupon->discount_value, 2), '0'), '.') }}%
                        @else
                            &#8377;{{ number_format($coupon->discount_value, 2) }}
                        @endif
                    </td>
                    <td class="py-3 text-slate-500 text-xs">
                        @if($coupon->min_order_amount)
                            &#8377;{{ number_format($coupon->min_order_amount, 0) }}
                        @else
                            -
                        @endif
                        /
                        @if($coupon->max_discount)
                            &#8377;{{ number_format($coupon->max_discount, 0) }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="py-3 text-slate-500 text-xs">
                        {{ $coupon->times_used }} / {{ $coupon->usage_limit ?? '∞' }}
                    </td>
                    <td class="py-3 text-slate-500 text-xs">
                        @if($coupon->valid_from)
                            {{ $coupon->valid_from->format('d M Y') }}
                        @else
                            -
                        @endif
                        <span class="text-slate-300">→</span>
                        @if($coupon->expires_at)
                            {{ $coupon->expires_at->format('d M Y') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="py-3">
                        <span class="px-2 py-0.5 rounded-md text-[9px] font-bold uppercase tracking-tighter {{ $coupon->status ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">
                            {{ $coupon->status ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="py-3 text-right">
                        <div class="flex justify-end space-x-1">
                            <a href="{{ route('admin.coupons.edit', $coupon->id) }}" class="p-1.5 text-indigo-400 hover:bg-indigo-50 rounded-md transition-all">
                                <i class="fas fa-edit text-xs"></i>
                            </a>
                            <button type="button" onclick="confirmDelete('{{ $coupon->id }}')" class="p-1.5 text-rose-400 hover:bg-rose-50 rounded-md transition-all">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                            <form id="delete-form-{{ $coupon->id }}" action="{{ route('admin.coupons.destroy', $coupon->id) }}" method="POST" class="hidden">
                                @csrf
                                @method('DELETE')
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function confirmDelete(id) {
        Swal.fire({
            title: 'Delete Coupon?',
            text: 'This will remove the coupon permanently.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#a91b43',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        })
    }
</script>
@endpush
