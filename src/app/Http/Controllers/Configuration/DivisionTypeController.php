<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Http\Requests\Configuration\DivisionTypeRequest;
use App\Models\Configuration\DivisionType;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Support\DeleteGuard;
class DivisionTypeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:divisiontypes.view', only: ['index']),
            new Middleware('permission:divisiontypes.create', only: ['create', 'store']),
            new Middleware('permission:divisiontypes.edit', only: ['edit', 'update']),
            new Middleware('permission:divisiontypes.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $divisionTypes = DivisionType::query()
            ->when($request->q, fn ($query, $q) => $query->where('divtypename', 'like', "%{$q}%"))
            ->orderByDesc('divtypeid')
            ->paginate(10)
            ->withQueryString();

        return view('configuration.division_types.index', compact('divisionTypes'));
    }

    public function create()
    {
        return view('configuration.division_types.create', ['divisionType' => new DivisionType()]);
    }

    public function store(DivisionTypeRequest $request)
    {
        $data = $request->validated();
        $data['active'] = $request->boolean('active');

        DivisionType::create($data);

        return redirect()->route('division-types.index')->with('success', 'Division type created.');
    }

    public function edit(DivisionType $divisionType)
    {
        return view('configuration.division_types.edit', compact('divisionType'));
    }

    public function update(DivisionTypeRequest $request, DivisionType $divisionType)
    {
        $data = $request->validated();
        $data['active'] = $request->boolean('active');

        $divisionType->update($data);

        return redirect()->route('division-types.index')->with('success', 'Division type updated.');
    }

    public function destroy(DivisionType $divisionType)
    {
        if ($blocked = DeleteGuard::blocker($divisionType)) {
        return back()->with('error', $blocked);
        }
        $divisionType->update(['is_delete' => true]);

        return redirect()->route('division-types.index')->with('success', 'Division type deleted.');
    }
}