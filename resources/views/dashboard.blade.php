<x-layouts.app title="Dashboard">
    <div class="rounded-xl bg-white p-8 shadow">
        <p class="text-slate-600">
            Halo, {{ auth()->user()->name }}. Kartu KPI, grafik, peta, dan tabel akan ditaruh di sini.
        </p>
    </div>
</x-layouts.app>