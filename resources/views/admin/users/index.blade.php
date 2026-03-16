@extends('admin.layouts.admin')

@section('title', 'Users')

@section('content')
<div class="card-glass p-6 rounded-2xl">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-800">User Management</h2>
            <p class="text-xs text-slate-400">View customer profiles, status, and spending</p>
        </div>

        <form method="GET" action="{{ route('admin.users.index') }}" class="flex items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, email, phone"
                   class="w-64 bg-white border border-slate-200 px-3 py-2 rounded-lg text-xs outline-none focus:border-[#a91b43] transition-all text-slate-700">
            <button type="submit" class="px-4 py-2 rounded-lg text-xs font-bold bg-[#a91b43] text-white">Search</button>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="text-slate-400 text-[10px] font-bold uppercase tracking-widest border-b border-slate-100">
                    <th class="pb-3 px-2 font-bold">User ID</th>
                    <th class="pb-3 font-bold">User</th>
                    <th class="pb-3 font-bold">Status</th>
                    <th class="pb-3 font-bold">Role</th>
                    <th class="pb-3 font-bold">Registration</th>
                    <th class="pb-3 font-bold">Last Login</th>
                    <th class="pb-3 font-bold">Orders</th>
                    <th class="pb-3 font-bold">Total Spent</th>
                    <th class="pb-3 font-bold text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                @forelse($users as $user)
                    <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-all">
                        <td class="py-3 px-2">
                            <span class="font-bold text-[#a91b43]">#{{ $user->id }}</span>
                        </td>
                        <td class="py-3">
                            <div class="flex items-center gap-3">
                                @php
                                    $avatar = $user->profile_picture
                                        ? asset('uploads/users/' . $user->profile_picture)
                                        : null;
                                @endphp
                                @if($avatar)
                                    <img src="{{ $avatar }}" alt="{{ $user->name }}" class="w-9 h-9 rounded-full object-cover border border-slate-200">
                                @else
                                    <div class="w-9 h-9 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-xs">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="font-bold text-slate-800">{{ $user->name }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $user->email }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $user->phone ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3">
                            <span class="px-2 py-0.5 rounded-md text-[9px] font-bold uppercase tracking-tighter {{ $user->account_status === 'active' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">
                                {{ $user->account_status }}
                            </span>
                        </td>
                        <td class="py-3 text-[10px] text-slate-500 font-bold uppercase">
                            {{ $user->role }}
                        </td>
                        <td class="py-3 text-[10px] text-slate-500 font-bold">
                            {{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}
                        </td>
                        <td class="py-3 text-[10px] text-slate-500 font-bold">
                            {{ $user->last_login_at ? $user->last_login_at->format('d M Y, h:i A') : '-' }}
                        </td>
                        <td class="py-3 text-[10px] text-slate-500 font-bold">
                            {{ $user->orders_count ?? 0 }}
                        </td>
                        <td class="py-3 text-[10px] text-slate-700 font-bold">
                            &#8377;{{ number_format($user->orders_sum_grand_total ?? 0, 2) }}
                        </td>
                        <td class="py-3 text-right">
                            <div class="flex justify-end space-x-1">
                                <a href="{{ route('admin.users.show', $user->id) }}" class="p-1.5 text-indigo-400 hover:bg-indigo-50 rounded-md transition-all" title="View Profile">
                                    <i class="fas fa-eye text-xs"></i>
                                </a>
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="p-1.5 text-amber-400 hover:bg-amber-50 rounded-md transition-all" title="Edit User">
                                    <i class="fas fa-edit text-xs"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="py-10 text-center text-slate-400 italic">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $users->appends(request()->query())->links() }}
    </div>
</div>
@endsection
