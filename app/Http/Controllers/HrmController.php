<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Payroll;
use App\Models\HrmSetting;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Events\AttendanceRecorded;

class HrmController extends Controller
{
    public function index(Request $request)
    {
        $activeTab = $request->input('tab', 'employees');
        
        $employees = Employee::with(['branch', 'attendances'])->latest()->get();
        $branches = \App\Models\Branch::where('is_active', true)->get();
        
        // Attendance logic
        $attendanceDate = $request->input('date', date('Y-m-d'));
        $attendances = Attendance::with('employee')
            ->whereDate('date', $attendanceDate)
            ->get();
            
        // Leave / Sick logic
        $leaves = Attendance::with('employee')
            ->whereIn('status', ['leave', 'sick'])
            ->latest('date')
            ->get();
            
        // Payroll logic
        $payrollMonth = $request->input('month', date('Y-m'));
        $periodMonth = Carbon::parse($payrollMonth)->month;
        $periodYear = Carbon::parse($payrollMonth)->year;
        
        $payrolls = Payroll::with('employee')
            ->where('period_month', $periodMonth)
            ->where('period_year', $periodYear)
            ->get();
 
        $stats = [
            'totalEmployees' => $employees->count(),
            'activeEmployees' => $employees->where('status', 'active')->count(),
            'presentToday' => Attendance::whereDate('date', date('Y-m-d'))->where('status', 'present')->count(),
            'payrollTotal' => $payrolls->sum('net_salary'),
            'leavesToday' => Attendance::whereDate('date', date('Y-m-d'))->whereIn('status', ['leave', 'sick'])->count(),
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

        $employees = Employee::where('status', 'active')->get();

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
        
        if ($validated['status'] === 'paid' && $payroll->status !== 'paid') {
            $validated['paid_at'] = now();
        }

        $payroll->update($validated);

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
