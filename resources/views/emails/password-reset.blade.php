<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Recuperação de Senha</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
<div style="max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
    <h2 style="text-align: center; color: #d9534f;">Recuperação de Senha</h2>
    <p>Prezado(a) <strong>{{ $userName }}</strong>,</p>
    <p>Recebemos uma solicitação para redefinição da senha da sua conta.</p>
    <p>
        Para criar uma nova senha, clique no botão abaixo dentro das próximas 1 hora:
    </p>

    <div style="text-align: center; margin: 25px 0;">
        <a href="{{ $resetLink }}" style="background-color: #d9534f; color: white; padding: 12px 20px; text-decoration: none; border-radius: 4px; font-weight: bold;">
            Redefinir Senha
        </a>
    </div>

    <p>
        Se você não solicitou a alteração de senha, por favor ignore este e-mail ou entre em contato conosco
        através do telefone <a href="tel:+5521979832030" style="color: #0853b2;">(21) 97983-2030</a> para verificar a segurança da sua conta.
    </p>

    <p style="text-align: center; color: #888; font-size: 0.9em;">
        O link de redefinição de senha expirará em 1 hora.
    </p>

    <p style="text-align: right; font-weight: bold;">
        Atenciosamente,<br>
        <span style="color: #0853b2;">PIXCOIN</span>
    </p>

    <hr style="margin: 20px 0; border: none; border-top: 1px solid #ddd;">

    <p style="text-align: center; font-size: 0.8em; color: #666;">
        Este é um e-mail automático. Por favor, não responda a esta mensagem.
    </p>
</div>
</body>
</html>
