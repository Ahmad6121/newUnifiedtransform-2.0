<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\JobTitle;
use App\Models\SchoolSession;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    /**
     * Staff page = وظائف بدون Login
     * (عدّلها لو بدك تضيف وظائف ثانية)
     */
    private $nonLoginJobTitles = ['Driver', 'Cleaner'];

    private function currentSessionId(): int
    {
        if (session()->has('browse_session_id')) {
            return (int) session('browse_session_id');
        }

        $latest = SchoolSession::latest()->first();
        return $latest ? (int) $latest->id : 1;
    }

    /**
     * GET /staff
     * Route name: staff.employees.index
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->get('search', ''));

        $query = Staff::with('jobTitle')->orderByDesc('id');

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('first_name', 'like', "%{$q}%")
                    ->orWhere('last_name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            });
        }

        // ✅ paginate (عشان links() تشتغل)
        $staff = $query->paginate(10)->withQueryString();

        return view('staff.employees.index', compact('staff'));
    }

    /**
     * GET /staff/create
     * Route name: staff.employees.create
     */
    public function create()
    {
        // فقط الوظائف اللي بدون Login
        $jobTitles = JobTitle::whereIn('name', $this->nonLoginJobTitles)
            ->orderBy('name')
            ->get();

        return view('staff.employees.create', compact('jobTitles'));
    }

    /**
     * POST /staff
     * Route name: staff.employees.store
     */
    public function store(Request $request)
    {
        $allowedIds = JobTitle::whereIn('name', $this->nonLoginJobTitles)->pluck('id')->toArray();

        $request->validate([
            'first_name'   => ['required','string','max:255'],
            'last_name'    => ['required','string','max:255'],
            'email'        => ['nullable','email','max:255'],
            'phone'        => ['nullable','string','max:255'],

            // ✅ خليها required حتى ما يضل فاضي
            'job_title_id' => ['required', Rule::in($allowedIds)],

            'salary_type'  => ['required', Rule::in(['fixed','hourly'])],
            'base_salary'  => ['required','numeric','min:0'],
            'join_date'    => ['nullable','date'],
            'status'       => ['required', Rule::in(['active','inactive'])],
        ], [
            'job_title_id.in' => 'Staff page is only for Driver/Cleaner. Accountants must be added from Accountants page.',
        ]);

        Staff::create([
            'first_name'   => $request->first_name,
            'last_name'    => $request->last_name,
            'email'        => $request->email,
            'phone'        => $request->phone,
            'salary_type'  => $request->salary_type,
            'base_salary'  => $request->base_salary,
            'join_date'    => $request->join_date,
            'status'       => $request->status,
            'session_id'   => $this->currentSessionId(),
            'job_title_id' => $request->job_title_id,
            'user_id'      => null, // ✅ بدون Login
        ]);

        return redirect()->route('staff.employees.index')
            ->with('success', 'Staff created successfully.');
    }

    /**
     * GET /staff/{employee}/edit
     * Route name: staff.employees.edit
     */
    public function edit(Staff $employee)
    {
        $jobTitles = JobTitle::whereIn('name', $this->nonLoginJobTitles)
            ->orderBy('name')
            ->get();

        return view('staff.employees.edit', [
            'employee'  => $employee,
            'jobTitles' => $jobTitles,
        ]);
    }

    /**
     * PUT /staff/{employee}
     * Route name: staff.employees.update
     */
    public function update(Request $request, Staff $employee)
    {
        $allowedIds = JobTitle::whereIn('name', $this->nonLoginJobTitles)->pluck('id')->toArray();

        $request->validate([
            'first_name'   => ['required','string','max:255'],
            'last_name'    => ['required','string','max:255'],
            'email'        => ['nullable','email','max:255'],
            'phone'        => ['nullable','string','max:255'],
            'job_title_id' => ['required', Rule::in($allowedIds)],

            'salary_type'  => ['required', Rule::in(['fixed','hourly'])],
            'base_salary'  => ['required','numeric','min:0'],
            'join_date'    => ['nullable','date'],
            'status'       => ['required', Rule::in(['active','inactive'])],
        ]);

        $employee->update([
            'first_name'   => $request->first_name,
            'last_name'    => $request->last_name,
            'email'        => $request->email,
            'phone'        => $request->phone,
            'job_title_id' => $request->job_title_id,
            'salary_type'  => $request->salary_type,
            'base_salary'  => $request->base_salary,
            'join_date'    => $request->join_date,
            'status'       => $request->status,
        ]);

        return redirect()->route('staff.employees.index')
            ->with('success', 'Staff updated successfully.');
    }

    /**
     * DELETE /staff/{employee}
     * Route name: staff.employees.destroy
     */
    public function destroy(Staff $employee)
    {
        $employee->delete();

        return redirect()->route('staff.employees.index')
            ->with('success', 'Staff deleted successfully.');
    }
}
