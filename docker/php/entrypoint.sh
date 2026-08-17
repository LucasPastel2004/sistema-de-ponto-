#!/bin/sh
set -e

# ─── Entrypoint para Queue Worker e Scheduler ──────────────────────────
# Aguarda o banco de dados estar pronto e as migrations executadas antes
# de iniciar o processo. Evita loop de reinicialização no primeiro setup.

echo "[entrypoint] Aguardando banco de dados..."

MAX_RETRIES=30
RETRY_COUNT=0

# Aguarda o PostgreSQL aceitar conexões
until php artisan db:monitor --databases=pgsql 2>/dev/null || [ $RETRY_COUNT -eq $MAX_RETRIES ]; do
    RETRY_COUNT=$((RETRY_COUNT + 1))
    echo "[entrypoint] PostgreSQL não disponível (tentativa $RETRY_COUNT/$MAX_RETRIES). Aguardando 2s..."
    sleep 2
done

if [ $RETRY_COUNT -eq $MAX_RETRIES ]; then
    echo "[entrypoint] ERRO: Timeout ao aguardar PostgreSQL. Abortando."
    exit 1
fi

echo "[entrypoint] PostgreSQL disponível."

# Aguarda até que a tabela de migrations exista (indica que o setup foi feito)
echo "[entrypoint] Verificando se migrations foram executadas..."

RETRY_COUNT=0
until php artisan migrate:status 2>/dev/null | grep -q "Ran" || [ $RETRY_COUNT -eq $MAX_RETRIES ]; do
    RETRY_COUNT=$((RETRY_COUNT + 1))
    echo "[entrypoint] Migrations ainda não executadas (tentativa $RETRY_COUNT/$MAX_RETRIES). Aguardando 5s..."
    sleep 5
done

if [ $RETRY_COUNT -eq $MAX_RETRIES ]; then
    echo "[entrypoint] AVISO: Migrations não encontradas após timeout. Iniciando mesmo assim..."
fi

echo "[entrypoint] Sistema pronto. Iniciando: $@"

# Executa o comando passado (queue:work ou schedule:run)
exec "$@"
