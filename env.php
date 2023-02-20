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
