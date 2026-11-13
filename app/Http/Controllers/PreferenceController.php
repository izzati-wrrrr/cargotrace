<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PreferenceController extends Controller
{
    public function currency(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'currency_code' => ['required', 'string', 'size:3', Rule::exists('currencies', 'code')],
        ]);

        $request->user()->update(['currency_code' => $data['currency_code']]);

        return back()->with('success', 'Mata uang tampilan diubah ke ' . $data['currency_code'] . '.');
    }
}