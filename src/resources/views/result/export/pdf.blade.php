<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $sheet['title'] }}</title>
    <style>
        @page { margin: 22px 24px; }
        body { font-family: 'Times', serif; font-size: 9pt; color: #000; }
        .head { text-align: center; font-weight: bold; margin: 0; line-height: 1.4; }
        .head.big { font-size: 12pt; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #444; padding: 2px 4px; text-align: center; }
        th { font-weight: bold; }
        .left { text-align: left; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
        .gap { margin-top: 16px; }
        .note { font-weight: bold; margin: 14px 0 0; }
        table.sign { border: none; margin-top: 46px; }
        table.sign td { border: none; font-weight: bold; text-align: center; width: 33%; }
        .max { font-size: 7pt; font-weight: normal; }
    </style>
</head>
<body>
    <p class="head big">{{ $sheet['department'] }}</p>
    <p class="head big">{{ $sheet['university'] }}</p>
    <p class="head">{{ $sheet['title'] }}</p>
    <p class="head" style="margin-bottom:10px">{{ $sheet['batch'] }}</p>

    <table>
        <thead>
            <tr>
                <th style="width:4%">S. No.</th>
                <th style="width:10%">Roll Number</th>
                <th style="width:10%">Enrollment Number</th>
                <th style="width:20%">Student Name</th>
                @foreach ($sheet['subjects'] as $subject)
                    <th>
                        {{ $subject['code'] }}
                        @if ($sheet['view'] === 'marks')
                            <br><span class="max">({{ $subject['max'] }})</span>
                        @endif
                    </th>
                @endforeach
                <th style="width:6%">SGPA</th>
                <th style="width:9%">Result</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($sheet['rows'] as $row)
            <tr>
                <td>{{ $row['sr'] }}</td>
                <td>{{ $row['roll'] }}</td>
                <td>{{ $row['enrollment'] }}</td>
                <td class="left">{{ $row['name'] }}</td>
                @foreach ($row['cells'] as $value)
                    <td>{{ $value }}</td>
                @endforeach
                <td>{{ $row['sgpa'] }}</td>
                <td>{{ $row['result'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <table class="gap">
        <colgroup>
            <col style="width:9%"><col style="width:35%"><col style="width:8%">
            <col style="width:9%"><col style="width:31%"><col style="width:8%">
        </colgroup>
        <thead>
            <tr>
                <th>Subject Code</th><th>Subject Name</th><th>Subject Credit</th>
                <th>Subject Code</th><th>Subject Name</th><th>Subject Credit</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($sheet['pairs'] as $pair)
            <tr>
                @foreach ([0, 1] as $i)
                    @if (isset($pair[$i]))
                        <td>{{ $pair[$i]['code'] }}</td>
                        <td class="left">{{ $pair[$i]['name'] }}</td>
                        <td>{{ $pair[$i]['credit'] }}</td>
                    @else
                        <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                    @endif
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>

    @if ($sheet['declared'])
        <p class="note">Date of Declaration of Result: {{ $sheet['declared'] }}</p>
    @endif

    <table class="sign">
        <tr>
            @foreach (array_slice($sheet['signatures'], 0, 3) as $label)
                <td>{{ $label }}</td>
            @endforeach
        </tr>
    </table>
</body>
</html>