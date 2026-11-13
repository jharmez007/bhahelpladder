<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class EnquiryController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Enquiry::class);

        return view('admin.enquiries.index', [
            'enquiries' => Enquiry::query()->latest()->paginate(20),
        ]);
    }
}
