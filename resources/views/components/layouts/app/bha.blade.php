<!DOCTYPE html>
<html lang="en">
    <head>
        @include('partials.head', ['title' => 'Dashboard | '.config('app.name')])
    </head>
    <body class="min-h-screen bg-bha-50">
        <flux:sidebar sticky stashable class="border-r border-bha-100 bg-white">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('dashboard') }}" class="mr-4"><x-app-logo /></a>

            <flux:navlist variant="outline">
                <flux:navlist.group heading="Workspace" class="grid">
                    <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('*.dashboard') || request()->routeIs('dashboard')">Dashboard</flux:navlist.item>
                    @if (auth()->user()->role === \App\Enums\UserRole::Administrator)
                        <flux:navlist.item icon="inbox" :href="route('admin.enquiries.index')" :current="request()->routeIs('admin.enquiries.*')">Enquiries</flux:navlist.item>
                    @endif
                </flux:navlist.group>
            </flux:navlist>

            <flux:spacer />

            <div class="mb-3 rounded-2xl bg-bha-50 p-4 text-xs leading-5 text-zinc-600">
                <p class="font-bold text-bha-800">{{ auth()->user()->role->label() }} access</p>
                <p class="mt-1">Appointment tools will appear here in Phase 2.</p>
            </div>

            <flux:dropdown position="bottom" align="start">
                <flux:profile :name="auth()->user()->name" :initials="auth()->user()->initials()" icon-trailing="chevrons-up-down" />
                <flux:menu class="w-[240px]">
                    <div class="px-2 py-2 text-sm">
                        <p class="truncate font-semibold">{{ auth()->user()->name }}</p>
                        <p class="truncate text-xs text-zinc-500">{{ auth()->user()->email }}</p>
                    </div>
                    <flux:menu.separator />
                    <flux:menu.item href="{{ route('home') }}" icon="globe-alt">Public website</flux:menu.item>
                    <flux:menu.item href="{{ route('settings.profile') }}" icon="cog">Account settings</flux:menu.item>
                    <flux:menu.separator />
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">Log out</flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <flux:header class="border-b border-bha-100 bg-white lg:hidden">
            <flux:sidebar.toggle icon="bars-2" inset="left" />
            <flux:spacer />
            <span class="text-xs font-bold text-bha-700">{{ auth()->user()->role->label() }}</span>
        </flux:header>

        {{ $slot }}
        @fluxScripts
    </body>
</html>
