<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Staff;
use App\Models\PayrollRecord;
use App\Models\TimeClockEntry;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    /**
     * Display the employee management index page
     *
     * @return \Illuminate\View\View
     */
    public function employeeIndex()
    {
        return view('payroll.employees.index');
    }

    /**
     * Display the employee creation form
     *
     * @return \Illuminate\View\View
     */
    public function employeeCreate()
    {
        return view('payroll.employees.create');
    }

    /**
     * Display the employee edit form
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function employeeEdit($id)
    {
        $employee = Employee::with('staff')->findOrFail($id);
        return view('payroll.employees.edit', compact('employee'));
    }

    /**
     * Display the time clock management page
     *
     * @return \Illuminate\View\View
     */
    public function timeClockIndex()
    {
        return view('payroll.time-clock.index');
    }

    /**
     * Display the time clock entry form
     *
     * @return \Illuminate\View\View
     */
    public function timeClockEntry()
    {
        return view('payroll.time-clock.entry');
    }

    /**
     * Display the payroll records index page
     */
    public function payrollIndex()
    {
        return view('payroll.records.index');
    }

    /**
     * Display the payroll generation form
     */
    public function payrollGenerate()
    {
        return view('payroll.records.generate');
    }

    /**
     * Display the payroll record details page
     */
    public function payrollShow($id)
    {
        $payrollRecord = PayrollRecord::with(['employee', 'employee.staff'])->findOrFail($id);
        return view('payroll.records.show', compact('payrollRecord'));
    }

    /**
     * Display the payroll reports page
     */
    public function payrollReports()
    {
        return view('payroll.reports.index');
    }
}
