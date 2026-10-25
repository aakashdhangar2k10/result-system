@php
$selectedRoles = array_map('intval', old('roles', $user->exists ? $user->roles->pluck('id')->all() : []));
$selectedGroups = array_map('intval', old('groups', $user->exists ? $user->groups->pluck('groupid')->all() : []));
$selectedDepartments = array_map('intval', old('departments', $user->exists ? $user->departments->pluck('deptid')->all() : []));
$mode = old('department_access', $user->all_departments ? 'all' : 'selected');
@endphp

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Name</label>
    <div class="col-sm-9">
        <input type="text" name="name" value="{{ old('name', $user->name) }}"
            class="form-control @error('name') is-invalid @enderror" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>
<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Photo</label>
    <div class="col-sm-9">
        <div class="d-flex align-items-center gap-3">
            <img id="userPhotoPreview" src="{{ $user->avatar_url }}" alt="" class="rounded-circle shadow"
                style="width:72px;height:72px;object-fit:cover">
            <div class="flex-grow-1">
                <input type="file" name="photo" id="userPhotoInput" accept=".jpg,.jpeg,.png,.webp"
                    class="form-control @error('photo') is-invalid @enderror">
                @error('photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <div class="form-text">JPG, PNG or WebP, up to 2 MB. Without a photo the initials are shown.</div>

                @if ($user->exists && $user->photo)
                <div class="form-check mt-1">
                    <input type="hidden" name="remove_photo" value="0">
                    <input class="form-check-input" type="checkbox" name="remove_photo" id="remove_photo" value="1">
                    <label class="form-check-label" for="remove_photo">Remove the current photo</label>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Email</label>
    <div class="col-sm-9">
        <input type="email" name="email" value="{{ old('email', $user->email) }}"
            class="form-control @error('email') is-invalid @enderror" required>
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Password</label>
    <div class="col-sm-9">
        <input type="password" name="password" autocomplete="new-password"
            class="form-control @error('password') is-invalid @enderror" {{ $user->exists ? '' : 'required' }}>
        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
        <div class="form-text">
            At least 8 characters with letters and numbers.
            @if ($user->exists) Leave empty to keep the current password. @endif
        </div>
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label fw-bold text-sm-end">Confirm Password</label>
    <div class="col-sm-9">
        <input type="password" name="password_confirmation" autocomplete="new-password" class="form-control">
    </div>
</div>

<div class="row mb-3">
    <div class="col-sm-9 offset-sm-3">
        <div class="form-check">
            <input type="hidden" name="active" value="0">
            <input class="form-check-input" type="checkbox" name="active" id="active" value="1"
                @checked(old('active', $user->active ?? true))>
            <label class="form-check-label" for="active">Active (a deactivated user cannot log in)</label>
        </div>
        @error('active') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>
</div>

<h5 class="mt-4 mb-3 border-bottom pb-2">Roles</h5>
<div class="row mb-3">
    <div class="col-sm-9 offset-sm-3">
        @forelse ($roles as $role)
        @php $allowed = in_array($role->id, $grantableRoleIds, true); @endphp
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="roles[]" id="role_{{ $role->id }}" value="{{ $role->id }}"
                @checked(in_array($role->id, $selectedRoles, true)) @disabled(! $allowed)>
            <label class="form-check-label" for="role_{{ $role->id }}">
                {{ $role->name }}
                @if (in_array($role->name, config('access.system_roles'), true))
                <span class="badge text-bg-secondary">system</span>
                @endif
                @unless ($allowed)
                <i class="bi bi-lock-fill text-muted" title="You cannot give this role: it has more access than you have"></i>
                @endunless
            </label>
        </div>
        @empty
        <div class="text-muted">No roles yet. Create them under Administration → Roles.</div>
        @endforelse
        @error('roles') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        <div class="form-text">The access of the roles is added together.</div>
    </div>
</div>

<h5 class="mt-4 mb-3 border-bottom pb-2">Groups</h5>
<div class="row mb-3">
    <div class="col-sm-9 offset-sm-3">
        @forelse ($groups as $group)
        @php $allowed = in_array($group->groupid, $grantableGroupIds, true); @endphp
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="groups[]" id="group_{{ $group->groupid }}" value="{{ $group->groupid }}"
                @checked(in_array($group->groupid, $selectedGroups, true)) @disabled(! $allowed)>
            <label class="form-check-label" for="group_{{ $group->groupid }}">
                {{ $group->name }}
                @unless ($group->active) <span class="badge text-bg-secondary">inactive</span> @endunless
                @unless ($allowed)
                <i class="bi bi-lock-fill text-muted" title="You cannot give this group: it has more access than you have"></i>
                @endunless
            </label>
        </div>
        @empty
        <div class="text-muted">No groups yet. Create them under Administration → Groups.</div>
        @endforelse
        @error('groups') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    </div>
</div>

<h5 class="mt-4 mb-3 border-bottom pb-2">Department access</h5>
<div class="row mb-3">
    <div class="col-sm-9 offset-sm-3">
        <div class="form-check">
            <input class="form-check-input" type="radio" name="department_access" id="deptAll" value="all"
                @checked($mode==='all' ) @disabled(! $canGiveAllDepartments)>
            <label class="form-check-label" for="deptAll">All departments</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="department_access" id="deptSelected" value="selected"
                @checked($mode==='selected' )>
            <label class="form-check-label" for="deptSelected">Only the selected departments</label>
        </div>

        <div class="border rounded p-2 mt-2" id="deptBox">
            @forelse ($departments as $department)
            <div class="form-check">
                <input class="form-check-input dept-check" type="checkbox" name="departments[]"
                    id="dept_{{ $department->deptid }}" value="{{ $department->deptid }}"
                    @checked(in_array($department->deptid, $selectedDepartments, true))>
                <label class="form-check-label" for="dept_{{ $department->deptid }}">
                    {{ $department->fullname }} <small class="text-muted">({{ $department->shortname }})</small>
                </label>
            </div>
            @empty
            <div class="text-muted">No departments yet.</div>
            @endforelse
        </div>
        @error('departments') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        <div class="form-text">
            A restricted user sees only the courses of these departments and everything under them:
            sessions, subjects, students, lists and results. Administrators always see everything.
        </div>
    </div>
</div>

<script>
    (function() {
        var radios = document.querySelectorAll('input[name=department_access]');
        var boxes = document.querySelectorAll('.dept-check');

        // the department boxes work only for "selected departments"
        function sync() {
            var selected = document.getElementById('deptSelected').checked;
            boxes.forEach(function(box) {
                box.disabled = !selected;
            });
            document.getElementById('deptBox').classList.toggle('opacity-50', !selected);
        }

        radios.forEach(function(radio) {
            radio.addEventListener('change', sync);
        });
        sync();
    })();

    (function() {
        var input = document.getElementById('userPhotoInput');
        var preview = document.getElementById('userPhotoPreview');

        input.addEventListener('change', function() {
            var picked = input.files[0];
            if (!picked) return;

            if (picked.size > 2 * 1024 * 1024) {
                alert('The photo must not be larger than 2 MB.');
                input.value = '';
                return;
            }

            var reader = new FileReader();
            reader.onload = function(event) {
                preview.src = event.target.result;
            };
            reader.readAsDataURL(picked);
        });
    })();
</script>