<!DOCTYPE html>
<html>
<head>
    <title>Redefinir Senha - PIXCOIN</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            padding: 30px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #d9534f;
            margin-bottom: 30px;
        }

        .form-group {
            position: relative;
            margin-bottom: 30px;
            padding-top: 15px;
        }

        input {
            position: relative;
            font-weight: 500;
            color: #1d1d1d;
            width: 100%;
            box-sizing: border-box;
            letter-spacing: 1px;
            border: 0;
            padding: 4px 0;
            border-bottom: 1px solid #000;
            background-color: transparent;
            font-size: 16px;
        }

        input:not(:placeholder-shown) ~ label,
        input:focus ~ label {
            top: -16px;
            color: #929292;
            z-index: 1;
            opacity: 1;
            transition: 0.3s;
        }

        label {
            position: absolute;
            font-weight: 500;
            left: 0;
            top: 0;
            z-index: -1;
            letter-spacing: 0.5px;
            opacity: 0;
            transition: all .2s ease;
            font-size: 16px;
        }

        .bar {
            position: relative;
            display: inherit;
            top: -2px;
            width: 0;
            height: 3px;
            background-color: #d9534f;
            transition: 0.4s;
        }

        input::placeholder {
            color: #929292;
            opacity: 1;
            transition: 0.2s;
        }

        input:focus::placeholder {
            opacity: 0;
            transition: 0.2s;
        }

        input:focus {
            outline: none;
        }

        input:focus ~ .bar {
            width: 100%;
            transition: 0.4s;
        }

        input:focus ~ label {
            top: -16px;
            color: #d9534f;
            opacity: 1;
            z-index: 1;
            animation: bounceUp .4s forwards;
        }

        @keyframes bounceUp {
            from {
                transform: scale3d(1, 1, 1);
            }
            50% {
                transform: scale3d(1.1618, 1.1618, 1.1618);
            }
            to {
                transform: scale3d(1, 1, 1);
            }
        }

        .button-ui {
            padding: 16px 25px;
            max-height: 50px;
            margin-top: 2rem;
            min-width: 175px;
            color: #fff;
            border-radius: 50px;
            cursor: pointer;
            text-transform: uppercase;
            border: 0 none;
            outline: none;
            transition: 0.3s all;
            background-color: #d9534f;
            display: block;
            margin: 30px auto 0;
            font-weight: bold;
        }

        .button-ui:hover {
            opacity: 0.7;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 0.9em;
            color: #666;
        }

        .logo {
            text-align: center;
            margin-bottom: 20px;
            color: #0275d8;
            font-weight: bold;
            font-size: 1.5em;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="logo">PIXCOIN</div>
    <h2>Redefinir Senha</h2>

    <form method="POST" action="{{ url('/reset-password') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="form-group">
            <input type="password" name="password" id="password" placeholder="Nova Senha" required>
            <span class="bar"></span>
            <label for="password">Nova Senha</label>
        </div>

        <div class="form-group">
            <input placeholder="Confirmar Senha" type="password" name="password_confirmation" id="password_confirmation" required>
            <span class="bar"></span>
            <label for="password_confirmation">Confirmar Senha</label>
        </div>

        <button type="submit" class="button-ui">Salvar Nova Senha</button>
    </form>

    <div class="footer">
        <p>Este link expirará em 1 hora por motivos de segurança.</p>
        <p>© {{ date('Y') }} PIXCOIN. Todos os direitos reservados.</p>
    </div>
</div>
</body>
</html>
