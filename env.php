<?php

/**
 * Variáveis de ambiente
 * PHP version 8.1.2
 *
 * @category Environment
 * @package  Root
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */

// Servidor PHP
putenv("PHP_DEBUG=true"); // Modo depuração
putenv("PHP_ROOT_DIR=" . __DIR__ . "/"); // Caminho até a raiz do projeto
putenv("PHP_SESSION_NAME=HcodeStore"); // Nome da sessão para armazenar no cookie do navegador
putenv("SECRET_KEY=CursoCompletoPHP"); // Chave para criptografia
putenv("SECRET_IV=SolusComputacao"); // Contra chave para criptografia
date_default_timezone_set("America/Sao_Paulo"); // Localização para ajustar o horário


// Banco de dados
putenv("MYSQL_HOSTNAME=192.168.15.33"); // Servidor SQL
putenv("MYSQL_DRIVER=mysql"); // Driver SQL
putenv("MYSQL_SCHEMA=hcode_store"); // Nome do banco de dados
putenv("MYSQL_USERNAME=pi"); // Nome do usuário
putenv("MYSQL_PASSWORD=mjolnir"); // Senha de acesso
putenv("MYSQL_PORT=3306"); // Porta do servidor SQL


// Servidor de e-mail
putenv("SMTP_EMAIL_HOSTNAME=smtp.hostinger.com"); // Servidor de e-mail de saída (SMTP)
putenv("SMTP_EMAIL_ADDRESS=hcodestore@luizamichi.com.br"); // Endereço de e-mail
putenv("SMTP_EMAIL_PASSWORD=HcodeStore"); // Senha do e-mail
putenv("SMTP_EMAIL_NAME_FROM=Hcode Store"); // Nome que vai junto à mensagem
putenv("SMTP_EMAIL_REPLY="); // E-mail que será replicada a mensagem
putenv("SMTP_DEBUG=0"); // 0: desligado, 1: mensagens do cliente, 2: mensagens do servidor, 3: mensagens da conexão, 4: mensagens de baixo nível
putenv("SMTP_PORT=587"); // 465: SMTP com TLS implícito (SMTPS RFC8314), 587: SMTP + STARTTLS
putenv("SMTP_SECURE=tls"); // SMTPS: TLS implícito na porta 465, STARTTLS: TLS explícito na porta 587
putenv("SMTP_AUTH=true"); // Autenticação SMTP


// Empresa
putenv("ENTERPRISE_NAME=Hcode Treinamentos"); // Razão social
putenv("ENTERPRISE_CNPJ=29642267000164"); // CNPJ
putenv("ENTERPRISE_ADDRESS=Rua José Roque Salton, 250"); // Endereço
putenv("ENTERPRISE_ZIP_CODE=86047622"); // CEP
putenv("ENTERPRISE_CITY=Londrina"); // Cidade
putenv("ENTERPRISE_FU=PR"); // Estado (UF)
putenv("ENTERPRISE_LOGO=/res/multiShop/img/hcode.png"); // Logotipo
putenv("ENTERPRISE_PHONE=44998665521"); // Telefone
putenv("ENTERPRISE_MAIL=contato@luizamichi.com.br"); // E-mail


// Serviço para cálculo do envio de encomendas pelos Correios
putenv("COURIER_COMPANY_CODE="); // Código administrativo junto à ECT
putenv("COURIER_PASSWORD="); // Senha para acesso ao serviço
putenv("COURIER_SERVICE_CODE=40010"); // Código do serviço (40010: SEDEX Varejo, 40045: SEDEX a Cobrar Varejo, 40215: SEDEX 10 Varejo, 40290: SEDEX Hoje Varejo, 41106: PAC Varejo)
putenv("COURIER_ORIGIN_ZIP_CODE=86047622"); // CEP de origem
putenv("COURIER_ORDER_FORMAT=1"); // Formato da encomenda (1: Formato caixa/pacote, 2: Formato rolo/prisma, 3: Envelope)
putenv("COURIER_OWN_HAND=S"); // Serviço adicional mão própria
putenv("COURIER_ORDER_RECEIPT_NOTICE=S"); // Aviso de recebimento (S: Sim, N: Não)
