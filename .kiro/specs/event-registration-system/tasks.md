Event# Implementation Plan

- [x] 1. Configurar projeto Laravel e dependências
  - Instalar Laravel 12 com MySQL
  - Instalar Filament 4.x via composer
  - Configurar variáveis de ambiente (.env)
  - Instalar Mercado Pago SDK
  - _Requirements: 10.1, 10.2_

- [x] 2. Criar migrations e models base
  - [x] 2.1 Criar migration e model User com role
    - Adicionar coluna 'role' (enum: admin, user) na tabela users
    - Implementar método isAdmin() no model User
    - _Requirements: 10.1, 10.2_
  
  - [x] 2.2 Criar migration e model Event
    - Criar tabela events (name, description, event_date, is_active)
    - Implementar relationships e scopes no model
    - _Requirements: 1.1, 1.2_
  
  - [x] 2.3 Criar migration e model PaymentBatch
    - Criar tabela payment_batches (event_id, price, start_date, end_date)
    - Implementar método isActive() e relationship com Event
    - _Requirements: 2.1, 2.2, 2.3_
  
  - [x] 2.4 Criar migration e model Package
    - Criar tabela packages (package_number, user_id, status, total_amount, payment_method, payment_id)
    - Implementar método generatePackageNumber()
    - _Requirements: 5.4, 6.4, 7.4_
  
  - [x] 2.5 Criar migration e model Registration
    - Criar tabela registrations (package_id, event_id, participant_name, participant_email, participant_phone, participant_data, price_paid)
    - Implementar relationships com Package e Event
    - _Requirements: 5.2, 6.3_

- [x] 3. Configurar autenticação e autorização Filament
  - [x] 3.1 Configurar Filament Panel com autenticação
    - Criar painel Filament em /admin
    - Configurar autenticação usando User model
    - _Requirements: 10.1, 10.3_
  
  - [x] 3.2 Criar Policies para autorização
    - Criar EventPolicy (apenas admin pode gerenciar)
    - Criar RegistrationPolicy (admin vê tudo, user vê próprias)
    - Criar PackagePolicy (admin vê tudo, user vê próprios)
    - Registrar policies no AuthServiceProvider
    - _Requirements: 10.2, 10.3, 10.4, 10.5_

- [x] 4. Implementar Filament Resources para Admin
  - [x] 4.1 Criar EventResource
    - Criar resource com formulário (name, description, event_date, is_active)
    - Configurar tabela com colunas e filtros
    - Adicionar validação canViewAny() para apenas admin
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5_
  
  - [x] 4.2 Criar PaymentBatchResource como Relation Manager
    - Criar relation manager para Event
    - Implementar formulário (price, start_date, end_date)
    - Adicionar validação de sobreposição de datas
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5_
  
  - [x] 4.3 Criar RegistrationResource
    - Criar resource com tabela de todas as inscrições
    - Adicionar filtros (evento, status, data, pacote)
    - Implementar formulário de edição
    - Adicionar ações em massa (exportar)
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5_
  
  - [x] 4.4 Criar PackageResource
    - Criar resource com tabela de pacotes
    - Adicionar filtros (status, usuário, data)
    - Implementar visualização de detalhes com inscrições
    - _Requirements: 3.5, 9.4_

- [x] 5. Implementar Services Layer
  - [x] 5.1 Criar EventService
    - Implementar createEvent(), updateEvent()
    - Implementar getActiveEvents()
    - Implementar getCurrentPrice() que busca lote ativo
    - _Requirements: 1.1, 1.2, 4.1, 4.4_
  
  - [x] 5.2 Criar PaymentBatchService
    - Implementar createBatch(), updateBatch()
    - Implementar getActiveBatch()
    - Implementar validateBatchDates() para evitar sobreposição
    - _Requirements: 2.1, 2.2, 2.3, 2.4_
  
  - [x] 5.3 Criar RegistrationService
    - Implementar createPackage() que gera número único
    - Implementar addRegistrationToPackage()
    - Implementar calculatePackageTotal()
    - Implementar updatePackageStatus()
    - _Requirements: 5.4, 5.5, 6.3, 6.4, 6.5_
  
  - [x] 5.4 Criar PaymentService
    - Implementar createPaymentPreference() para Mercado Pago
    - Implementar processPaymentCallback()
    - Implementar confirmPayment() que atualiza status
    - _Requirements: 7.2, 7.3, 7.4, 7.5, 8.1, 8.2_
  
  - [x] 5.5 Criar NotificationService
    - Implementar sendConfirmationEmail()
    - Criar template de email com detalhes do pacote
    - _Requirements: 8.3, 8.4, 8.5_

- [x] 6. Implementar Filament Pages para Usuários
  - [x] 6.1 Criar AvailableEventsPage
    - Criar página Filament listando eventos disponíveis
    - Exibir cards com informações e preço atual
    - Adicionar botão "Inscrever-se" que redireciona para RegistrationPage
    - Configurar visibilidade para usuários autenticados
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5_
  
  - [x] 6.2 Criar RegistrationPage
    - Criar página com formulário de inscrição
    - Implementar área de inscritos adicionados (lista inferior)
    - Adicionar action para adicionar mais inscrições
    - Implementar botão "Prosseguir para Pagamento"
    - Integrar com RegistrationService
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 6.1, 6.2, 6.3_
  
  - [x] 6.3 Criar MyRegistrationsPage
    - Criar página dashboard do usuário
    - Listar pacotes do usuário autenticado
    - Implementar modal de detalhes ao clicar no pacote
    - Adicionar filtros por status e evento
    - _Requirements: 9.1, 9.2, 9.3, 9.4, 9.5_
  
  - [x] 6.4 Criar PaymentPage
    - Criar página de seleção de método (PIX/Cartão)
    - Exibir resumo do pacote e valor total
    - Implementar botão que chama PaymentService
    - Redirecionar para Mercado Pago
    - _Requirements: 7.1, 7.2, 7.3, 7.4_

