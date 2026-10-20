<script>
    (function () {
        function summary() {
            var all = document.querySelectorAll('.student-check');
            var on = document.querySelectorAll('.student-check:checked').length;

            var text = document.getElementById('studentSummary');
            if (text) text.textContent = on + ' of ' + all.length;

            var top = document.getElementById('checkAllStudents');
            if (top) top.checked = all.length > 0 && on === all.length;
        }

        document.addEventListener('input', function (event) {
            if (event.target.id !== 'studentSearch') return;

            var q = event.target.value.trim().toLowerCase();
            document.querySelectorAll('.student-row').forEach(function (row) {
                row.style.display = row.dataset.search.indexOf(q) !== -1 ? '' : 'none';
            });
        });

        document.addEventListener('change', function (event) {
            // "All" ticks the rows that are currently shown
            if (event.target.id === 'checkAllStudents') {
                document.querySelectorAll('.student-row').forEach(function (row) {
                    if (row.style.display === 'none') return;
                    row.querySelector('.student-check').checked = event.target.checked;
                });
            }

            if (event.target.matches('.student-check, #checkAllStudents')) summary();
        });

        // the add page calls this after it inserts the students
        window.refreshStudentSummary = summary;
        summary();
    })();
</script>