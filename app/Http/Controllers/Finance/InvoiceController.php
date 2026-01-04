<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\SchoolSession;
use App\Models\Payment;
use App\Models\Promotion;
use App\Models\Section;
use App\Models\StudentParentInfo;

use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use PDF;

class InvoiceController extends Controller
{


    // ✅ CREATE PAGE

    // ✅ STORE
    public function store(Request $request)
    {
        $fallbackSessionId = session()->has('browse_session_id')
            ? session('browse_session_id')
            : SchoolSession::latest()->value('id');

        // ✅ IMPORTANT: class + section required (عشان ما نعرض كل الطلاب)
        $validated = $request->validate([
            'session_id'  => 'required|exists:school_sessions,id',
            'class_id'    => 'required|exists:school_classes,id',
            'section_id'  => 'required|exists:sections,id', // فقط للفلترة (مش لازم ينخزن)
            'student_id'  => 'required|exists:users,id',
            'title'       => 'required|string|max:255',
            'amount'      => 'required|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'due_date'    => 'nullable|date',
            'status'      => 'required|in:paid,partial,unpaid',
            'notes'       => 'nullable|string',
        ]);

        if (empty($validated['session_id'])) {
            $validated['session_id'] = $fallbackSessionId;
        }

        // ✅ تأكيد أن الطالب فعلاً ضمن هذا class/section/session
        $ok = Promotion::where('session_id', $validated['session_id'])
            ->where('class_id', $validated['class_id'])
            ->where('section_id', $validated['section_id'])
            ->where('student_id', $validated['student_id'])
            ->exists();

        if (!$ok) {
            return back()->withErrors(['student_id' => 'Student is not in the selected Class/Section for this session.'])->withInput();
        }

        // ✅ لا نخزن section_id داخل invoice (إلا إذا عندك عمود بالجدول)
        $data = $validated;
        unset($data['section_id']);

        $data['paid_amount'] = isset($data['paid_amount']) ? (float)$data['paid_amount'] : 0.0;

        $amount = (float)$data['amount'];
        $paid   = (float)$data['paid_amount'];

        if ($amount <= 0) {
            $data['status'] = 'paid';
        } else {
            if ($paid >= $amount) {
                $data['paid_amount'] = $amount;
                $data['status'] = 'paid';
            } elseif ($paid > 0) {
                $data['status'] = 'partial';
            } else {
                $data['status'] = 'unpaid';
            }
        }

        if (!$request->filled('invoice_number')) {
            $data['invoice_number'] = 'INV-' . str_pad((string)(Invoice::max('id') + 1), 6, '0', STR_PAD_LEFT);
        }

        $invoice = Invoice::create($data);

        // ✅ لو paid_amount > 0 نسجل Payment (حسب أعمدتك الجديدة)
        if ($paid > 0) {
            try {
                Payment::create([
                    'invoice_id'  => $invoice->id,
                    'amount'      => $paid,
                    'method'      => 'cash',
                    'reference'   => 'PMT-' . strtoupper(Str::random(8)),
                    'paid_at'     => now(),
                    'received_by' => auth()->id(),
                    'notes'       => 'Payment on invoice creation',
                ]);
            } catch (\Throwable $e) {
                // ignore
            }
        }

        return redirect()->route('finance.invoices.index')->with('status', 'Invoice created successfully ✅');
    }



    // ✅ EDIT PAGE
    public function edit(Invoice $invoice)
    {
        $sessionId = $invoice->session_id ?: (session()->has('browse_session_id')
            ? session('browse_session_id')
            : SchoolSession::latest()->value('id'));

        // ✅ نجيب class/section من promotion للطالب
        $promo = Promotion::where('session_id', $sessionId)
            ->where('student_id', $invoice->student_id)
            ->first();

        $prefillClassId = $promo ? $promo->class_id : $invoice->class_id;
        $prefillSectionId = $promo ? $promo->section_id : null;

        // ✅ الطلاب: ما نجيبهم كلهم، بس نخلي الطالب الحالي يظهر
        $students = collect();
        if ($invoice->student) {
            $students = collect([$invoice->student]);
        }

        // ✅ Classes
        try {
            $classes = SchoolClass::orderBy('name')->get();
        } catch (QueryException $e) {
            try {
                $classes = SchoolClass::orderBy('class_name')->get();
            } catch (QueryException $e2) {
                $classes = SchoolClass::orderBy('id')->get();
            }
        }

        // ✅ Sections
        try {
            $sections = Section::orderBy('name')->get();
        } catch (QueryException $e) {
            try {
                $sections = Section::orderBy('section_name')->get();
            } catch (QueryException $e2) {
                $sections = Section::orderBy('id')->get();
            }
        }

        return view('finance.invoices.edit', compact(
            'invoice', 'students', 'classes', 'sections', 'sessionId', 'prefillClassId', 'prefillSectionId'
        ));
    }

