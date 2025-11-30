# Requirements Document

## Introduction

Este documento descreve os requisitos para um sistema de inscrições para eventos desenvolvido com Laravel 12 e Filament 4.x. O sistema permite que administradores gerenciem eventos e lotes de pagamento, enquanto usuários podem realizar inscrições individuais ou em pacotes, com pagamento via Mercado Pago.

## Glossary

- **Sistema**: O sistema de inscrições para eventos
- **Admin**: Usuário administrador com permissões completas de gerenciamento
- **Usuário**: Usuário final que realiza inscrições em eventos
- **Evento**: Atividade ou acontecimento para o qual usuários podem se inscrever
- **Lote de Pagamento**: Período com valor específico para inscrições em um evento
- **Inscrição**: Registro de participação de um usuário em um evento
- **Pacote**: Conjunto de inscrições agrupadas sob um identificador único
- **Rascunho**: Estado inicial de um pacote antes da confirmação de pagamento
- **Mercado Pago**: Gateway de pagamento integrado ao sistema

## Requirements

### Requirement 1

**User Story:** Como Admin, eu quero cadastrar eventos no sistema, para que usuários possam visualizar e se inscrever neles

#### Acceptance Criteria

1. THE Sistema SHALL permitir que Admin crie um novo evento com nome, descrição, data e informações relevantes
2. THE Sistema SHALL armazenar os dados do evento no banco de dados MySQL
3. THE Sistema SHALL exibir eventos cadastrados na interface administrativa do Filament
4. THE Sistema SHALL permitir que Admin edite informações de eventos existentes
5. THE Sistema SHALL permitir que Admin desative ou remova eventos

### Requirement 2

**User Story:** Como Admin, eu quero adicionar lotes de pagamento aos eventos, para que eu possa gerenciar preços por período

#### Acceptance Criteria

1. WHEN Admin acessa um evento, THE Sistema SHALL exibir opção para adicionar lotes de pagamento
2. THE Sistema SHALL permitir que Admin defina data de início, data de fim e valor para cada lote
3. THE Sistema SHALL validar que as datas dos lotes não se sobreponham
4. THE Sistema SHALL aplicar o valor do lote ativo baseado na data atual da inscrição
5. THE Sistema SHALL permitir que Admin edite ou remova lotes de pagamento existentes

### Requirement 3

**User Story:** Como Admin, eu quero visualizar e filtrar todas as inscrições, para que eu possa gerenciar os participantes dos eventos

#### Acceptance Criteria

1. THE Sistema SHALL exibir lista completa de inscrições na interface administrativa
2. THE Sistema SHALL permitir que Admin filtre inscrições por evento, status, data ou pacote
3. THE Sistema SHALL exibir informações detalhadas de cada inscrição incluindo dados do usuário
4. THE Sistema SHALL permitir que Admin edite dados de inscrições quando necessário
5. THE Sistema SHALL exibir o número do pacote associado a cada inscrição

### Requirement 4

**User Story:** Como Usuário, eu quero visualizar eventos disponíveis, para que eu possa escolher em quais me inscrever

#### Acceptance Criteria

1. WHEN Usuário acessa o sistema, THE Sistema SHALL exibir lista de eventos disponíveis
2. THE Sistema SHALL exibir informações do evento incluindo nome, descrição, data e valor atual
3. THE Sistema SHALL exibir apenas eventos ativos e com inscrições abertas
4. THE Sistema SHALL exibir o valor do lote de pagamento vigente para cada evento
5. WHEN Usuário clica em um evento, THE Sistema SHALL exibir opção de inscrever-se

### Requirement 5

**User Story:** Como Usuário, eu quero realizar inscrição individual em um evento, para que eu possa participar

#### Acceptance Criteria

1. WHEN Usuário clica em inscrever-se, THE Sistema SHALL exibir formulário de inscrição
2. THE Sistema SHALL permitir que Usuário preencha dados pessoais necessários para a inscrição
3. THE Sistema SHALL validar todos os campos obrigatórios do formulário
4. WHEN Usuário submete inscrição individual, THE Sistema SHALL criar pacote com identificador único
5. THE Sistema SHALL adicionar a inscrição ao pacote e marcar como rascunho

### Requirement 6

**User Story:** Como Usuário, eu quero adicionar múltiplas inscrições antes de pagar, para que eu possa inscrever várias pessoas de uma vez

#### Acceptance Criteria

1. WHEN Usuário está no fluxo de inscrição, THE Sistema SHALL exibir área para adicionar novas inscrições
2. THE Sistema SHALL exibir lista de inscritos adicionados abaixo do formulário
3. THE Sistema SHALL permitir que Usuário adicione quantidade ilimitada de inscrições ao pacote
4. THE Sistema SHALL associar todas as inscrições ao mesmo número de pacote único
5. THE Sistema SHALL manter todas as inscrições em estado de rascunho até confirmação de pagamento

### Requirement 7

**User Story:** Como Usuário, eu quero escolher método de pagamento, para que eu possa finalizar minhas inscrições

#### Acceptance Criteria

1. WHEN Usuário clica em prosseguir para pagamento, THE Sistema SHALL exibir opções PIX e cartão de crédito
2. WHEN Usuário seleciona método de pagamento, THE Sistema SHALL calcular valor total do pacote
3. THE Sistema SHALL integrar com API do Mercado Pago para processar pagamento
4. THE Sistema SHALL redirecionar Usuário para interface de pagamento do Mercado Pago
5. THE Sistema SHALL manter referência do pacote durante processo de pagamento

### Requirement 8

**User Story:** Como Usuário, eu quero receber confirmação por email após pagamento, para que eu tenha comprovante das minhas inscrições

#### Acceptance Criteria

1. WHEN pagamento é confirmado pelo Mercado Pago, THE Sistema SHALL atualizar status do pacote para confirmado
2. THE Sistema SHALL atualizar status de todas as inscrições do pacote para confirmado
3. THE Sistema SHALL enviar email de confirmação para endereço do Usuário
4. THE Sistema SHALL incluir no email informações de todas as inscrições do pacote
5. THE Sistema SHALL incluir no email número do pacote e detalhes do evento

### Requirement 9

**User Story:** Como Usuário, eu quero visualizar minhas inscrições na tela inicial, para que eu possa acompanhar meus eventos

#### Acceptance Criteria

1. WHEN Usuário acessa tela inicial, THE Sistema SHALL exibir lista de pacotes de inscrições do Usuário
2. THE Sistema SHALL exibir informações resumidas de cada pacote incluindo evento e status
3. WHEN Usuário clica em um pacote, THE Sistema SHALL abrir modal com detalhes completos
4. THE Sistema SHALL exibir no modal todas as inscrições do pacote com dados dos inscritos
5. THE Sistema SHALL exibir no modal número do pacote, status de pagamento e informações do evento

### Requirement 10

**User Story:** Como Sistema, eu preciso gerenciar autenticação e autorização, para que apenas usuários autorizados acessem funcionalidades específicas

#### Acceptance Criteria

1. THE Sistema SHALL implementar autenticação de usuários usando Laravel Authentication
2. THE Sistema SHALL diferenciar permissões entre Admin e Usuário comum
3. THE Sistema SHALL restringir acesso ao painel administrativo Filament apenas para Admin
4. THE Sistema SHALL permitir que Usuário acesse apenas suas próprias inscrições
5. THE Sistema SHALL implementar proteção de rotas baseada em roles e permissões
