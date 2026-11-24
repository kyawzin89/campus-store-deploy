<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Campus Store Management')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen flex flex-col">

    @auth
    <nav class="bg-slate-900 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-6">
                <a href="{{ route('pos.index') }}" class="text-lg font-bold tracking-tight text-emerald-400">
                    Campus Store POS
                </a>

                <div class="flex space-x-2">
                    <a href="{{ route('pos.index') }}"
                       class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('pos.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        POS Checkout
                    </a>

                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.inventory') }}"
                           class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            Inventory & Sales
                        </a>
                    @endif
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <div class="text-sm text-right">
                    <span class="font-medium">{{ auth()->user()->name }}</span>
                    <span class="ml-2 px-2 py-0.5 text-xs uppercase font-semibold rounded-full {{ auth()->user()->isAdmin() ? 'bg-amber-500/20 text-amber-300 border border-amber-400/40' : 'bg-sky-500/20 text-sky-300 border border-sky-400/40' }}">
                        {{ auth()->user()->role }}
                    </span>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold px-3 py-1.5 rounded-md transition">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>
    @endauth

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @if(session('success'))
            <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-3 text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 rounded-lg bg-rose-50 border border-rose-300 text-rose-800 px-4 py-3 text-sm font-medium">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 rounded-lg bg-rose-50 border border-rose-300 text-rose-800 px-4 py-3 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

</body>
</html>
