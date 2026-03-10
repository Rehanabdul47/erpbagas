<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Payroll;
use App\Traits\BranchScope;
use Carbon\Carbon;

class HrmDashboardController extends Controller
{
    use BranchScope;

    public function index()
    {
        $today = date('Y-m-d');
        $currentMonth = date('n');
        $currentYear = date('Y');

        $totalEmployeesQuery = Employee::query();
        $this->scopeByBranch($totalEmployeesQuery);
        $totalEmployees = $totalEmployeesQuery->count();

        $activeEmployeesQuery = Employee::where('status', 'active');
        $this->scopeByBranch($activeEmployeesQuery);
        $activeEmployees = $activeEmployeesQuery->count();

        $presentTodayQuery = Attendance::whereDate('date', $today)->where('status', 'present');
        $this->scopeByRelationBranch($presentTodayQuery, 'employee');
        $presentToday = $presentTodayQuery->count();
        
        $payrollQuery = Payroll::where('period_month', $currentMonth)
                               ->where('period_year', $currentYear);
        $this->scopeByRelationBranch($payrollQuery, 'employee');
        $payrollTotal = $payrollQuery->sum('net_salary');

        $recentAttendancesQuery = Attendance::with('employee')
                                       ->whereDate('date', $today)
                                       ->latest('updated_at');
        $this->scopeByRelationBranch($recentAttendancesQuery, 'employee');
        $recentAttendances = $recentAttendancesQuery->take(10)->get();

        $stats = [
            'totalEmployees' => $totalEmployees,
            'activeEmployees' => $activeEmployees,
            'presentToday' => $presentToday,
            'payrollTotal' => $payrollTotal,
        ];

        return view('pages.hrm-dashboard.index', compact('stats', 'recentAttendances'))
               ->with('activePage', 'hrm-dashboard');
    }
}
