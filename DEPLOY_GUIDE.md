# Guia de Deploy - Sistema de Inscrições IORG

## Última Atualização: 28/01/2026

Este documento contém todas as informações necessárias para fazer o deploy do sistema em produção.

---

## 📋 Mudanças Recentes Implementadas

### 1. Sistema de Preços Diferenciados (PIX vs Cartão)
- ✅ Adicionados campos `price_pix` e `price_card` na tabela `payment_batches`
- ✅ Migration que migra dados existentes automaticamente
- ✅ Interface admin atualizada para gerenciar ambos os preços
- ✅ Páginas de eventos, inscrição e pagamento mostram ambos os valores
- ✅ Cálculo automático baseado no método de pagamento selecionado
- ✅ Layout responsivo para mobile

### 2. Role "Hotel"
- ✅ Nova role `hotel` para visualização de inscrições confirmadas
- ✅ Página exclusiva mostrando: nome completo, data de nascimento, assembleia
- ✅ Sem acesso a funcionalidades admin ou de usuário comum

### 3. Correção Mercado Pago
- ✅ `notification_url` só é enviada em ambiente de produção (requer HTTPS)
- ✅ Em desenvolvimento local, funciona sem webhook (usa apenas back_urls)

---

## 🚀 Checklist de Deploy em Produção

### 1. Preparação do Servidor

```bash
# Clone o repositório
git clone [URL_DO_REPOSITORIO]
cd inscricoes-iorg

# Instale dependências (SEM dev)
composer install --optimize-autoloader --no-dev

# Copie o .env.example
cp .env.example .env
```

### 2. Configuração do .env

**IMPORTANTE:** Configure estas variáveis no servidor:

```bash
# Aplicação
APP_NAME="Inscrições IORG"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seudominio.com.br  # ⚠️ HTTPS OBRIGATÓRIO

# Banco de Dados
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nome_do_banco_producao
DB_USERNAME=usuario_producao
DB_PASSWORD=senha_segura_aqui

# Mercado Pago - CREDENCIAIS DE PRODUÇÃO
MERCADOPAGO_PUBLIC_KEY=APP_USR_xxxxxxxx
MERCADOPAGO_ACCESS_TOKEN=APP_USR_xxxxxxxx

# Email - Configure SMTP Real
MAIL_MAILER=smtp
MAIL_HOST=smtp.seuservidor.com
MAIL_PORT=587
MAIL_USERNAME=seu_email@dominio.com
MAIL_PASSWORD=senha_email
MAIL_FROM_ADDRESS="noreply@seudominio.com.br"
MAIL_FROM_NAME="${APP_NAME}"

# Queue (importante para emails de confirmação)
QUEUE_CONNECTION=database

# Cache e Session
CACHE_STORE=database
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Log
LOG_CHANNEL=stack
LOG_LEVEL=error
```

### 3. Comandos de Instalação

Execute na ordem:

