@extends('admin.layouts.admin')

@section('title', 'Edit User')

@section('content')
<div class="card-glass p-6 rounded-2xl">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Edit User</h2>
            <p class="text-xs text-slate-400">Update profile and manage addresses</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.users.show', $user->id) }}" class="px-4 py-2 rounded-lg text-xs font-bold bg-slate-100 text-slate-600">View</a>
            <a href="{{ route('admin.users.index') }}" class="px-4 py-2 rounded-lg text-xs font-bold bg-slate-100 text-slate-600">Back</a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.users.update', $user->id) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700">Full Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full bg-slate-50 border border-slate-200 px-3 py-2 rounded-lg text-sm outline-none focus:border-[#a91b43] transition-all text-slate-800">
            </div>
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full bg-slate-50 border border-slate-200 px-3 py-2 rounded-lg text-sm outline-none focus:border-[#a91b43] transition-all text-slate-800">
            </div>
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full bg-slate-50 border border-slate-200 px-3 py-2 rounded-lg text-sm outline-none focus:border-[#a91b43] transition-all text-slate-800">
            </div>
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700">Role</label>
                <input type="text" name="role" value="{{ old('role', $user->role) }}" class="w-full bg-slate-50 border border-slate-200 px-3 py-2 rounded-lg text-sm outline-none focus:border-[#a91b43] transition-all text-slate-800">
            </div>
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700">Account Status</label>
                <select name="account_status" class="w-full bg-slate-50 border border-slate-200 px-3 py-2 rounded-lg text-sm outline-none focus:border-[#a91b43] transition-all text-slate-800">
                    <option value="active" {{ old('account_status', $user->account_status) === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('account_status', $user->account_status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700">Gender</label>
                <select name="gender" class="w-full bg-slate-50 border border-slate-200 px-3 py-2 rounded-lg text-sm outline-none focus:border-[#a91b43] transition-all text-slate-800">
                    <option value="">Select</option>
                    <option value="male" {{ old('gender', $user->gender) === 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ old('gender', $user->gender) === 'female' ? 'selected' : '' }}>Female</option>
                    <option value="other" {{ old('gender', $user->gender) === 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700">DOB</label>
                <input type="date" name="dob" value="{{ old('dob', optional($user->dob)->format('Y-m-d')) }}" class="w-full bg-slate-50 border border-slate-200 px-3 py-2 rounded-lg text-sm outline-none focus:border-[#a91b43] transition-all text-slate-800">
            </div>
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700">Profile Picture</label>
                <input type="file" name="profile_picture" class="w-full bg-white border border-slate-200 px-3 py-2 rounded-lg text-sm outline-none focus:border-[#a91b43] transition-all text-slate-700">
                @if($user->profile_picture)
                    <div class="text-[10px] text-slate-400">Current: {{ $user->profile_picture }}</div>
                @endif
            </div>
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700">New Password</label>
                <input type="password" name="password" class="w-full bg-slate-50 border border-slate-200 px-3 py-2 rounded-lg text-sm outline-none focus:border-[#a91b43] transition-all text-slate-800" placeholder="Leave empty to keep current">
            </div>
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700">Confirm Password</label>
                <input type="password" name="password_confirmation" class="w-full bg-slate-50 border border-slate-200 px-3 py-2 rounded-lg text-sm outline-none focus:border-[#a91b43] transition-all text-slate-800">
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-[#a91b43] text-white px-10 py-2.5 rounded-xl text-sm font-bold hover:bg-[#940437] shadow-xl shadow-pink-900/10 transition-all active:scale-95">
                Save Changes
            </button>
        </div>
    </form>

    <div class="mt-10">
        <h3 class="text-sm font-bold text-slate-800 mb-3">Addresses</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($user->addresses as $address)
                <div class="p-4 rounded-xl border border-slate-100 bg-white">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-slate-700">{{ $address->label ?? 'Address' }}</span>
                        <div class="flex items-center gap-2">
                            @if($address->is_default)
                                <span class="text-[9px] font-bold uppercase text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">Default</span>
                            @endif
                            <form method="POST" action="{{ route('admin.users.addresses.destroy', [$user->id, $address->id]) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-[10px] font-bold text-rose-500">Remove</button>
                            </form>
                        </div>
                    </div>
                    <div class="text-xs text-slate-600 leading-relaxed">
                        {{ $address->address1 }}<br>
                        @if($address->address2){{ $address->address2 }}<br>@endif
                        {{ $address->city }}, {{ $address->state }} {{ $address->zip }}<br>
                        {{ $address->country }}<br>
                        @if($address->landmark)<span class="text-slate-400">Landmark: {{ $address->landmark }}</span>@endif
                    </div>
                </div>
            @empty
                <div class="text-xs text-slate-400 italic">No addresses saved.</div>
            @endforelse
        </div>

        <div class="card-glass p-5 rounded-2xl mt-6">
            <h4 class="text-sm font-bold text-slate-800 mb-4">Add Address</h4>
            <form method="POST" action="{{ route('admin.users.addresses.store', $user->id) }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Label</label>
                    <input type="text" name="label" class="w-full bg-slate-50 border border-slate-200 px-3 py-2 rounded-lg text-sm outline-none focus:border-[#a91b43] transition-all text-slate-800" placeholder="Home / Office">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Landmark</label>
                    <input type="text" name="landmark" class="w-full bg-slate-50 border border-slate-200 px-3 py-2 rounded-lg text-sm outline-none focus:border-[#a91b43] transition-all text-slate-800" placeholder="Near temple">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Address Line 1</label>
                    <input type="text" name="address1" required class="w-full bg-slate-50 border border-slate-200 px-3 py-2 rounded-lg text-sm outline-none focus:border-[#a91b43] transition-all text-slate-800">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Address Line 2</label>
                    <input type="text" name="address2" class="w-full bg-slate-50 border border-slate-200 px-3 py-2 rounded-lg text-sm outline-none focus:border-[#a91b43] transition-all text-slate-800">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">City</label>
                    <input type="text" name="city" required class="w-full bg-slate-50 border border-slate-200 px-3 py-2 rounded-lg text-sm outline-none focus:border-[#a91b43] transition-all text-slate-800">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">State</label>
                    <input type="text" name="state" required class="w-full bg-slate-50 border border-slate-200 px-3 py-2 rounded-lg text-sm outline-none focus:border-[#a91b43] transition-all text-slate-800">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">ZIP</label>
                    <input type="text" name="zip" class="w-full bg-slate-50 border border-slate-200 px-3 py-2 rounded-lg text-sm outline-none focus:border-[#a91b43] transition-all text-slate-800">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Country</label>
                    <input type="text" name="country" value="India" class="w-full bg-slate-50 border border-slate-200 px-3 py-2 rounded-lg text-sm outline-none focus:border-[#a91b43] transition-all text-slate-800">
                </div>
                <div class="flex items-center gap-2 md:col-span-2">
                    <input type="checkbox" name="is_default" value="1" class="accent-[#a91b43]">
                    <label class="text-xs font-bold text-slate-700">Set as default address</label>
                </div>
                <div class="md:col-span-2 flex justify-end">
                    <button type="submit" class="bg-slate-800 text-white px-8 py-2 rounded-lg text-xs font-bold hover:bg-slate-900">Add Address</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
