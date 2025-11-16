# Filament Actions

Este diretório contém ações reutilizáveis do Filament para o sistema de inscrições.

## CreateRegistrationAction

Action modal para adicionar uma nova inscrição a um pacote.

### Uso

```php
use App\Filament\Actions\CreateRegistrationAction;

// Em uma página Filament
protected function getHeaderActions(): array
{
    return [
        CreateRegistrationAction::make($this->package, $this->event),
    ];
}

// Em um Resource ou Table
public function table(Table $table): Table
{
    return $table
        ->headerActions([
            CreateRegistrationAction::make($package, $event),
        ]);
}
```

### Funcionalidades

- Formulário modal com validação completa
- Campos: nome, email, telefone (com máscara), informações adicionais
- Exibe preço atual do evento na descrição do modal
- Integração automática com RegistrationService
- Notificações de sucesso/erro

## ViewPackageDetailsAction

Action modal para visualizar detalhes completos de um pacote de inscrições.

### Uso

```php
use App\Filament\Actions\ViewPackageDetailsAction;

// Em um Resource Table
public function table(Table $table): Table
{
    return $table
        ->actions([
            ViewPackageDetailsAction::make(),
        ]);
}
```

### Funcionalidades

- Modal slideOver com detalhes do pacote
- Lista todas as inscrições do pacote
- Exibe status do pagamento com descrição contextual
- Mostra informações do evento
- Carrega automaticamente relacionamentos necessários

## Requisitos Atendidos

- **Requirement 5.2, 5.3**: Validação de formulário e criação de inscrição
- **Requirement 6.2**: Associação de inscrições ao pacote
- **Requirement 9.3, 9.4, 9.5**: Visualização de detalhes do pacote e inscrições