- [x] 7. Implementar Filament Actions e Modals
  - [x] 7.1 Criar CreateRegistrationAction
    - Criar action modal para adicionar inscrição
    - Implementar formulário com validação
    - Integrar com RegistrationService
    - _Requirements: 5.2, 5.3, 6.2_
  
  - [x] 7.2 Criar ViewPackageDetailsAction
    - Criar action modal para exibir detalhes do pacote
    - Listar todas as inscrições do pacote
    - Exibir status e informações do evento
    - _Requirements: 9.3, 9.4, 9.5_

- [x] 8. Implementar integração com Mercado Pago
  - [x] 8.1 Configurar SDK do Mercado Pago
    - Adicionar credenciais no .env
    - Criar config/mercadopago.php
    - _Requirements: 7.3_
  
  - [x] 8.2 Criar PaymentController para callbacks
    - Implementar método callback() para webhook
    - Validar assinatura do Mercado Pago
    - Chamar PaymentService.processPaymentCallback()
    - _Requirements: 7.4, 7.5, 8.1, 8.2_
  
  - [x] 8.3 Implementar fluxo de redirecionamento
    - Criar rota para processar pagamento
    - Gerar preferência de pagamento no Mercado Pago
    - Redirecionar usuário para checkout
    - _Requirements: 7.3, 7.4, 7.5_

- [x] 9. Implementar sistema de notificações
  - [x] 9.1 Criar Mailable para confirmação
    - Criar ConfirmationMail com template
    - Incluir detalhes do pacote e inscrições
    - Incluir número do pacote e informações do evento
    - _Requirements: 8.3, 8.4, 8.5_
  
  - [x] 9.2 Criar Job para envio de email
    - Criar SendConfirmationEmailJob
    - Implementar queue para processamento assíncrono
    - _Requirements: 8.3_
  
  - [x] 9.3 Integrar notificações no fluxo de pagamento
    - Disparar job após confirmação de pagamento
    - Adicionar tratamento de erros de envio
    - _Requirements: 8.1, 8.2, 8.3_

- [x] 10. Configurar navegação e menu Filament
  - [x] 10.1 Configurar menu para Admin
    - Adicionar EventResource ao menu
    - Adicionar RegistrationResource ao menu
    - Adicionar PackageResource ao menu
    - Configurar ícones e ordem
    - _Requirements: 10.2, 10.3_
  
  - [x] 10.2 Configurar menu para Usuários
    - Adicionar MyRegistrationsPage ao menu
    - Adicionar AvailableEventsPage ao menu
    - Ocultar resources de admin
    - _Requirements: 10.4_

- [x] 11. Implementar validações e tratamento de erros
  - [x] 11.1 Adicionar validações nos formulários
    - Validar dados de evento (name, description, event_date)
    - Validar dados de lote (price, dates, sobreposição)
    - Validar dados de inscrição (participant_name, email, phone)
    - _Requirements: 1.1, 2.2, 5.2_
  
  - [x] 11.2 Criar exceptions customizadas
    - Criar PaymentException
    - Criar BatchOverlapException
    - Criar InvalidPackageStateException
    - _Requirements: 2.3, 7.4_
  
  - [x] 11.3 Implementar tratamento de erros de pagamento
    - Adicionar try-catch em PaymentService
    - Logar erros de pagamento
    - Exibir notificações amigáveis ao usuário
    - _Requirements: 7.4, 8.1_

- [x] 12. Adicionar índices e otimizações no banco
  - Criar índices em users.email, packages.package_number
  - Criar índices em packages.user_id, registrations.package_id
  - Criar índices em registrations.event_id, payment_batches.event_id
  - _Requirements: 3.2, 9.1_

- [x] 13. Criar seeders para dados iniciais
  - Criar seeder para usuário admin
  - Criar seeder para eventos de exemplo
  - Criar seeder para lotes de pagamento
  - _Requirements: 10.1, 1.1, 2.1_

- [ ]* 14. Implementar testes
  - [ ]* 14.1 Criar testes unitários para models
    - Testar Package::generatePackageNumber()
    - Testar Package::calculateTotal()
    - Testar PaymentBatch::isActive()
    - _Requirements: 5.4, 6.5, 2.4_
  
  - [ ]* 14.2 Criar testes de feature para fluxo de inscrição
    - Testar criação de inscrição individual
    - Testar adição de múltiplas inscrições
    - Testar geração de pacote com número único
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 6.1, 6.2, 6.3_
  
  - [ ]* 14.3 Criar testes de integração para pagamento
    - Mockar API do Mercado Pago
    - Testar criação de preferência
    - Testar processamento de callback
    - Testar atualização de status
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 8.1, 8.2_
