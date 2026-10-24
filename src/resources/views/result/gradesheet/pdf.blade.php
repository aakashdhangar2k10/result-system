@php
    $t = $pack['template'];
    $m = $pack['margins'];
    $showHeader = $pack['show_header'];
    $logo = $pack['logo'];
    $anyPhoto = $pack['show_photo'];
    $marks = $t->show_marks;
    $columns = $marks ? 9 : 6;
    $footer = $t->show_footer ? array_values(array_filter((array) $t->footer_items)) : [];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $t->title }}</title>
    <style>
        @page { margin: {{ $m[0] }}mm {{ $m[1] }}mm {{ $m[2] }}mm {{ $m[3] }}mm; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 9pt; color: #000; }
        table { border-collapse: collapse; width: 100%; }
        table.plain td { border: none; padding: 0; vertical-align: middle; }
        table.grid th, table.grid td { border: 1px solid #000; padding: 3px 5px; text-align: center; vertical-align: middle; }
        table.grid th { font-weight: bold; }
        .left { text-align: left !important; }
        .strong { font-weight: bold; }
        .uni { font-size: 15pt; font-weight: bold; text-align: center; margin: 0; }
        .hl { font-size: 10pt; font-weight: bold; text-align: center; line-height: 1.35; margin: 0; }
        .rule { border: 0; border-top: 1px solid #000; margin: 8px 0 12px; }
        .title { text-align: center; font-weight: bold; font-size: 11pt; margin: 4px 0 2px; }
        .prog { text-align: center; font-weight: bold; font-size: 10pt; margin: 0 0 8px; }
        .block { margin: 0 0 6px; font-weight: bold; }
        .gap { margin-top: 10px; }
        .note { margin: 4px 0 0; }
        table.sign { margin-top: 44px; }
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
            @php $side = $logo || $sheet['photo']; @endphp
            <table class="plain">
                <tr>
                    @if ($side)
                        <td style="width:90px">@if ($logo)<img class="logo" src="{{ $logo }}" alt="">@endif</td>
                    @endif
                    <td>
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
                    @if ($side)
                        <td style="width:90px; text-align:right">@if ($sheet['photo'])<img class="photo" src="{{ $sheet['photo'] }}" alt="">@endif</td>
                    @endif
                </tr>
            </table>
            @if ($t->show_header_line)<hr class="rule">@endif
        @elseif ($sheet['photo'])
            {{-- no header (letterhead paper): the photo can still be printed --}}
            <div style="text-align:right"><img class="photo" src="{{ $sheet['photo'] }}" alt=""></div>
        @endif

        <div class="title">{{ $t->title }}</div>

        @if ($t->show_institute_block)
            <p class="block">
                Name of the Institute / School of Studies : {{ $sheet['department'] }}<br>
                @if ($t->institute_address) Address of Institute / School of Studies : {{ $t->institute_address }} @endif
            </p>
        @endif

        <p class="prog">{{ $t->programme_label }} {{ $sheet['programme'] }}</p>

        {{-- student --}}
        <table class="grid">
            <tr>
                <td class="left" style="width:50%"><span class="strong">Batch:</span> {{ $sheet['batch'] }}</td>
                <td class="left"><span class="strong">Examination:</span> {{ $sheet['examination'] }}</td>
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
                    <td>{{ $row['grade'] }}</td>
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

        {{-- semester-wise details --}}
        @if ($t->show_semester_summary && $sheet['terms'])
            <table class="grid gap">
                <tr><th colspan="{{ count($sheet['terms']) + 1 }}">{{ mb_strtoupper($sheet['term_word']) }}-WISE DETAILS OF RESULT</th></tr>
                <tr>
                    <th style="width:14%">{{ $sheet['term_word'] }}</th>
                    @foreach ($sheet['terms'] as $cell)<th>{{ $cell['label'] }}</th>@endforeach
                </tr>
                <tr>
                    <th>Credit</th>
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
                    @foreach ($sheet['terms'] as $cell)<td>{{ $cell['result'] }}</td>@endforeach
                </tr>
            </table>
        @endif

        @if ($t->show_legend)
            <p class="note"><span class="strong">SGPA:</span> Semester Grade Point Average</p>
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
@endforeach
</body>
</html>