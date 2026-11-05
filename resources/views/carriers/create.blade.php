<x-layouts.app title="Tambah Carrier">
    <div class="max-w-xl rounded-xl bg-white p-8 shadow">
        <form method="POST" action="{{ route('carriers.store') }}">
            @csrf
            @include('carriers._form')

            <div class="mt-6 flex gap-3">
                <button type="submit" class="rounded-lg bg-slate-900 px-5 py-2 text-sm font-medium text-white hover:bg-slate-700">Simpan</button>
                <a href="{{ route('carriers.index') }}" class="rounded-lg border border-slate-300 px-5 py-2 text-sm hover:bg-slate-50">Batal</a>
            </div>
        </form>
    </div>
</x-layouts.app>