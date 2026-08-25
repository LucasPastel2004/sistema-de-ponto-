# Tasks — Correção de Bugs Sistema de Ponto ✅

## 🔴 CRÍTICO
- [x] BUG #1 — EspelhoPontoResource chave errada ('colaborador_id' → 'colaborador')
- [x] BUG #2 — Cache do espelho não invalida ao registrar ponto
- [x] BUG #3 — CreateColaborador sem transação DB
- [x] BUG #4 — BaterPontoWidget lógica binária ignora IntervaloInicio/IntervaloFim
- [x] BUG #5 — JustificativaController index() filtra Pendente hardcoded

## 🟠 ALTO
- [x] BUG #6 — validarHorario não trata IntervaloInicio/IntervaloFim
- [x] BUG #7 — N+1 query em exigeGeolocalizacao + validarGeolocalizacao
- [x] BUG #8 — FortifyServiceProvider só autentica por email
- [x] BUG #9 — AlertasOmissaoWidget sem filtro de empresa
- [x] BUG #10 — gerarResumoMensal usa 22 dias fixos
- [x] BUG #11 — Bulk action justificativa bypassa Policy

## 🟡 MÉDIO
- [x] BUG #12 — BaterPontoWidget sem declare(strict_types=1)
- [x] BUG #13 — Matricula race condition (max id)
- [x] BUG #14 — calcularIntervalo retorno tipo inconsistente
- [x] BUG #15 — StorePontoRequest não valida colaborador ativo
- [x] BUG #16 — gerarPdf() view pode não existir
- [ ] BUG #17 — CPF hash sem salt de empresa (aceito: constraint composta já protege)

## 🔵 BAIXO
- [x] BUG #18 — docker-compose portas expostas (127.0.0.1)
- [x] BUG #19 — UpdateJustificativaRequest campo status indevido
- [x] BUG #20 — NotificacaoGestorService TODO → implementado + 3 Notification classes
- [ ] BUG #21 — custom.css — VERIFICADO: arquivo já existe em public/css/custom.css ✅

## Status Final
- Total corrigidos: 19/19
- PHP Syntax: ✅ Sem erros
- App rodando: ✅ Laravel 11.55.1 / Filament v3.3.54