    // ✅ UPDATE
    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'session_id'  => 'required|exists:school_sessions,id',
            'class_id'    => 'required|exists:school_classes,id',
            'section_id'  => 'required|exists:sections,id',
            'student_id'  => 'required|exists:users,id',
            'title'       => 'required|string|max:255',
            'amount'      => 'required|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'due_date'    => 'nullable|date',
            'status'      => 'required|in:paid,partial,unpaid',
            'notes'       => 'nullable|string',
        ]);

        // ✅ تأكيد الطالب ضمن الصف/الشعبة
        $ok = Promotion::where('session_id', $validated['session_id'])
            ->where('class_id', $validated['class_id'])
            ->where('section_id', $validated['section_id'])
            ->where('student_id', $validated['student_id'])
            ->exists();

        if (!$ok) {
            return back()->withErrors(['student_id' => 'Student is not in the selected Class/Section for this session.'])->withInput();
        }

        $data = $validated;
        unset($data['section_id']);

        $data['paid_amount'] = isset($data['paid_amount']) ? (float)$data['paid_amount'] : 0.0;

        $amount = (float)$data['amount'];
        $paid   = (float)$data['paid_amount'];

        if ($amount <= 0) {
            $data['status'] = 'paid';
        } else {
            if ($paid >= $amount) {
                $data['paid_amount'] = $amount;
                $data['status'] = 'paid';
            } elseif ($paid > 0) {
                $data['status'] = 'partial';
            } else {
                $data['status'] = 'unpaid';
            }
        }

        $invoice->update($data);

        return redirect()->route('finance.invoices.index')->with('status', 'Invoice updated ✅');
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return redirect()->route('finance.invoices.index')->with('status', 'Invoice deleted ✅');
    }

    // ✅ AJAX: Students by Class/Section/Session
    public function studentsByClassSection(Request $request)
    {
        $sessionId = $request->get('session_id') ?: (session()->has('browse_session_id')
            ? session('browse_session_id')
            : SchoolSession::latest()->value('id'));

        $classId   = $request->get('class_id');
        $sectionId = $request->get('section_id');

        if (!$sessionId || !$classId || !$sectionId) {
            return response()->json([]);
        }

        $studentIds = Promotion::where('session_id', $sessionId)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->pluck('student_id')
            ->toArray();

        if (empty($studentIds)) {
            return response()->json([]);
        }

        $students = User::whereIn('id', $studentIds)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(function ($s) {
                $name = trim(($s->first_name ?? '') . ' ' . ($s->last_name ?? ''));
                if ($name === '') {
                    $name = $s->name ?? ('Student #' . $s->id);
                }
                return [
                    'id'   => $s->id,
                    'text' => $name,
                ];
            });

        return response()->json($students);
    }

    public function quickPayment(Request $request, Invoice $invoice)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $payAmount = (float)$request->amount;
        $invoice->paid_amount = (float)$invoice->paid_amount + $payAmount;

        if ($invoice->paid_amount >= (float)$invoice->amount) {
            $invoice->paid_amount = (float)$invoice->amount;
            $invoice->status = 'paid';
        } else {
            $invoice->status = 'partial';
        }

        $invoice->save();

        try {
            Payment::create([
                'invoice_id'  => $invoice->id,
                'amount'      => $payAmount,
                'method'      => 'cash',
                'reference'   => 'PMT-' . strtoupper(Str::random(8)),
                'paid_at'     => now(),
                'received_by' => auth()->id(),
                'notes'       => 'Quick payment',
            ]);
        } catch (\Throwable $e) {
            // ignore
        }

        return redirect()->back()->with('status', 'Payment recorded and balance updated! ✅');
    }


    public function collectionSummary()
    {
        $totalInvoiced = Invoice::sum('amount');
        $totalPaid = Invoice::sum('paid_amount');
        $totalDue = $totalInvoiced - $totalPaid;

        return view('finance.reports.summary', compact('totalInvoiced', 'totalPaid', 'totalDue'));
    }

    private function getSessionIdFromRequest($request)
    {
        // browse_session_id لو موجودة، وإلا آخر Session
        return $request->get('session_id')
            ?: (session('browse_session_id') ?: \App\Models\SchoolSession::latest()->value('id'));
    }

    private function classLabel($class)
    {
        return $class->name ?? $class->class_name ?? $class->title ?? ('Class #' . $class->id);
    }

    public function create(Request $request)
    {
        $sessionId = $this->getSessionIdFromRequest($request);

        // ✅ بدون orderBy(name) عشان ما يطلع خطأ name column
        $classes = \App\Models\SchoolClass::query()
            ->when($sessionId, function ($q) use ($sessionId) {
                return $q->where('session_id', $sessionId);
            })
            ->get();

        // ✅ ترتيب Grade 1..12 (ترتيب رقمي)
        $classes = $classes->sortBy(function ($c) {
            $label = $this->classLabel($c);
            if (preg_match('/(\d+)/', $label, $m)) return (int) $m[1];
            return 9999;
        })->values();

        $invoice  = new \App\Models\Invoice();
        $sections = collect(); // سيتم تعبئتها عبر AJAX
        $students = collect(); // سيتم تعبئتها عبر AJAX

        return view('finance.invoices.create', compact('invoice', 'classes', 'sections', 'students', 'sessionId'));
    }

    /**
     * ✅ AJAX: يرجّع Sections الخاصة بالـ Class المختار (حسب promotions)
     */
    public function ajaxSections(Request $request)
    {
        $sessionId = $this->getSessionIdFromRequest($request);
        $classId   = (int) $request->get('class_id');

        if (!$classId || !$sessionId) {
            return response()->json([]);
        }

        $sectionIds = Promotion::query()
            ->where('session_id', $sessionId)
            ->where('class_id', $classId)
            ->whereNotNull('section_id')
            ->distinct()
            ->pluck('section_id')
            ->toArray();

        if (empty($sectionIds)) {
            return response()->json([]);
        }

        $sections = Section::query()
            ->whereIn('id', $sectionIds)
            ->get()
            ->map(function ($s) {
                return [
                    'id'   => $s->id,
                    'name' => $s->name ?? $s->section_name ?? $s->title ?? ('Section #' . $s->id),
                ];
            })
            ->sortBy('name')
            ->values();

        return response()->json($sections);
    }

    /**
     * ✅ AJAX: يرجّع Students الخاصة بالـ Class + Section المختارة (حسب promotions)
     */
    public function ajaxStudents(Request $request)
    {
        $sessionId = $this->getSessionIdFromRequest($request);
        $classId   = (int) $request->get('class_id');
        $sectionId = (int) $request->get('section_id');

        if (!$classId || !$sectionId || !$sessionId) {
            return response()->json([]);
        }

        $studentIds = Promotion::query()
            ->where('session_id', $sessionId)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->distinct()
            ->pluck('student_id')
            ->toArray();

        if (empty($studentIds)) {
            return response()->json([]);
        }

        $students = \App\Models\User::query()
            ->whereIn('id', $studentIds)
            ->get()
            ->map(function ($u) {
                return [
                    'id'   => $u->id,
                    'name' => trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) ?: ($u->name ?? ('Student #' . $u->id)),
                ];
            })
            ->sortBy('name')
            ->values();

        return response()->json($students);
    }



    private function isAdminOrAccountant($user): bool
    {
        return $this->hasRoleSafe($user, 'admin') || $this->hasRoleSafe($user, 'accountant');
    }

    private function parentChildrenIds($user): array
    {
        if (!$user) return [];

        return StudentParentInfo::where('parent_user_id', $user->id)
            ->pluck('student_id')
            ->toArray();
    }

    private function teacherClassIds($user): array
    {
        if (!$user) return [];

        // إذا عندك علاقة teacherCourses (زي اللي استخدمتها في UserController)
        if (method_exists($user, 'teacherCourses')) {
            return $user->teacherCourses()->pluck('class_id')->unique()->toArray();
        }

        return [];
    }

    private function canViewInvoice($user, Invoice $invoice): bool
    {
        if (!$user) return false;

        // Admin/Accountant يشوفوا الكل
        if ($this->isAdminOrAccountant($user)) return true;

        // Student يشوف بس فواتيره
        if ($this->hasRoleSafe($user, 'student')) {
            return (int)$invoice->student_id === (int)$user->id;
        }

        // Parent يشوف فواتير أولاده
        if ($this->hasRoleSafe($user, 'parent')) {
            $childrenIds = $this->parentChildrenIds($user);
            return in_array((int)$invoice->student_id, array_map('intval', $childrenIds), true);
        }

        // Teacher يشوف فواتير طلاب صفوفه (حسب class_id)
        if ($this->hasRoleSafe($user, 'teacher')) {
            $classIds = $this->teacherClassIds($user);
            return $invoice->class_id && in_array((int)$invoice->class_id, array_map('intval', $classIds), true);
        }

        return false;
    }

    // =========================
    // Index (قائمة الفواتير)
    // =========================
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Invoice::with(['student', 'class']);

        // ✅ فلترة حسب الدور
        if (!$this->isAdminOrAccountant($user)) {

            if ($this->hasRoleSafe($user, 'student')) {
                $query->where('student_id', $user->id);
            } elseif ($this->hasRoleSafe($user, 'parent')) {
                $childrenIds = $this->parentChildrenIds($user);

                // لو ما عنده أبناء -> ما يطلع شيء
                if (empty($childrenIds)) {
                    $query->whereRaw('1=0');
                } else {
                    $query->whereIn('student_id', $childrenIds);
                }
            } elseif ($this->hasRoleSafe($user, 'teacher')) {
                $classIds = $this->teacherClassIds($user);

                if (empty($classIds)) {
                    $query->whereRaw('1=0');
                } else {
                    $query->whereIn('class_id', $classIds);
                }
            } else {
                // أي دور ثاني ما يشوف شيء
                $query->whereRaw('1=0');
            }
        }

        // ✅ Filters الموجودة عندك
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                    ->orWhere('last_name', 'like', '%' . $request->search . '%');
            });
        }

        $invoices = $query->latest()->paginate(15);

        return view('finance.invoices.index', compact('invoices'));
    }

    // =========================
    // Show (لو عندك صفحة show)
    // =========================
    public function show(Invoice $invoice)
    {
        $user = auth()->user();

        abort_unless($this->canViewInvoice($user, $invoice), 403);

        $invoice->load(['student', 'class', 'payments']);

        return view('finance.invoices.show', compact('invoice'));
    }

    // =========================
    // Print PDF
    // =========================
    public function printInvoice(Invoice $invoice)
    {
        $user = auth()->user();

        // ✅ حماية مهمة: الطالب/الأهل/المعلم ما يقدر يطبع غير فواتيره
        abort_unless($this->canViewInvoice($user, $invoice), 403);

        $invoice->load(['student', 'class', 'payments']);

        $data = [
            'invoice' => $invoice,
            'title' => 'Official Invoice #' . ($invoice->invoice_number ?? $invoice->id),
            'date' => now()->format('d/m/Y'),
        ];

        $pdf = PDF::loadView('finance.reports.invoice_pdf', $data);

        return $pdf->stream('Invoice_' . $invoice->id . '.pdf');
    }

    private function hasRoleSafe($user, string $role): bool
    {
        if (!$user) return false;

        $role = strtolower($role);

        // ✅ 1) role column fallback (حتى لو Spatie موجود)
        if (isset($user->role) && strtolower((string)$user->role) === $role) {
            return true;
        }

        // ✅ 2) Spatie roles (لو موجود)
        if (method_exists($user, 'hasRole')) {
            try {
                if ($user->hasRole($role) || $user->hasRole(ucfirst($role)) || $user->hasRole(strtoupper($role))) {
                    return true;
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        // ✅ 3) isAdmin helper لو موجود
        if ($role === 'admin' && method_exists($user, 'isAdmin')) {
            try {
                return (bool) $user->isAdmin();
            } catch (\Throwable $e) {
                // ignore
            }
        }

        return false;
    }

}
