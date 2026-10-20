@php
    $isSystem = $role->exists && in_array($role->name, config('access.system_roles'), true);
@endphp

<div class="row mb-3">
    <label class="col-sm-2 col-form-label fw-bold text-sm-end">Role Name</label>
    <div class="col-sm-6">
        <input type="text" name="name" value="{{ old('name', $role->name) }}"
               class="form-control @error('name') is-invalid @enderror" placeholder="exam-cell"
               @readonly($isSystem) required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        <div class="form-text">
            @if ($isSystem) The name of a system role cannot be changed. @else Small letters, numbers, - and _ only. @endif
        </div>
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-2 col-form-label fw-bold text-sm-end">Access</label>
    <div class="col-sm-10">
        @include('administration._matrix')
        @error('permissions') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    </div>
</div>