# Retrospectiva — R02 (Revisão proponente/publicação)

> Registro da rodada — fechada em 2026-09-22. Épica #7 (segunda rodada). Variante Condensed.

## O que funcionou
- A onda cresceu dentro da rodada sem perder rastreabilidade: as duas novas lógicas (#71/#72) nasceram do uso real (debug do edital 65 do próprio dono) e entraram com dedup, issue e veredito — o fluxo absorveu incremento sem cerimônia.
- Os overrides foram registrados ANTES de aplicados (register-then-act) — a reversão do "sempre disponível" e o snapshot exigido estão na épica com as palavras do dono.
- O E2E dirigido pegou 5 bugs reais na cadeia da #66 (gate do resultsPublished, timeline não montava com preliminar, phaseType do EMC como objeto, snapshot atrás do gate de detalhamento, valor final fora do gate) — nenhum deles visível por simulação.

## Sinais de processo
- **PHP opcache no dev**: mudanças em `template.php` exigem restart do web, não só rebuild de assets — custou 1 falso "ainda não corrigido" (a review viu código antigo servido). Lição: rebuild + restart sempre em mudanças PHP de componente.
- **Review de layout em 5 ciclos (#65)**: lado a lado, centralização, largura plena (container preso na col-6), rótulo do despublicar, reversão do sempre-disponível — a especificação visual nas issues deveria nascer com medidas/padrão de referência explícitos.
- **Sincronização automática da inscrição de recurso** (descoberta no E2E do #71): a publicação do preliminar sincroniza a inscrição para a fase de recurso — comportamento do OpportunityPhases que não estava documentado nas jornadas; agora refletido na J4.5.
- **Gates em cadeia**: o mesmo gate canônico (`areRegistrationResultsPublished`) é consultado em 4 lugares (tema, timeline, componente, servidor) com formatos diferentes (boolean, coluna, metadata serializada como string) — cada elo precisou do seu fix. Candidato a consolidação futura.
- Suíte de testes vermelha na base (deprecations PHP 8.4) — continua da R01, pendente de rodada de higiene.

## Pendências declaradas (follow-ups, fora da rodada)
- CA-4 parcial na UI (prazo/correction_type/visibilidade) — segue como na R01.
- Reabertura pós-envio (CA-13 parcial) — segue como na R01.
- Link "Expandir" do bloco "Resposta do recurso" navega para a página pública (pré-existente, observado na R01).
- Consolidação do gate de publicação (4 elos com formatos distintos).
