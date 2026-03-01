<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Payroll;
use Carbon\Carbon;

class HrmDashboardController extends Controller
{
    public function index()
    {
        $today = date('Y-m-d');
        $currentMonth = date('n');
        $currentYear = date('Y');

        $totalEmployees = Employee::count();
        $activeEmployees = Employee::where('status', 'active')->count();
        $presentToday = Attendance::whereDate('date', $today)->where('status', 'present')->count();
        
        $payrollTotal = Payroll::where('period_month', $currentMonth)
                               ->where('period_year', $currentYear)
                               ->sum('net_salary');

        $recentAttendances = Attendance::with('employee')
                                       ->whereDate('date', $today)
                                       ->latest('updated_at')
                                       ->take(10)
                                       ->get();

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
