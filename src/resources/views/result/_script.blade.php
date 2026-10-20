<script>
    (function () {
        var form = document.getElementById('resultForm');
        var calcBtn = document.getElementById('calcBtn');
        var submitBtn = document.getElementById('submitBtn');
        var note = document.getElementById('calcMessage');

        function table() { return document.getElementById('entryTable'); }

        function clearOutputs() {
            document.querySelectorAll('#entryTable .out-total, #entryTable .out-grade, #entryTable .out-point').forEach(function (cell) {
                cell.textContent = '';
                cell.classList.remove('text-success', 'text-danger', 'text-secondary');
            });
            document.querySelectorAll('#entryTable .is-invalid').forEach(function (el) {
                el.classList.remove('is-invalid');
                el.removeAttribute('title');
            });
        }

        // any change means the grades on screen are out of date: Submit waits for a new calculation
        function invalidate() {
            submitBtn.disabled = true;
            clearOutputs();
            note.className = 'me-3 text-warning';
            note.textContent = 'Marks changed: click Calculate Grade again.';
        }

        // called after the student table is inserted
        function afterLoad() {
            submitBtn.disabled = true;
            calcBtn.disabled = !table();
            note.textContent = '';
        }

        document.addEventListener('change', function (event) {
            // an Absent tick disables the mark next to it
            if (event.target.matches('.absent-check')) {
                var input = event.target.closest('td').querySelector('.mark-input');
                input.disabled = event.target.checked;
                if (event.target.checked) input.value = '';
            }

            if (event.target.closest('#entryTable') && event.target.matches('.mark-input, .absent-check')) invalidate();
        });

        document.addEventListener('input', function (event) {
            if (event.target.closest('#entryTable') && event.target.matches('.mark-input')) invalidate();
        });

        calcBtn.addEventListener('click', function () {
            if (!table()) return;

            var data = new FormData(form);
            data.delete('_method');   // the edit page spoofs PUT, but the calculate route is a POST

            calcBtn.disabled = true;
            note.className = 'me-3 text-muted';
            note.textContent = 'Calculating...';
            clearOutputs();

            fetch(form.dataset.calcUrl, {
                method: 'POST',
                body: data,
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
                .then(function (response) {
                    return response.json().then(function (body) { return { ok: response.ok, body: body }; });
                })
                .then(function (res) {
                    if (!res.ok || !res.body.ok) {
                        var errors = res.body.errors || {};

                        Object.keys(errors).forEach(function (key) {
                            var parts = key.split('.');
                            var row = document.querySelector('#entryTable tr[data-student="' + parts[0] + '"]');
                            var field = row && row.querySelector('[data-field="' + parts[1] + '"]');
                            if (field) {
                                field.classList.add('is-invalid');
                                field.title = errors[key];
                            }
                        });

                        note.className = 'me-3 text-danger';
                        note.textContent = res.body.message || 'Could not calculate.';
                        return;
                    }

                    var rows = res.body.rows;

                    Object.keys(rows).forEach(function (id) {
                        var r = rows[id];
                        var row = document.querySelector('#entryTable tr[data-student="' + id + '"]');
                        if (!row) return;

                        var tone = r.absent ? 'text-secondary' : (r.pass ? 'text-success' : 'text-danger');

                        ['total', 'grade', 'point'].forEach(function (name) {
                            var cell = row.querySelector('.out-' + name);
                            if (!cell) return;
                            cell.textContent = name === 'point' ? r.point : r[name];
                            cell.classList.add(tone);
                        });
                    });

                    submitBtn.disabled = false;
                    note.className = 'me-3 text-success';
                    note.textContent = Object.keys(rows).length + ' students calculated. Check the grades, then Submit.';
                })
                .catch(function () {
                    note.className = 'me-3 text-danger';
                    note.textContent = 'Could not calculate. Reload the page and try again.';
                })
                .finally(function () { calcBtn.disabled = !table(); });
        });

        // one click on Submit sends the form once
        form.addEventListener('submit', function () { submitBtn.disabled = true; });

        window.resultForm = { afterLoad: afterLoad };
        afterLoad();
    })();
</script>