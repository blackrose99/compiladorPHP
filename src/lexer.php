<?php
class Lexico
{
    private $codigo;
    private $tokens = [];
    private $pos = 0;
    private $linea = 1;
    private const PALABRAS_CLAVE = ['si', 'sino', 'variable', 'mientras', 'para', 'retornar', 'entero', 'cadena'];
    private const OPERADORES = ['+', '-', '*', '/', '=', '==', '!=', '<', '>', '<=', '>=', '&&', '||'];
    private const SIMBOLOS = [';', '(', ')', '{', '}', '[', ']', ',', '.', ':', '?', '@'];

    public function __construct($codigo)
    {
        $this->codigo = $codigo;
    }

    public function tokenizar()
    {
        while ($this->pos < strlen($this->codigo)) {
            $char = $this->codigo[$this->pos];

            // Ignorar espacios en blanco
            if (ctype_space($char)) {
                if ($char === "\n") {
                    $this->linea++;
                }
                $this->pos++;
                continue;
            }

            // Manejar comentarios (// de una línea)
            if ($char === '/' && $this->siguienteCaracter() === '/') {
                while ($this->pos < strlen($this->codigo) && $this->codigo[$this->pos] !== "\n") {
                    $this->pos++;
                }
                continue;
            }

            // Identificar palabras clave o identificadores
            if (ctype_alpha($char) || $char === '_') {
                $palabra = $this->leerPalabra();
                $this->tokens[] = [
                    'tipo' => in_array($palabra, self::PALABRAS_CLAVE) ? 'PALABRA_CLAVE' : 'IDENTIFICADOR',
                    'valor' => $palabra,
                    'linea' => $this->linea
                ];
                continue;
            }

            // Identificar números
            if (ctype_digit($char)) {
                $this->tokens[] = [
                    'tipo' => 'NUMERO',
                    'valor' => $this->leerNumero(),
                    'linea' => $this->linea
                ];
                continue;
            }

            // Identificar cadenas de texto
            if ($char === '"' || $char === "'") {
                $this->tokens[] = [
                    'tipo' => 'CADENA',
                    'valor' => $this->leerCadena($char),
                    'linea' => $this->linea
                ];
                continue;
            }

            // Identificar operadores de dos caracteres
            $siguienteChar = $this->siguienteCaracter();
            if (in_array($char . $siguienteChar, self::OPERADORES)) {
                $this->tokens[] = [
                    'tipo' => 'OPERADOR',
                    'valor' => $char . $siguienteChar,
                    'linea' => $this->linea
                ];
                $this->pos += 2;
                continue;
            }

            // Identificar operadores de un carácter
            if (in_array($char, self::OPERADORES)) {
                $this->tokens[] = [
                    'tipo' => 'OPERADOR',
                    'valor' => $char,
                    'linea' => $this->linea
                ];
                $this->pos++;
                continue;
            }

            // Identificar símbolos
            if (in_array($char, self::SIMBOLOS)) {
                $this->tokens[] = [
                    'tipo' => 'SIMBOLO',
                    'valor' => $char,
                    'linea' => $this->linea
                ];
                $this->pos++;
                continue;
            }

            // Error por carácter no reconocido
            $this->tokens[] = [
                'tipo' => 'ERROR',
                'valor' => "Carácter no reconocido: '$char'",
                'linea' => $this->linea
            ];
            $this->pos++;
        }

        return $this->tokens;
    }

    private function leerPalabra()
    {
        $palabra = '';
        while ($this->pos < strlen($this->codigo) && (ctype_alnum($this->codigo[$this->pos]) || $this->codigo[$this->pos] === '_')) {
            $palabra .= $this->codigo[$this->pos++];
        }
        return $palabra;
    }

    private function leerNumero()
    {
        $numero = '';
        while ($this->pos < strlen($this->codigo) && ctype_digit($this->codigo[$this->pos])) {
            $numero .= $this->codigo[$this->pos++];
        }
        return intval($numero);
    }

    private function leerCadena($tipoComilla)
    {
        $this->pos++; // Saltar comilla inicial
        $cadena = '';
        while ($this->pos < strlen($this->codigo) && $this->codigo[$this->pos] !== $tipoComilla) {
            if ($this->codigo[$this->pos] === "\n") {
                $this->linea++;
            }
            $cadena .= $this->codigo[$this->pos++];
        }
        $this->pos++; // Saltar comilla final
        return $cadena;
    }

    private function siguienteCaracter()
    {
        return $this->pos + 1 < strlen($this->codigo) ? $this->codigo[$this->pos + 1] : '';
    }
}
