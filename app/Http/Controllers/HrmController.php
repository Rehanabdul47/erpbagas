<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Payroll;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HrmController extends Controller
{
    public function index(Request $request)
    {
        $activeTab = $request->input('tab', 'employees');
        
        $employees = Employee::withCount('attendances')->latest()->get();
        
        // Attendance logic
        $attendanceDate = $request->input('date', date('Y-m-d'));
        $attendances = Attendance::with('employee')
            ->whereDate('date', $attendanceDate)
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
        ];

        return view('pages.hrm.index', compact(
            'employees', 
            'attendances', 
            'payrolls', 
            'stats', 
            'activeTab', 
            'attendanceDate', 
            'payrollMonth'
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
            'join_date' => 'required|date',
            'basic_salary' => 'required|numeric|min:0',
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
            'join_date' => 'required|date',
            'basic_salary' => 'required|numeric|min:0',
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

        Attendance::updateOrCreate(
            ['employee_id' => $validated['employee_id'], 'date' => $validated['date']],
            $validated
        );

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
                Payroll::create([
                    'employee_id' => $emp->id,
                    'period_month' => $month,
                    'period_year' => $year,
                    'basic_salary' => $emp->basic_salary,
                    'allowance' => 0,
                    'deduction' => 0,
                    'net_salary' => $emp->basic_salary,
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
}