```bash
# 1. Gerar chave da aplicação
php artisan key:generate

# 2. Link do storage (para logos de eventos)
php artisan storage:link

# 3. Rodar todas as migrations
php artisan migrate --force

# 4. Otimizações de produção
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 5. Permissões corretas
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### 4. Configuração do Servidor Web

#### Nginx (Recomendado)

```nginx
server {
    listen 80;
    server_name seudominio.com.br;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name seudominio.com.br;

    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;

    root /path/to/inscricoes-iorg/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 5. SSL/HTTPS (Obrigatório para Mercado Pago)

```bash
# Instalar Certbot (Let's Encrypt)
sudo apt install certbot python3-certbot-nginx

# Gerar certificado
sudo certbot --nginx -d seudominio.com.br

# Renovação automática já está configurada
```

### 6. Queue Worker (Para Emails)

#### Opção A: Supervisor (Recomendado)

```bash
# Instalar supervisor
sudo apt install supervisor

# Criar arquivo de config
sudo nano /etc/supervisor/conf.d/inscricoes-queue.conf
```

Conteúdo do arquivo:

```ini
[program:inscricoes-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/inscricoes-iorg/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/inscricoes-iorg/storage/logs/queue-worker.log
stopwaitsecs=3600
```

```bash
# Recarregar supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start inscricoes-queue-worker:*
```

#### Opção B: Crontab

```bash
# Editar crontab
crontab -e

# Adicionar linha
* * * * * cd /path/to/inscricoes-iorg && php artisan schedule:run >> /dev/null 2>&1
```

### 7. Configurar Webhook no Mercado Pago

1. Acesse o painel do Mercado Pago: https://www.mercadopago.com.br/developers
2. Vá em "Suas integrações" > Sua aplicação > "Webhooks"
3. Configure:
   - **URL**: `https://seudominio.com.br/payment/callback`
   - **Eventos**: Marque `payment` (approved, rejected, cancelled, etc.)
4. Salve

### 8. Criar Usuários Iniciais

```bash
# Conectar ao servidor via SSH
php artisan tinker

# Criar admin
User::create([
    'name' => 'Admin',
    'email' => 'admin@seudominio.com.br',
    'password' => bcrypt('senha_segura_aqui'),
    'role' => 'admin'
]);

# Criar hotel
User::create([
    'name' => 'Hotel',
    'email' => 'hotel@seudominio.com.br',
    'password' => bcrypt('senha_hotel_aqui'),
    'role' => 'hotel'
]);

exit
```

---

## 🔍 Verificação Pós-Deploy

### Checklist Final

- [ ] Site acessível via HTTPS
- [ ] Login funcionando (admin e hotel)
- [ ] Admin consegue criar eventos
- [ ] Admin consegue criar lotes com preços PIX e Cartão diferentes
- [ ] Usuários conseguem se cadastrar
- [ ] Usuários veem eventos com ambos os preços
- [ ] Processo de inscrição funciona
- [ ] Redirecionamento para Mercado Pago funciona
- [ ] Webhook do Mercado Pago configurado
- [ ] Emails de confirmação sendo enviados
- [ ] Hotel vê apenas inscrições confirmadas
- [ ] Logs sem erros: `tail -f storage/logs/laravel.log`
- [ ] Queue worker rodando: `sudo supervisorctl status`

### Teste de Pagamento

1. Crie um evento com lotes e preços
2. Faça uma inscrição como usuário comum
3. Escolha PIX ou Cartão
4. Verifique se redireciona para Mercado Pago
5. Faça um pagamento de teste
6. Verifique se o webhook atualiza o status
7. Verifique se o email de confirmação foi enviado
8. Login como hotel e veja se a inscrição aparece

---

## 🐛 Troubleshooting

### Erro: "notification_url must be a valid url"
**Causa:** APP_ENV não está como `production` ou APP_URL não é HTTPS
**Solução:**
```bash
# No .env
APP_ENV=production
APP_URL=https://seudominio.com.br

# Limpar cache
php artisan config:clear
php artisan config:cache
```

### Webhook não está chegando
**Causas possíveis:**
1. URL não está acessível publicamente
2. Firewall bloqueando IPs do Mercado Pago
3. SSL inválido
4. Webhook não configurado no painel do MP

**Solução:**
```bash
# Testar URL externamente
curl https://seudominio.com.br/payment/callback

# Ver logs
tail -f storage/logs/laravel.log
```

### Queue não está processando
```bash
# Ver status do supervisor
sudo supervisorctl status

# Reiniciar worker
sudo supervisorctl restart inscricoes-queue-worker:*

# Ver logs do worker
tail -f storage/logs/queue-worker.log
```

### Emails não estão sendo enviados
1. Verifique configurações SMTP no .env
2. Verifique se a queue está rodando
3. Veja jobs pendentes: `php artisan queue:work --once`

### Erro 500 genérico
```bash
# Ver logs detalhados
tail -100 storage/logs/laravel.log

# Se precisar debug temporário
# No .env (APENAS TEMPORÁRIO!)
APP_DEBUG=true

# DEPOIS volte para false!
APP_DEBUG=false
php artisan config:cache
```

---

## 📝 Migrações Específicas Desta Versão

### Migration de Preços (já está no código)

Esta migration será executada automaticamente com `php artisan migrate`:

- Adiciona `price_card` e `price_pix` em `payment_batches`
- Migra dados existentes do campo `price` antigo
- Remove o campo `price` antigo

**IMPORTANTE:** Se já tem dados em produção, esta migration preserva tudo!

### Migration de Role Hotel (já está no código)

- Adiciona valor `hotel` ao enum de roles
- Não afeta usuários existentes

---

## 🔒 Segurança

### Recomendações

1. **Senha forte** para usuário admin
2. **Backup regular** do banco de dados
3. **APP_DEBUG=false** em produção (NUNCA true!)
4. **Firewall** configurado (UFW/iptables)
5. **Fail2ban** para proteção contra brute force
6. **Logs rotacionados** (logrotate)
7. **Monitoramento** (Uptime Robot, etc.)

### Backup do Banco

```bash
# Script de backup diário
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u usuario -p senha nome_banco > /backups/inscricoes_$DATE.sql
find /backups -name "inscricoes_*.sql" -mtime +7 -delete
```

Adicione ao crontab:
```bash
0 2 * * * /path/to/backup-script.sh
```

---

## 📞 Contato e Suporte

Se tiver problemas durante o deploy, verifique:

1. Este documento primeiro
2. Logs: `storage/logs/laravel.log`
3. Logs do servidor: `/var/log/nginx/error.log`
4. Status dos serviços: `sudo supervisorctl status`

---

## 📦 Estrutura de Arquivos Importantes

```
inscricoes-iorg/
├── app/
│   ├── Filament/
│   │   ├── Pages/
│   │   │   ├── AvailableEventsPage.php (lista eventos)
│   │   │   ├── RegistrationPage.php (formulário inscrição)
│   │   │   ├── PaymentPage.php (seleção método)
│   │   │   ├── HotelRegistrationsPage.php (página hotel)
│   │   │   └── Dashboard.php (redireciona por role)
│   │   └── Resources/
│   │       └── EventResource/
│   │           └── RelationManagers/
│   │               └── PaymentBatchesRelationManager.php (gerenciar lotes)
│   ├── Services/
│   │   ├── PaymentService.php (integração MP)
│   │   ├── RegistrationService.php (criar inscrições)
│   │   └── EventService.php (lógica de eventos)
│   └── Models/
│       ├── User.php (roles: admin, user, hotel)
│       ├── Event.php
│       ├── PaymentBatch.php (price_pix, price_card)
│       ├── Registration.php
│       └── Package.php
├── database/
│   └── migrations/
│       ├── 2026_01_28_022443_add_hotel_role_to_users_table.php
│       └── 2026_01_28_023706_add_price_card_and_price_pix_to_payment_batches_table.php
├── resources/views/filament/pages/
│   ├── available-events-page.blade.php
│   ├── registration-page.blade.php
│   ├── payment-page.blade.php
│   └── hotel-registrations-page.blade.php
└── routes/
    └── web.php (rotas de callback do MP)
```

---

## ✅ Checklist Rápido

```bash
# 1. Clone e instale
git clone [repo] && cd inscricoes-iorg
composer install --no-dev --optimize-autoloader

# 2. Configure .env
cp .env.example .env
nano .env  # Configurar variáveis

# 3. Setup Laravel
php artisan key:generate
php artisan storage:link
php artisan migrate --force

# 4. Otimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Permissões
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# 6. SSL
sudo certbot --nginx -d seudominio.com.br

# 7. Queue worker
# (Configurar supervisor conforme seção 6)

# 8. Webhook MP
# (Configurar no painel do Mercado Pago)

# 9. Teste tudo!
```

---

**Última atualização:** 28/01/2026
**Versão Laravel:** 12
**Versão Filament:** 4.0
**Versão PHP:** 8.2+
**Mercado Pago SDK:** v3

Boa sorte com o deploy! 🚀
