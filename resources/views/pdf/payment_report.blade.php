@php use Carbon\Carbon; @endphp
    <!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Pagamentos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .header {
            text-align: center;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="header">
    <h1>Relatório de Pagamentos</h1>
    <h3>Gerado por PIXCoin</h3>
    <p>Máquina: {{ $maquinaNome }}</p>
    <p>{{ Carbon::parse($startDate)->format('d/m/Y') }} - {{ Carbon::parse($endDate)->format('d/m/Y') }}</p>
</div>

<div class="content">
    <p>Total sem estorno: R$ {{ number_format($totalSemEstorno, 2, ',', '.') }}</p>
    <p>Total estornado: R$ {{ number_format($totalComEstorno, 2, ',', '.') }}</p>
    <p>Total em espécie: R$ {{ number_format($totalEspecie, 2, ',', '.') }}</p>
</div>

<table class="table">
    <thead>
    <tr>
        @foreach ($tableArray['headers'] as $header)
            <th>{{ $header }}</th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @foreach ($tableArray['rows'] as $row)
        <tr>
            @foreach ($row as $cell)
                <td>{{ $cell }}</td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>

<div class="footer">
    <p>Relatório gerado automaticamente.</p>
</div>
</body>
</html>
