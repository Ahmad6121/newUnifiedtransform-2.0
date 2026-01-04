<?php

namespace App\Http\Controllers;

use App\Models\JobTitle;
use Illuminate\Http\Request;

class JobTitleController extends Controller
{
    public function index()
    {
        $titles = JobTitle::orderBy('name')->paginate(20);
        return view('staff.job_titles.index', compact('titles'));
    }

    public function create()
    {
        return view('staff.job_titles.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:job_titles,name',
        ]);

        JobTitle::create($data);
        return redirect()->route('staff.job-titles.index')->with('success', 'Job title created.');
    }

    public function edit(JobTitle $job_title)
    {
        return view('staff.job_titles.edit', ['title' => $job_title]);
    }

    public function update(Request $request, JobTitle $job_title)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:job_titles,name,' . $job_title->id,
        ]);

        $job_title->update($data);
        return redirect()->route('staff.job-titles.index')->with('success', 'Job title updated.');
    }

    public function destroy(JobTitle $job_title)
    {
        $job_title->delete();
        return back()->with('success', 'Job title deleted.');
    }
}
