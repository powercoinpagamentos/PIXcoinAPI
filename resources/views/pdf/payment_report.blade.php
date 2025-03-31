@php use Carbon\Carbon; @endphp
    <!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Pagamentos</title>
    <style>

        @page {  margin: 0;   }

        body { font-family: Arial, sans-serif; }

        .banner,
        .table,
        .footer { padding: 20px; }

        .informs, .content {
            padding: 0 20px;
        }

        .banner {
            width: 100%;
            height: 50px;
            background-color: #0097b2;
            position: relative;
            color: white;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th {
            background-color: #0097b2;
            color: white;
            border: none !important;
            text-align: left;
            padding: 10px 5px;
        }

        .table td {
            border-bottom: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            width: 100%;
            height: 50px;
            background-color: #0097b2;
            color: white;
            position: absolute;
            bottom: 0;
            left: 0;
        }
    </style>
</head>
<body>

<div class="banner">
    <h1 style="margin: 0">Relatório de pagamentos</h1>
    <h1 style="margin: 0; text-align: right; position: absolute; top: 20px; right: 80px;">PIXcoin</h1>
</div>

<div class="informs">
    <h2>Informações do cliente e máquina:</h2>

    <h3 style="margin-bottom: 15px; border-bottom: 2px solid #0097b2;">Máquina: {{ $maquinaNome }}</h3>
    <h3 style="margin-bottom: 15px; border-bottom: 2px solid #0097b2;">Responsável: {{ $clienteNome }}</h3>
    <h3 style="margin-bottom: 15px; border-bottom: 2px solid #0097b2;">Data: {{ Carbon::parse($startDate)->format('d/m/Y') }} - {{ Carbon::parse($endDate)->format('d/m/Y') }}</h3>
</div>

@if($totalSemEstorno > 0 || $totalEspecie > 0 || $totalComEstorno > 0)
    <div class="content">
        @if($hasPagBank)
            <h2>Soma de pagamentos do Mercado Pago:</h2>
        @else
            <h2>Soma de pagamentos:</h2>
        @endif

        <h3  style="margin-bottom: 15px; border-bottom: 2px solid #0097b2;">Soma total entre Pix, Débito e Crédito: R$ {{ number_format($totalSemEstorno - $totalEspecie, 2, ',', '.') }}</h3>
        <h3  style="margin-bottom: 15px; border-bottom: 2px solid #0097b2;">Soma total de Espécie: R$ {{ number_format($totalEspecie, 2, ',', '.') }}</h3>
        <h3  style="margin-bottom: 15px; border-bottom: 2px solid #0097b2;">Soma total entre Espécie, Pix, Débito e Crédito: R$ {{ number_format($totalSemEstorno, 2, ',', '.') }}</h3>
        <h3  style="margin-bottom: 15px; border-bottom: 2px solid #0097b2;">Soma total de estornos em Pix, Débito e Crédito: R$ {{ number_format($totalComEstorno, 2, ',', '.') }}</h3>
    </div>
@endif

@if($hasPagBank)
    <div class="content">
        <h2>Soma de pagamentos do PagSeguro:</h2>
        <h3  style="margin-bottom: 15px; border-bottom: 2px solid #0097b2;">Soma total entre Pix, Débito e Crédito: R$ {{ number_format($pagBankTotais['totalSemEstorno'], 2, ',', '.') }}</h3>
        <h3  style="margin-bottom: 15px; border-bottom: 2px solid #0097b2;">Soma total de estornos em Pix, Débito e Crédito: R$ {{ number_format($pagBankTotais['totalComEstorno'], 2, ',', '.') }}</h3>
    </div>

    @if($totalSemEstorno > 0  || $totalComEstorno > 0)
        <div class="content">
            <h2>Soma de pagamentos do PagSeguro + Mercado Pago:</h2>
            <h3  style="margin-bottom: 15px; border-bottom: 2px solid #0097b2;">Soma total entre Espécie, Pix, Débito e Crédito: R$ {{ number_format($pagBankTotais['totalSemEstorno'] + $totalSemEstorno, 2, ',', '.') }}</h3>
            <h3  style="margin-bottom: 15px; border-bottom: 2px solid #0097b2;">Soma total de estornos em Pix, Débito e Crédito: R$ {{ number_format($pagBankTotais['totalComEstorno'] + $totalComEstorno, 2, ',', '.') }}</h3>
        </div>
    @endif
@endif

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
    <h3>Todos os direitos reservados &copy; PIXcoin 2025</h3>
</div>
</body>
</html>
