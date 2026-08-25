<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-mail Verificado</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-image: linear-gradient(135deg, #00152b, #003366, #00509E, #003366, #00152b);
            background-size: 200% 200%;
            animation: gradientPulse 12s ease-in-out infinite;
            color: #f8fafc;
            text-align: center;
        }

        @keyframes gradientPulse {
            0% { background-position: 0% 0%; }
            50% { background-position: 100% 100%; }
            100% { background-position: 0% 0%; }
        }

        .card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-top: 5px solid #F15A24;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
            border-radius: 1.5rem;
            padding: 3rem;
            max-width: 500px;
            width: 90%;
        }

        .icon {
            font-size: 4rem;
            color: #10b981; /* Emerald 500 */
            margin-bottom: 1rem;
        }

        h1 {
            margin: 0 0 1rem;
            font-size: 1.8rem;
        }

        p {
            margin: 0;
            color: #cbd5e1;
            font-size: 1.1rem;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">✓</div>
        <h1>E-mail verificado!</h1>
        <p>Sua conta foi validada com sucesso.</p>
        <p style="margin-top: 1.5rem; font-weight: bold; color: #f8fafc;">Você já pode fechar esta aba agora.</p>
    </div>
</body>
</html>
