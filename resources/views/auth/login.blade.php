@extends('layouts.app')

@section('title', 'Staff Login — Campus Store')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-xl shadow-lg border border-slate-200 p-8">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-slate-900">Campus Store System</h1>
            <p class="text-sm text-slate-500 mt-1">Sign in with your staff account to begin</p>
        </div>

        <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Staff Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                <input id="password" name="password" type="password" required
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
            </div>

            <button type="submit"
                    class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 rounded-lg text-sm transition">
                Sign In
            </button>
        </form>

        <div class="mt-6 pt-5 border-t border-slate-200">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3 text-center">Quick Demo Fill</p>
            <div class="grid grid-cols-2 gap-2">
                <button type="button"
                        onclick="fillLogin('admin@campusstore.test', 'password')"
                        class="text-xs bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 font-medium py-2 px-3 rounded-lg transition">
                    Fill Admin Login
                </button>
                <button type="button"
                        onclick="fillLogin('cashier@campusstore.test', 'password')"
                        class="text-xs bg-sky-50 hover:bg-sky-100 text-sky-800 border border-sky-200 font-medium py-2 px-3 rounded-lg transition">
                    Fill Cashier Login
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function fillLogin(email, password) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = password;
    }
</script>
@endsection
