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
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $t->title ?: 'Ph.D. course work sheet' }}</title>
    <style>
        @page { margin: {{ $m[0] }}mm {{ $m[1] }}mm {{ $m[2] }}mm {{ $m[3] }}mm; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 9pt; color: #000; }
        table { border-collapse: collapse; width: 100%; }
        table.plain td { border: none; padding: 0; vertical-align: middle; }
        table.grid th, table.grid td { border: 1px solid #000; padding: 3px 5px; text-align: center; vertical-align: middle; font-weight: bold; }
        .left { text-align: left !important; }
        .strong { font-weight: bold; }
        .uni { font-size: 15pt; font-weight: bold; margin: 0 0 {{ $sp['header_lines'] }}mm; }
        .hl { font-size: 10pt; font-weight: bold; line-height: 1.3; margin: 0 0 {{ $sp['header_lines'] }}mm; }
        .title { font-weight: bold; font-size: 11pt; margin: 0 0 {{ $sp['sections'] }}mm; }
        .gap { margin-top: {{ $sp['sections'] }}mm; }
        .small { font-size: 8pt; }
        .certificate { font-weight: bold; margin: {{ $sp['sections'] + 2 }}mm 0 0; line-height: 1.5; }
        .note { margin: {{ $sp['sections'] }}mm 0 0; font-weight: bold; }
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

        <div style="padding-top: {{ $showHeader ? $sp['after_header'] : 0 }}mm">

            @if ($t->title)
                <div class="title" style="text-align: {{ $align($t->title_align) }}">{{ $t->title }}</div>
            @endif

            {{-- the information table: label | value --}}
            <table class="grid">
                @foreach ($sheet['info'] as $row)
                    <tr>
                        <td class="left" style="width:52%">{{ $row['label'] }}</td>
                        <td class="left">{{ $row['value'] }}</td>
                    </tr>
                @endforeach
            </table>

            {{-- the course table --}}
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
                    <tr>
                        <td colspan="{{ $marks ? 5 : 2 }}" class="left">Total</td>
                        <td>{{ $sheet['total_credits'] }}</td>
                        <td></td>
                        <td></td>
                        <td>{{ $sheet['total_points'] }}</td>
                    </tr>

                    @if ($t->show_attempt_row)
                        <tr>
                            <td class="left">{{ $t->attempt_label }}</td>
                            <td class="left">{{ $sheet['attempt'] }}</td>
                            <td colspan="{{ $columns - 3 }}" class="left">SGPA</td>
                            <td>{{ $sheet['sgpa'] }}</td>
                        </tr>
                    @endif

                    @if ($t->show_result_row)
                        <tr>
                            <td class="left">{{ $t->result_row_label }}</td>
                            <td class="left">
                                {{ $sheet['result'] }}
                                @if ($t->show_equivalent && $sheet['equivalent'] !== null)
                                    &nbsp;&nbsp;&nbsp;{{ $t->equivalent_label }} {{ $sheet['equivalent'] }}
                                @endif
                            </td>
                            <td colspan="{{ $columns - 2 }}" class="left">
                                @if ($t->show_division && $sheet['division'])
                                    {{ $t->division_label }} {{ $sheet['division'] }}
                                @endif
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>

            {{-- footnotes --}}
            @if ($t->show_repeat_note || $t->show_equivalent_note)
                <table class="plain" style="margin-top: 1mm">
                    <tr>
                        <td class="strong small left">{{ $t->show_repeat_note ? '*Grade in Repeat Examination' : '' }}</td>
                        <td class="strong small" style="text-align: right">
                            @if ($t->show_equivalent_note) Equivalent Percentage=SGPA*{{ $pack['multiplier'] }} @endif
                        </td>
                    </tr>
                </table>
            @endif

            @if ($sheet['certificate'])
                <p class="certificate">{{ $sheet['certificate'] }}</p>
            @endif

            @if ($t->show_declared_date && $sheet['declared'])
                <p class="note">Date of Result: {{ $sheet['declared'] }}</p>
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