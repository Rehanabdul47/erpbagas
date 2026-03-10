<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Payroll;
use App\Models\HrmSetting;
use App\Models\CashTransaction;
use App\Models\JournalEntry;
use App\Models\Account;
use App\Traits\BranchScope;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Events\AttendanceRecorded;

class HrmController extends Controller
{
    use BranchScope;
    public function index(Request $request)
    {
        $activeTab = $request->input('tab', 'employees');
        $branches = \App\Models\Branch::where('is_active', true)->get();
        
        // Date & Period handling
        $attendanceDate = $request->input('date', date('Y-m-d'));
        $payrollMonth = $request->input('month', date('Y-m'));
        $periodMonth = Carbon::parse($payrollMonth)->month;
        $periodYear = Carbon::parse($payrollMonth)->year;
        
        $employeesQuery = Employee::with(['branch', 'attendances'])->latest();
        $attendanceQuery = Attendance::with('employee')->whereDate('date', $attendanceDate);
        $leavesQuery = Attendance::with('employee')->whereIn('status', ['leave', 'sick'])->latest('date');
        $payrollQuery = Payroll::with('employee')->where('period_month', $periodMonth)->where('period_year', $periodYear);
        
        $statsRaw = [
            'totalEmployees' => Employee::query(),
            'activeEmployees' => Employee::where('status', 'active'),
            'presentToday' => Attendance::whereDate('date', date('Y-m-d'))->where('status', 'present'),
            'leavesToday' => Attendance::whereDate('date', date('Y-m-d'))->whereIn('status', ['leave', 'sick']),
            'payrollTotal' => (clone $payrollQuery)
        ];

        if (!auth()->user()->hasRole('Executive')) {
            $userBranchId = auth()->user()->branch_id;
            if ($userBranchId == 1) { // Pusat
                $employeesQuery->where(function($q) use ($userBranchId) { $q->where('branch_id', $userBranchId)->orWhereNull('branch_id'); });
                $attendanceQuery->whereHas('employee', function($q) use ($userBranchId) { $q->where('branch_id', $userBranchId)->orWhereNull('branch_id'); });
                $leavesQuery->whereHas('employee', function($q) use ($userBranchId) { $q->where('branch_id', $userBranchId)->orWhereNull('branch_id'); });
                $payrollQuery->whereHas('employee', function($q) use ($userBranchId) { $q->where('branch_id', $userBranchId)->orWhereNull('branch_id'); });
                foreach(['totalEmployees', 'activeEmployees'] as $key) {
                    $statsRaw[$key]->where(function($q) use ($userBranchId) { $q->where('branch_id', $userBranchId)->orWhereNull('branch_id'); });
                }
                foreach(['presentToday', 'leavesToday'] as $key) {
                    $statsRaw[$key]->whereHas('employee', function($q) use ($userBranchId) { $q->where('branch_id', $userBranchId)->orWhereNull('branch_id'); });
                }
            } else {
                $employeesQuery->where('branch_id', $userBranchId);
                $attendanceQuery->whereHas('employee', function($q) use ($userBranchId) { $q->where('branch_id', $userBranchId); });
                $leavesQuery->whereHas('employee', function($q) use ($userBranchId) { $q->where('branch_id', $userBranchId); });
                $payrollQuery->whereHas('employee', function($q) use ($userBranchId) { $q->where('branch_id', $userBranchId); });
                $statsRaw['totalEmployees']->where('branch_id', $userBranchId);
                $statsRaw['activeEmployees']->where('branch_id', $userBranchId);
                $statsRaw['presentToday']->whereHas('employee', function($q) use ($userBranchId) { $q->where('branch_id', $userBranchId); });
                $statsRaw['leavesToday']->whereHas('employee', function($q) use ($userBranchId) { $q->where('branch_id', $userBranchId); });
            }
        }

        $employees = $employeesQuery->get();
        $attendances = $attendanceQuery->get();
        $leaves = $leavesQuery->get();
        $payrolls = $payrollQuery->get();

        $stats = [
            'totalEmployees' => $statsRaw['totalEmployees']->count(),
            'activeEmployees' => $statsRaw['activeEmployees']->count(),
            'presentToday' => $statsRaw['presentToday']->count(),
            'payrollTotal' => $statsRaw['payrollTotal']->sum('net_salary'),
            'leavesToday' => $statsRaw['leavesToday']->count(),
        ];

        $settings = HrmSetting::pluck('value', 'key')->all();
        if (!isset($settings['late_deduction'])) $settings['late_deduction'] = 10000;
        if (!isset($settings['office_in'])) $settings['office_in'] = '08:00:00';
        if (!isset($settings['overtime_per_hour'])) $settings['overtime_per_hour'] = 15000;
 
        return view('pages.hrm.index', compact(
            'employees', 
            'branches',
            'attendances', 
            'payrolls', 
            'leaves',
            'stats', 
            'activeTab', 
            'attendanceDate', 
            'payrollMonth',
            'settings'
        ))->with('activePage', 'hrm');
    }
 
