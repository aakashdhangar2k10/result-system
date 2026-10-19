<script>
    (function () {
        // search boxes: <input data-table="tableId"> hides the rows whose text does not contain what was typed
        document.addEventListener('input', function (event) {
            var box = event.target.closest('[data-table]');
            if (!box) return;

            var table = document.getElementById(box.dataset.table);
            if (!table) return;

            var text = box.value.trim().toLowerCase();
            table.querySelectorAll('tbody tr[data-search]').forEach(function (row) {
                row.style.display = row.dataset.search.indexOf(text) !== -1 ? '' : 'none';
            });
        });

        function counts() {
            var home = document.querySelectorAll('.home-check');
            var homeChecked = document.querySelectorAll('.home-check:checked').length;
            var extraChecked = document.querySelectorAll('.extra-check:checked').length;

            var summary = document.getElementById('selectedSummary');
            if (summary) {
                summary.textContent = homeChecked + ' of ' + home.length + ' session students, '
                    + extraChecked + ' back / repeat';
            }

            var all = document.getElementById('checkAllHome');
            if (all) all.checked = home.length > 0 && homeChecked === home.length;
        }

        document.addEventListener('change', function (event) {
            // "All" ticks the rows that are currently shown
            if (event.target.id === 'checkAllHome') {
                document.querySelectorAll('#homeTable tbody tr').forEach(function (row) {
                    if (row.style.display === 'none') return;
                    var box = row.querySelector('.home-check');
                    if (box) box.checked = event.target.checked;
                });
            }

            if (event.target.matches('.home-check, .extra-check, #checkAllHome')) counts();
        });

        // the Add page calls this after it inserts the students
        window.refreshStudentCounts = counts;
        counts();
    })();
</script>