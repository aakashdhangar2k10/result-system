@php
    $t = $pack['template'];
    $m = $pack['margins'];
    $sp = $pack['spacing'];
    $showHeader = $pack['show_header'];
    $logo = $pack['logo'];
    $marks = $t->show_marks;
    $columns = $marks ? 9 : 6;
    $footer = $t->show_footer ? array_values(array_filter((array) $t->footer_items)) : [];
    $align = fn ($value) => in_array($value, ['left', 'center', 'right'], true) ? $value : 'left';
    $multiplier = rtrim(rtrim(number_format((float) config('result.cgpa_multiplier', 10), 2, '.', ''), '0'), '.');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $t->title ?: 'Grade sheet' }}</title>
    <style>
        @page { margin: {{ $m[0] }}mm {{ $m[1] }}mm {{ $m[2] }}mm {{ $m[3] }}mm; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 9pt; color: #000; }
        table { border-collapse: collapse; width: 100%; }
        table.plain td { border: none; padding: 0; vertical-align: middle; }
        table.grid th, table.grid td { border: 1px solid #000; padding: 3px 5px; text-align: center; vertical-align: middle; }
        table.grid th { font-weight: bold; }
        .left { text-align: left !important; }
        .strong { font-weight: bold; }
        .uni { font-size: 15pt; font-weight: bold; margin: 0 0 {{ $sp['header_lines'] }}mm; }
        .hl { font-size: 10pt; font-weight: bold; line-height: 1.3; margin: 0 0 {{ $sp['header_lines'] }}mm; }
        .title { font-weight: bold; font-size: 11pt; margin: 0 0 1mm; }
        .inst { font-weight: bold; margin: 0 0 2mm; line-height: 1.4; }
        .prog { font-weight: bold; font-size: 10pt; margin: 0 0 {{ $sp['sections'] }}mm; }
        .gap { margin-top: {{ $sp['sections'] }}mm; }
        .note { margin: 1.5mm 0 0; }
        .small { font-size: 8pt; }
        table.sign { margin-top: {{ $sp['footer'] }}mm; }
        table.sign td { border: none; font-weight: bold; padding: 0; }
        img.logo { width: 78px; }
        img.photo { width: 70px; height: 88px; }
    </style>
</head>
<body>
@foreach ($pack['sheets'] as $sheet)
    <div @if (! $loop->last) style="page-break-after: always" @endif>

        {{-- header --}}
        @if ($showHeader)
            @php
                // logo and photo: each goes to the side chosen in the template
                $left = [];
                $right = [];

                if ($logo) {
                    if ($t->logo_position === 'right') { $right[] = ['logo', $logo]; } else { $left[] = ['logo', $logo]; }
                }

                if ($sheet['photo']) {
                    if ($t->photo_position === 'left') { $left[] = ['photo', $sheet['photo']]; } else { $right[] = ['photo', $sheet['photo']]; }
                }

                // both side cells get the same width, so the text stays centred
                $sideWidth = max(count($left), count($right)) * 92;
            @endphp

            <div style="padding: {{ $sp['header_top'] }}mm {{ $sp['header_side'] }}mm 0 {{ $sp['header_side'] }}mm">
                <table class="plain">
                    <tr>
                        @if ($sideWidth)
                            <td style="width: {{ $sideWidth }}px; text-align: left">
                                @foreach ($left as [$kind, $src])<img class="{{ $kind }}" src="{{ $src }}" alt="">@endforeach
                            </td>
                        @endif

                        <td style="text-align: {{ $align($t->header_align) }}">
                            @if ($t->university_name)<p class="uni">{{ $t->university_name }}</p>@endif
                            @foreach (\App\Models\Result\ResultTemplate::lines($t->header_lines_top) as $line)
                                <p class="hl">{{ $line }}</p>
                            @endforeach
                            @if ($t->show_department && $sheet['department'])
                                <p class="hl">{{ mb_strtoupper($sheet['department']) }}</p>
                            @endif
                            @foreach (\App\Models\Result\ResultTemplate::lines($t->header_lines_bottom) as $line)
                                <p class="hl">{{ $line }}</p>
                            @endforeach
                        </td>

                        @if ($sideWidth)
                            <td style="width: {{ $sideWidth }}px; text-align: right">
                                @foreach ($right as [$kind, $src])<img class="{{ $kind }}" src="{{ $src }}" alt="">@endforeach
                            </td>
                        @endif
                    </tr>
                </table>

                @if ($t->show_header_line)
                    <div style="border-top: 1px solid #000; margin-top: 2mm"></div>
                @endif
            </div>
        @elseif ($sheet['photo'])
            {{-- no header (letterhead paper): the photo can still be printed --}}
            <div style="text-align: {{ $t->photo_position === 'left' ? 'left' : 'right' }}"><img class="photo" src="{{ $sheet['photo'] }}" alt=""></div>
        @endif

        {{-- the gap between the header and the grade sheet --}}
        <div style="padding-top: {{ $showHeader ? $sp['after_header'] : 0 }}mm">

            @if ($t->title)
                <div class="title" style="text-align: {{ $align($t->title_align) }}">{{ $t->title }}</div>
            @endif

            @if ($t->show_institute_block)
                <div class="inst" style="text-align: {{ $align($t->institute_align) }}">
                    <div>{{ $t->institute_label }} {{ $sheet['department'] }}</div>
                    @if ($t->institute_address)
                        <div>{{ $t->address_label }} {{ $t->institute_address }}</div>
                    @endif
                </div>
            @endif

            <div class="prog" style="text-align: {{ $align($t->programme_align) }}">{{ trim($t->programme_label . ' ' . $sheet['programme']) }}</div>

            {{-- student --}}
            <table class="grid">
                <tr>
                    <td class="left" style="width:50%"><span class="strong">Batch:</span> {{ $sheet['batch'] }}</td>
                    <td class="left"><span class="strong">{{ $pack['exam_label'] }}:</span> {{ $sheet['examination'] }}</td>
                </tr>
                <tr>
                    <td class="left"><span class="strong">Name of the Student:</span> {{ $sheet['name'] }}</td>
                    <td class="left"><span class="strong">Enrollment No:</span> {{ $sheet['enrollment'] }}</td>
                </tr>
                <tr>
                    <td class="left"><span class="strong">{{ $sheet['father_label'] }}:</span> {{ $sheet['father'] }}</td>
                    <td class="left"><span class="strong">Roll No:</span> {{ $sheet['roll'] }}</td>
                </tr>
                <tr>
                    <td class="left"><span class="strong">Mother's Name:</span> {{ $sheet['mother'] }}</td>
                    <td class="left"><span class="strong">{{ $sheet['term_word'] }}:</span> {{ $sheet['term'] }}</td>
                </tr>
            </table>

            {{-- subjects --}}
            <table class="grid gap">
                <thead>
                    <tr>
                        <th style="width:11%">Course Code</th>
                        <th>Course Title</th>
                        @if ($marks)
                            <th>Internal</th><th>External</th><th>Total</th>
                        @endif
                        <th style="width:9%">Credits</th>
                        <th style="width:9%">Grade</th>
                        <th style="width:9%">Grade Point</th>
                        <th style="width:15%">Credits Points<br>(Credits X Grade Points)</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($sheet['rows'] as $row)
                    <tr>
                        <td>{{ $row['code'] }}</td>
                        <td class="left">{{ $row['title'] }}</td>
                        @if ($marks)
                            <td>{{ $row['marks']['internal'] ?? '-' }}</td>
                            <td>{{ $row['marks']['external'] ?? '-' }}</td>
                            <td>{{ $row['marks']['total'] ?? '-' }}</td>
                        @endif
                        <td>{{ $row['credit'] }}</td>
                        <td>{{ $row['grade'] }}{{ $row['repeat'] ? '*' : '' }}</td>
                        <td>{{ $row['point'] }}</td>
                        <td>{{ $row['points'] }}</td>
                    </tr>
                @endforeach
                    <tr class="strong">
                        <td colspan="{{ $marks ? 5 : 2 }}" class="left">Total</td>
                        <td>{{ $sheet['total_credits'] }}</td>
                        <td></td>
                        <td></td>
                        <td>{{ $sheet['total_points'] }}</td>
                    </tr>
                    <tr class="strong">
                        <td colspan="{{ $columns - 1 }}" class="left">SGPA</td>
                        <td>{{ $sheet['sgpa'] }}</td>
                    </tr>
                </tbody>
            </table>

            @if ($t->show_repeat_note && collect($sheet['rows'])->contains('repeat', true))
                <p class="note strong small">* Grade in Repeat Examination</p>
            @endif

            {{-- semester-wise details --}}
            @if ($t->show_semester_summary && $sheet['terms'])
                <table class="grid gap">
                    <tr><th colspan="{{ count($sheet['terms']) + 1 }}">{{ mb_strtoupper($sheet['term_word']) }}-WISE DETAILS OF RESULT</th></tr>
                    <tr>
                        <th style="width:14%">{{ $sheet['term_word'] }}</th>
                        @foreach ($sheet['terms'] as $cell)<th>{{ $cell['label'] }}</th>@endforeach
                    </tr>
                    <tr>
                        <th>CREDITS</th>
                        @foreach ($sheet['terms'] as $cell)<td>{{ $cell['credit'] }}</td>@endforeach
                    </tr>
                    <tr>
                        <th>SGPA</th>
                        @foreach ($sheet['terms'] as $cell)<td>{{ $cell['sgpa'] }}</td>@endforeach
                    </tr>
                    <tr>
                        <th>ATTEMPT</th>
                        @foreach ($sheet['terms'] as $cell)<td>{{ $cell['attempt'] }}</td>@endforeach
                    </tr>
                    <tr>
                        <th>RESULT</th>
                        @foreach ($sheet['terms'] as $cell)<td class="strong">{{ $cell['result'] }}</td>@endforeach
                    </tr>
                </table>
            @endif

            @if ($t->show_legend)
                <p class="note"><span class="strong">SGPA:</span> Semester Grade Point Average</p>
            @endif

            {{-- final result: printed on the sheet of a final semester --}}
            @if ($sheet['final'])
                @php $final = $sheet['final']; @endphp
                <table class="grid gap">
                    <tr>
                        <th colspan="4">FINAL RESULT{{ $final['degree'] ? ' (' . $final['degree'] . ')' : '' }}: {{ $final['label'] }}</th>
                    </tr>
                    <tr>
                        <th style="width:22%">TOTAL CREDITS</th>
                        <th style="width:14%">CGPA</th>
                        <th>EQUIVALENT PERCENTAGE</th>
                        <th style="width:28%">DIVISION</th>
                    </tr>
                    <tr class="strong">
                        <td>{{ $final['credits'] }}</td>
                        <td>{{ $final['cgpa'] }}</td>
                        <td>{{ $final['percentage'] }}</td>
                        <td>{{ $final['division'] }}</td>
                    </tr>
                </table>
                <table class="plain" style="margin-top: 1mm">
                    <tr>
                        <td class="strong small left">Equivalent Percentage= CGPA*{{ $multiplier }}</td>
                        <td class="strong small" style="text-align: right">CGPA: Cumulative Grade Point Average</td>
                    </tr>
                </table>
            @endif

            @if ($t->show_declared_date && $sheet['declared'])
                <p class="note"><span class="strong">Date of Result:</span> {{ $sheet['declared'] }}</p>
            @endif

            {{-- signatures --}}
            @if ($footer)
                <table class="sign">
                    <tr>
                        @foreach ($footer as $label)
                            <td style="width: {{ round(100 / count($footer), 2) }}%; text-align: {{ $loop->first ? 'left' : ($loop->last && count($footer) > 1 ? 'right' : 'center') }}">{{ $label }}</td>
                        @endforeach
                    </tr>
                </table>
            @endif
        </div>
    </div>
@endforeach
</body>
</html>