    public function storeEmployee(Request $request)
    {
        $validated = $request->validate([
            'nip' => 'required|string|unique:employees,nip',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email',
            'phone' => 'required|string',
            'position' => 'required|string',
            'department' => 'required|string',
            'branch_id' => 'nullable|exists:branches,id',
            'join_date' => 'required|date',
            'basic_salary' => 'required|numeric|min:0',
            'allowance' => 'nullable|numeric|min:0',
            'deduction' => 'nullable|numeric|min:0',
        ]);
 
        Employee::create($validated);
 
        return redirect()->route('hrm.index', ['tab' => 'employees'])->with('success', 'Karyawan berhasil ditambahkan');
    }
 
    public function updateEmployee(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'nip' => 'required|string|unique:employees,nip,' . $employee->id,
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,' . $employee->id,
            'phone' => 'required|string',
            'position' => 'required|string',
            'department' => 'required|string',
            'branch_id' => 'nullable|exists:branches,id',
            'join_date' => 'required|date',
            'basic_salary' => 'required|numeric|min:0',
            'allowance' => 'nullable|numeric|min:0',
            'deduction' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);
 
        $employee->update($validated);
 
        return redirect()->route('hrm.index', ['tab' => 'employees'])->with('success', 'Data karyawan berhasil diperbarui');
    }

    public function destroyEmployee(Employee $employee)
    {
        $employee->delete();
        return redirect()->route('hrm.index', ['tab' => 'employees'])->with('success', 'Karyawan berhasil dihapus');
    }

    public function storeAttendance(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,leave,sick',
            'clock_in' => 'nullable',
            'clock_out' => 'nullable',
            'note' => 'nullable|string',
        ]);

        if ($validated['status'] === 'present' && !empty($validated['clock_in'])) {
            $officeIn = HrmSetting::where('key', 'office_in')->value('value') ?? '08:00:00';
            $validated['is_late'] = $validated['clock_in'] > $officeIn;
        }

        $attendance = Attendance::updateOrCreate(
            ['employee_id' => $validated['employee_id'], 'date' => $validated['date']],
            $validated
        );

        event(new AttendanceRecorded($attendance));

        return back()->with('success', 'Absensi berhasil dicatat');
    }

    public function generatePayroll(Request $request)
    {
        $request->validate(['month' => 'required']);
        $date = Carbon::parse($request->month);
        $month = $date->month;
        $year = $date->year;

        $employeesQuery = Employee::where('status', 'active');
        $this->scopeByBranch($employeesQuery);
        $employees = $employeesQuery->get();

        foreach ($employees as $emp) {
            // Check if payroll already exists
            $exists = Payroll::where('employee_id', $emp->id)
                ->where('period_month', $month)
                ->where('period_year', $year)
                ->exists();

            if (!$exists) {
                // Calculate Lateness Deductions for this month
                $lateCount = Attendance::where('employee_id', $emp->id)
                    ->whereMonth('date', $month)
                    ->whereYear('date', $year)
                    ->where('is_late', true)
                    ->count();
                
                $lateDeductionAmount = HrmSetting::where('key', 'late_deduction')->value('value') ?? 10000;
                $lateDeduction = $lateCount * $lateDeductionAmount;
                $totalDeduction = ($emp->deduction ?? 0) + $lateDeduction;

                Payroll::create([
                    'employee_id' => $emp->id,
                    'period_month' => $month,
                    'period_year' => $year,
                    'basic_salary' => $emp->basic_salary,
                    'allowance' => $emp->allowance ?? 0,
                    'deduction' => $totalDeduction,
                    'net_salary' => $emp->basic_salary + ($emp->allowance ?? 0) - $totalDeduction,
                    'status' => 'draft',
                ]);
            }
        }

        return redirect()->route('hrm.index', ['tab' => 'payroll', 'month' => $request->month])
            ->with('success', 'Payroll berhasil digenerate untuk periode ' . $date->format('F Y'));
    }

    public function updatePayroll(Request $request, Payroll $payroll)
    {
        $validated = $request->validate([
            'allowance' => 'required|numeric|min:0',
            'deduction' => 'required|numeric|min:0',
            'status' => 'required|in:draft,paid',
        ]);

        $validated['net_salary'] = $payroll->basic_salary + $validated['allowance'] - $validated['deduction'];
        
        $statusChangedToPaid = ($validated['status'] === 'paid' && $payroll->status !== 'paid');

        if ($statusChangedToPaid) {
            $validated['paid_at'] = now();
        }

        $payroll->update($validated);

        if ($statusChangedToPaid) {
            $payroll->load('employee');
            
            // 1. Record Cash Out
            CashTransaction::create([
                'date' => Carbon::now(),
                'type' => 'out',
                'category' => 'Payroll',
                'amount' => $payroll->net_salary,
                'description' => "Pembayaran Gaji Karyawan {$payroll->employee->name} Periode " . Carbon::createFromDate($payroll->period_year, $payroll->period_month)->format('M Y'),
                'reference' => 'PAYROLL-' . $payroll->id,
                'user_id' => auth()->id() ?? 1,
            ]);

            // 2. Journal Entry
            $gajiAccount = Account::where('code', '5-102')->first();
            $kasAccount = Account::where('code', '1-101')->first();

            if ($gajiAccount && $kasAccount) {
                $journalEntry = JournalEntry::create([
                    'date' => Carbon::now(),
                    'reference' => 'PAYROLL-' . $payroll->id,
                    'description' => "Pembayaran Gaji Karyawan {$payroll->employee->name}",
                ]);

                // Debit: Biaya Gaji (6001), Credit: Kas (1001)
                $journalEntry->items()->createMany([
                    [
                        'account_id' => $gajiAccount->id,
                        'debit' => $payroll->net_salary,
                        'credit' => 0,
                    ],
                    [
                        'account_id' => $kasAccount->id,
                        'debit' => 0,
                        'credit' => $payroll->net_salary,
                    ]
                ]);
            }
        }

        return back()->with('success', 'Payroll berhasil diperbarui');
    }

    public function updateSettings(Request $request)
    {
        $settings = $request->only(['late_deduction', 'office_in', 'overtime_per_hour']);
        
        foreach ($settings as $key => $value) {
            HrmSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'Pengaturan HRM berhasil diperbarui');
    }

    public function storeLeave(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:leave,sick',
            'note' => 'required|string|max:255',
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $days = $startDate->diffInDays($endDate);

        for ($i = 0; $i <= $days; $i++) {
            $currentDate = $startDate->copy()->addDays($i)->format('Y-m-d');
            
            Attendance::updateOrCreate(
                ['employee_id' => $validated['employee_id'], 'date' => $currentDate],
                [
                    'status' => $validated['status'],
                    'note' => $validated['note'],
                ]
            );
        }

        return redirect()->route('hrm.index', ['tab' => 'leaves'])->with('success', 'Cuti/Izin berhasil diajukan');
    }

    public function kiosk()
    {
        return view('pages.hrm.kiosk');
    }

    public function kioskScan(Request $request)
    {
        $request->validate([
            'nip' => 'required|string',
            'image' => 'nullable|string', // Base64 image
        ]);

        $employee = Employee::where('nip', $request->nip)->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan dengan NIP ' . $request->nip . ' tidak ditemukan.'
            ], 404);
        }

        $today = now()->format('Y-m-d');
        $now = now()->format('H:i:s');

        // Handle Image
        $imageName = null;
        if ($request->image) {
            $imageData = $request->image;
            if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
                $type = strtolower($type[1]); // png, jpg, jpeg
            }
            $imageData = str_replace(' ', '+', $imageData);
            $imageName = $employee->nip . '_' . time() . '.' . ($type ?? 'png');
            \Storage::disk('public')->put('attendances/' . $imageName, base64_decode($imageData));
        }

        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', $today)
            ->first();

        if (!$attendance) {
            // Clock In
            $officeIn = HrmSetting::where('key', 'office_in')->value('value') ?? '08:00:00';
            $is_late = $now > $officeIn;
            $newAttendance = Attendance::create([
                'employee_id' => $employee->id,
                'date' => $today,
                'status' => 'present',
                'clock_in' => $now,
                'is_late' => $is_late,
                'image' => $imageName,
            ]);

            event(new AttendanceRecorded($newAttendance));

            return response()->json([
                'success' => true,
                'type' => 'in',
                'message' => 'Berhasil Absen Masuk: ' . $employee->name,
                'time' => $now
            ]);
        } elseif (!$attendance->clock_out) {
            // Clock Out
            $attendance->update([
                'clock_out' => $now,
                'image_out' => $imageName,
            ]);

            event(new AttendanceRecorded($attendance));

            return response()->json([
                'success' => true,
                'type' => 'out',
                'message' => 'Berhasil Absen Keluar: ' . $employee->name,
                'time' => $now
            ]);
        } else {
            // Already clocked out
            return response()->json([
                'success' => false,
                'message' => $employee->name . ' sudah melakukan absen keluar hari ini.'
            ], 400);
        }
    }
}
