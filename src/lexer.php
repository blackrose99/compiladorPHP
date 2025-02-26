<?php

class Lexer {
    private $code;
    private $tokens = [];
    private $pos = 0;
    private $line = 1; // Para mostrar la línea del error

    private const KEYWORDS = ['if', 'else', 'var', 'while', 'for', 'return', 'int'];
    private const OPERATORS = ['+', '-', '*', '/', '=', '==', '!=', '<', '>', '<=', '>=', '&&', '||'];
    private const SYMBOLS = [';', '(', ')', '{', '}', '[', ']', ',', '.'];

    public function __construct($code) {
        $this->code = $code;
    }

    public function tokenize() {
        while ($this->pos < strlen($this->code)) {
            $char = $this->code[$this->pos];

            // Ignorar espacios en blanco
            if (ctype_space($char)) {
                if ($char === "\n") {
                    $this->line++; // Contar líneas
                }
                $this->pos++;
                continue;
            }

            // Identificar palabras clave o variables
            if (ctype_alpha($char) || $char === '_') {
                $word = $this->readWord();
                $this->tokens[] = ['type' => in_array($word, self::KEYWORDS) ? 'KEYWORD' : 'IDENTIFIER', 'value' => $word, 'line' => $this->line];
                continue;
            }

            // Identificar números
            if (ctype_digit($char)) {
                $this->tokens[] = ['type' => 'NUMBER', 'value' => $this->readNumber(), 'line' => $this->line];
                continue;
            }

            // Identificar cadenas de texto
            if ($char === '"' || $char === "'") {
                $this->tokens[] = ['type' => 'STRING', 'value' => $this->readString($char), 'line' => $this->line];
                continue;
            }

            // Identificar operadores dobles
            $nextChar = $this->peekNext();
            if (in_array($char . $nextChar, self::OPERATORS)) {
                $this->tokens[] = ['type' => 'OPERATOR', 'value' => $char . $nextChar, 'line' => $this->line];
                $this->pos += 2;
                continue;
            }

            // Identificar operadores simples
            if (in_array($char, self::OPERATORS)) {
                $this->tokens[] = ['type' => 'OPERATOR', 'value' => $char, 'line' => $this->line];
                $this->pos++;
                continue;
            }

            // Identificar símbolos
            if (in_array($char, self::SYMBOLS)) {
                $this->tokens[] = ['type' => 'SYMBOL', 'value' => $char, 'line' => $this->line];
                $this->pos++;
                continue;
            }

            // ⚠️ Error de caracter no reconocido
            $this->tokens[] = ['type' => 'ERROR', 'value' => "Carácter no reconocido: '$char'", 'line' => $this->line];
            $this->pos++;
        }

        return $this->tokens;
    }

    private function readWord() {
        $word = '';
        while ($this->pos < strlen($this->code) && (ctype_alnum($this->code[$this->pos]) || $this->code[$this->pos] === '_')) {
            $word .= $this->code[$this->pos++];
        }
        return $word;
    }

    private function readNumber() {
        $number = '';
        while ($this->pos < strlen($this->code) && ctype_digit($this->code[$this->pos])) {
            $number .= $this->code[$this->pos++];
        }
        return intval($number);
    }

    private function readString($quoteType) {
        $this->pos++; // Saltar la comilla inicial
        $string = '';

        while ($this->pos < strlen($this->code) && $this->code[$this->pos] !== $quoteType) {
            if ($this->code[$this->pos] === "\n") {
                $this->line++; // Contar línea si hay salto de línea
            }
            $string .= $this->code[$this->pos++];
        }

        $this->pos++; // Saltar la comilla final
        return $string;
    }

    private function peekNext() {
        return $this->pos + 1 < strlen($this->code) ? $this->code[$this->pos + 1] : '';
    }
}

?>
