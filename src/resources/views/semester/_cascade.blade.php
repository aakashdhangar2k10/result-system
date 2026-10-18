<script>
    (function () {
        var course = document.getElementById('courseid');
        var batch = document.getElementById('batchid');
        var term = document.getElementById('semid');
        var label = document.getElementById('term_label');

        function sync() {
            var opt = course.options[course.selectedIndex];
            var courseId = opt ? opt.value : '';
            var terms = opt && opt.dataset.terms ? parseInt(opt.dataset.terms, 10) : 0;
            var isYear = !!opt && opt.dataset.structure === 'year';
            var word = isYear ? 'Year' : 'Semester';

            if (label) label.textContent = word;

            // sessions of the selected course only
            Array.prototype.forEach.call(batch.options, function (o) {
                if (!o.value) return;
                var match = courseId !== '' && o.dataset.course === courseId;
                o.hidden = !match;
                o.disabled = !match;
            });
            var b = batch.options[batch.selectedIndex];
            if (b && b.disabled) batch.value = '';

            // terms up to the course length, named "First Semester" or "First Year"
            Array.prototype.forEach.call(term.options, function (o) {
                if (!o.value) return;
                var within = terms > 0 && parseInt(o.dataset.number, 10) <= terms;
                o.hidden = !within;
                o.disabled = !within;
                o.textContent = (o.dataset.base ? o.dataset.base + ' ' : '') + word;
            });
            var t = term.options[term.selectedIndex];
            if (t && t.disabled) term.value = '';
        }

        course.addEventListener('change', sync);
        sync();
    })();
</script>