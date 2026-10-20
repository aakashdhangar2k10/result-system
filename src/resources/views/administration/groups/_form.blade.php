@php $members = array_map('intval', old('members', $checkedMembers)); @endphp

<div class="row mb-3">
    <label class="col-sm-2 col-form-label fw-bold text-sm-end">Group Name</label>
    <div class="col-sm-6">
        <input type="text" name="name" value="{{ old('name', $group->name) }}"
               class="form-control @error('name') is-invalid @enderror" placeholder="Result Declaration Team" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-2 col-form-label fw-bold text-sm-end">Description</label>
    <div class="col-sm-6">
        <input type="text" name="description" value="{{ old('description', $group->description) }}"
               class="form-control @error('description') is-invalid @enderror" maxlength="255">
        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-3">
    <div class="col-sm-6 offset-sm-2">
        <div class="form-check">
            <input type="hidden" name="active" value="0">
            <input class="form-check-input" type="checkbox" name="active" id="active" value="1"
                   @checked(old('active', $group->active ?? true))>
            <label class="form-check-label" for="active">Active (an inactive group gives no access)</label>
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

<div class="row mb-3">
    <label class="col-sm-2 col-form-label fw-bold text-sm-end">Members</label>
    <div class="col-sm-10">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <input type="search" id="memberSearch" class="form-control form-control-sm w-auto" placeholder="Search name or email">
            <span class="small text-muted">Selected: <span id="memberCount">0</span> of {{ $users->count() }}</span>
        </div>

        <div class="border rounded p-2" style="max-height:300px; overflow:auto">
            @foreach ($users as $member)
                <div class="form-check member-row" data-search="{{ mb_strtolower($member->name . ' ' . $member->email) }}">
                    <input class="form-check-input member-check" type="checkbox" name="members[]"
                           id="member_{{ $member->id }}" value="{{ $member->id }}" @checked(in_array($member->id, $members, true))>
                    <label class="form-check-label" for="member_{{ $member->id }}">
                        {{ $member->name }} <small class="text-muted">{{ $member->email }}</small>
                        @unless ($member->active) <span class="badge text-bg-secondary">inactive</span> @endunless
                    </label>
                </div>
            @endforeach
        </div>
        @error('members') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        <div class="form-text">The members get the access of this group, together with the access of their own roles.</div>
    </div>
</div>

<script>
    (function () {
        var rows = document.querySelectorAll('.member-row');

        function count() {
            document.getElementById('memberCount').textContent = document.querySelectorAll('.member-check:checked').length;
        }

        document.getElementById('memberSearch').addEventListener('input', function (event) {
            var q = event.target.value.trim().toLowerCase();
            rows.forEach(function (row) { row.style.display = row.dataset.search.indexOf(q) !== -1 ? '' : 'none'; });
        });

        document.addEventListener('change', function (event) {
            if (event.target.classList.contains('member-check')) count();
        });

        count();
    })();
</script>