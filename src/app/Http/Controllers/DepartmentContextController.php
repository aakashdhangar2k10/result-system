<?php

namespace App\Http\Controllers;

use App\Support\DepartmentAccess;
use Illuminate\Http\Request;

class DepartmentContextController extends Controller
{
    public function update(Request $request)
    {
        $id = $request->input('deptid');

        if ($id === null || $id === '') {
            $request->session()->forget('dept_context');
        } else {
            abort_unless(
                in_array((int) $id, DepartmentAccess::permittedIds(), true),
                403,
                'You do not have access to this department.'
            );

            $request->session()->put('dept_context', (int) $id);
        }

        DepartmentAccess::flush();

        $previous = url()->previous();
        $path = (string) parse_url($previous, PHP_URL_PATH);

        // an open record may not exist in the new department, and old filters may point to another department's data
        return preg_match('#/\d+(/|$)#', $path)
            ? redirect()->route('dashboard.index')
            : redirect(strtok($previous, '?'));
    }
}
