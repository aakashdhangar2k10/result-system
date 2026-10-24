<?php

namespace App\Http\Controllers;

use App\Services\StructureService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class StructureController extends Controller implements HasMiddleware
{
    public function __construct(private StructureService $structure)
    {
    }

    public static function middleware(): array
    {
        return [new Middleware('permission:structure.view')];
    }

    public function __invoke()
    {
        return view('structure.index', $this->structure->build());
    }
}