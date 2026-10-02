<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $status = in_array($request->query('status'), Lead::STATUSES, true) ? $request->query('status') : null;

        return view('admin.leads.index', [
            'leads' => Lead::with('product.category', 'client')->when($status, fn ($q) => $q->where('status', $status))->latest()->paginate(30)->withQueryString(),
            'status' => $status,
            'counts' => Lead::selectRaw('status, COUNT(*) as n')->groupBy('status')->pluck('n', 'status'),
        ]);
    }

    public function update(Request $request, Lead $lead)
    {
        $lead->update($request->validate(['status' => ['required', Rule::in(Lead::STATUSES)]]));

        return back()->with('status', __('admin.common.saved'));
    }
}
