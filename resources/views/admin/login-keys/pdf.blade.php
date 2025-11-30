<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>Prisijungimo raktai - {{ $school->name }}</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            font-size: 14px;
        }
        h2 { 
            text-align: center; 
            margin-bottom: 10px; 
            font-size: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #f0f0f0;
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        td {
            border: 1px solid #ddd;
            padding: 10px;
            vertical-align: top;
        }
        tr:nth-child(even) {
            background-color: #fafafa;
        }
        .key-code {
            font-family: monospace;
            font-weight: bold;
            font-size: 16px;
            background-color: #f5f5f5;
            padding: 5px;
            border-radius: 3px;
        }
        @media print {
            table { page-break-inside: avoid; }
            tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <h2>Prisijungimo raktai</h2>
    <div class="header">
        <p><strong>Mokykla:</strong> {{ $school->name }}</p>
        @if(request('type'))
            <p><strong>Tipas:</strong> {{ request('type') === 'student' ? 'Mokiniai' : 'Mokytojai' }}</p>
        @endif
        @if(request('class_id'))
            @php
                $selectedClass = $school->classes->find(request('class_id'));
            @endphp
            @if($selectedClass)
                <p><strong>Klasė:</strong> {{ $selectedClass->name }}</p>
            @endif
        @endif
        @if(request('teacher_id'))
            @php
                $selectedTeacher = $school->loginKeys()->find(request('teacher_id'));
            @endphp
            @if($selectedTeacher)
                <p><strong>Mokytojas:</strong> {{ $selectedTeacher->full_name }}</p>
            @endif
        @endif
        @if(request('school_year'))
            <p><strong>Mokslo metai:</strong> {{ request('school_year') }}</p>
        @endif
        <p><strong>Iš viso raktų:</strong> {{ $loginKeys->count() }}</p>
        <p><strong>Išspausdinta:</strong> {{ now()->format('Y-m-d H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: {{ request('show_email') ? '16%' : '18%' }};">Vardas Pavardė</th>
                <th style="width: 10%;">Tipas</th>
                <th style="width: 10%;">Klasė</th>
                <th style="width: {{ request('show_email') ? '16%' : '18%' }};">Klasės vadovas</th>
                <th style="width: 10%;">Mokslo metai</th>
                <th style="width: {{ request('show_email') ? '23%' : '34%' }};">Prisijungimo raktas</th>
                @if(request('show_email'))
                    <th style="width: 15%;">El. paštas</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($loginKeys as $key)
                <tr>
                    <td>{{ $key->first_name }} {{ $key->last_name }}</td>
                    <td>
                        @if($key->type === 'student')
                            <strong>Mokinys</strong>
                        @else
                            <strong>Mokytojas</strong>
                        @endif
                    </td>
                    <td>
                        @if($key->class)
                            {{ $key->class->name }}
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if($key->type === 'student')
                            {{-- Mokiniui rodyti klasės vadovą --}}
                            @if($key->class && $key->class->teacher)
                                {{ $key->class->teacher->full_name }}
                            @else
                                -
                            @endif
                        @else
                            {{-- Mokytojui rodyti kokių klasių jis vadovas --}}
                            @if($key->leadingClasses && $key->leadingClasses->count() > 0)
                                {{ $key->leadingClasses->pluck('name')->join(', ') }}
                            @else
                                -
                            @endif
                        @endif
                    </td>
                    <td>
                        {{ $key->school_year ?: '-' }}
                    </td>
                    <td>
                        <div class="key-code">{{ $key->key }}</div>
                    </td>
                    @if(request('show_email'))
                        <td>
                            {{ $key->email ?: 'Nenustatytas' }}
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    <script>
        window.print();
    </script>
</body>
</html>
