<x-sidenav.nav-item
    href="{{ route('scm-bid.index') }}"
    title="Bids"
    :active="request()->routeIs('scm-bid.index')">
    <x-slot:icon>
        <x-icons.chart />
    </x-slot>
</x-sidenav.nav-item>
