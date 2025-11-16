<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmação de Inscrição</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 20px;
            border: 1px solid #ddd;
            border-top: none;
        }
        .package-info {
            background-color: white;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #4CAF50;
        }
        .registration-item {
            background-color: white;
            padding: 15px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .registration-item h4 {
            margin-top: 0;
            color: #4CAF50;
        }
        .info-row {
            margin: 5px 0;
        }
        .label {
            font-weight: bold;
            color: #666;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 12px;
        }
        .total {
            font-size: 18px;
            font-weight: bold;
            color: #4CAF50;
            text-align: right;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Confirmação de Inscrição</h1>
    </div>
    
    <div class="content">
        <p>Olá, <strong>{{ $package->user->name }}</strong>!</p>
        
        <p>Sua inscrição foi confirmada com sucesso! Abaixo estão os detalhes do seu pacote:</p>
        
        <div class="package-info">
            <div class="info-row">
                <span class="label">Número do Pacote:</span> {{ $package->package_number }}
            </div>
            <div class="info-row">
                <span class="label">Status:</span> {{ ucfirst($package->status) }}
            </div>
            <div class="info-row">
                <span class="label">Método de Pagamento:</span> {{ strtoupper($package->payment_method) }}
            </div>
            @if($package->payment_id)
            <div class="info-row">
                <span class="label">ID do Pagamento:</span> {{ $package->payment_id }}
            </div>
            @endif
        </div>

        <h3>Inscrições Confirmadas:</h3>
        
        @foreach($package->registrations as $registration)
        <div class="registration-item">
            <h4>{{ $registration->event->name }}</h4>
            
            <div class="info-row">
                <span class="label">Participante:</span> {{ $registration->participant_name }}
            </div>
            <div class="info-row">
                <span class="label">Email:</span> {{ $registration->participant_email }}
            </div>
            <div class="info-row">
                <span class="label">Telefone:</span> {{ $registration->participant_phone }}
            </div>
            <div class="info-row">
                <span class="label">Data do Evento:</span> {{ $registration->event->event_date->format('d/m/Y H:i') }}
            </div>
            <div class="info-row">
                <span class="label">Valor Pago:</span> R$ {{ number_format($registration->price_paid, 2, ',', '.') }}
            </div>
            
            @if($registration->participant_data)
            <div class="info-row">
                <span class="label">Informações Adicionais:</span><br>
                {{ $registration->participant_data }}
            </div>
            @endif
        </div>
        @endforeach
        
        <div class="total">
            Total: R$ {{ number_format($package->total_amount, 2, ',', '.') }}
        </div>
        
        <p style="margin-top: 30px;">
            Guarde este email como comprovante de sua inscrição. 
            Em caso de dúvidas, entre em contato conosco informando o número do pacote.
        </p>
    </div>
    
    <div class="footer">
        <p>Este é um email automático, por favor não responda.</p>
        <p>&copy; {{ date('Y') }} Sistema de Inscrições. Todos os direitos reservados.</p>
    </div>
</body>
</html>
