<?php

/**
 * PHP version 8.1.2
 *
 * @category GlobalClass
 * @package  Amichi
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 */

namespace Amichi;

/**
 * Classe que define funções mágicas para serem herdadas em outras classes
 *
 * @category GlobalClass
 * @package  Amichi
 * @author   Luiz Amichi <eu@luizamichi.com.br>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/luizamichi/hcode-store
 * @abstract
 */
abstract class Model
{
    /**
     * Propriedade
     *
     * @var array<string,mixed> $values Valores auxiliares
     */
    protected array $values = [];


    /**
     * Propriedade
     *
     * @var array<string,mixed> $_cache Valores em cache para evitar consultas repetidas ao banco
     */
    private static array $_cache = [];


    /**
     * Escreve dados nas propriedades inacessíveis
     *
     * @param string $name  Nome da variável
     * @param mixed  $value Valor da variável
     *
     * @return void
     */
    public function __set(string $name, mixed $value): void
    {
        $this->values[$name] = $value;
    }


    /**
     * Lê dados das propriedades inacessíveis
     *
     * @param string $name Nome da variável
     *
     * @throws \InvalidArgumentException Se a propriedade não existir
     *
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        if (in_array($name, array_keys($this->values))) {
            return $this->values[$name];
        }

        throw (new HttpException("Atributo \"$name\" não existe.", 500))->json();
    }


    /**
     * Trata métodos inacessíveis no contexto do objeto
     *
     * @param string       $name      Nome da função
     * @param array<mixed> $arguments Valores dos parâmetros
     *
     * @throws \BadMethodCallException Se o método não for SET ou GET/IS
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments): mixed
    {
        $method = substr($name, 0, 3);
        $field = lcfirst(substr($name, 3));

        if (substr($method, 0, 2) === "is") {
            $method = "is";
        }

        return match ($method) {
            "set" => $this->values[$field] = $arguments[0],
            "get", "is" => $this->values[$field] ?? throw (new HttpException("Argumento \"$field\" não existe no uso da função \"$name(" . implode(", ", $arguments) . ")\".", 500))->json(),
            default => throw (new HttpException("Função \"$name(" . implode(", ", $arguments) . ")\" não existe.", 500))->json()
        };
    }


    /**
     * Armazena/recupera um objeto salvo em cache
     *
     * @param int              $id     ID do objeto
     * @param null|self|string $object Objeto instanciado
     *
     * @static
     *
     * @return ?self
     */
    protected static function cache(int $id, null|self|string $object = null): ?self
    {
        $className = is_string($object) ? $object : ($object::class ?? null);
        if ($object !== null && !is_string($object)) {
            self::$_cache[$className][$id] = $object;
        }

        return self::$_cache[$className][$id] ?? null;
    }
}
