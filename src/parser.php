<?php
class Sintactico
{
    private $tokens;
    private $pos = 0;
    private $variables = []; // Seguimiento de variables declaradas

    public function __construct($tokens)
    {
        $this->tokens = $tokens;
    }

    public function analizar()
    {
        $sentencias = [];
        while ($this->pos < count($this->tokens)) {
            if ($this->coincide('SIMBOLO', ';')) {
                $this->pos++; // Saltar sentencias vacías
                continue;
            }
            $sentencia = $this->analizarSentencia();
            if ($sentencia) {
                $sentencias[] = $sentencia;
            }
        }
        return $sentencias;
    }

    private function analizarSentencia()
    {
        if ($this->coincide('PALABRA_CLAVE', 'entero')) {
            return $this->analizarDeclaracion();
        }
        if ($this->coincide('IDENTIFICADOR')) {
            return $this->analizarAsignacion();
        }
        if ($this->coincide('PALABRA_CLAVE', 'si')) {
            return $this->analizarSi();
        }
        if ($this->coincide('PALABRA_CLAVE', 'mientras')) {
            return $this->analizarMientras();
        }
        throw new Exception("Error Sintáctico en la línea {$this->lineaActual()}: Sentencia inválida");
    }

    private function analizarDeclaracion()
    {
        $this->consumir('PALABRA_CLAVE', 'entero');
        $nombreVar = $this->consumir('IDENTIFICADOR')['valor'];

        if (in_array($nombreVar, $this->variables)) {
            throw new Exception("Error Semántico en la línea {$this->lineaActual()}: Variable '$nombreVar' ya declarada");
        }

        $this->variables[] = $nombreVar;
        $this->consumir('OPERADOR', '=');
        $valor = $this->analizarExpresion();
        $this->consumir('SIMBOLO', ';');

        return [
            'tipo' => 'DECLARACION',
            'variable' => $nombreVar,
            'valor' => $valor
        ];
    }

    private function analizarAsignacion()
    {
        $nombreVar = $this->consumir('IDENTIFICADOR')['valor'];
        $this->consumir('OPERADOR', '=');
        $valor = $this->analizarExpresion();
        $this->consumir('SIMBOLO', ';');

        return [
            'tipo' => 'ASIGNACION',
            'variable' => $nombreVar,
            'valor' => $valor
        ];
    }

    private function analizarSi()
    {
        $this->consumir('PALABRA_CLAVE', 'si');
        $this->consumir('SIMBOLO', '(');
        $condicion = $this->analizarCondicion();
        $this->consumir('SIMBOLO', ')');
        $this->consumir('SIMBOLO', '{');
        $cuerpo = $this->analizarBloque();
        $this->consumir('SIMBOLO', '}');

        return [
            'tipo' => 'SI',
            'condicion' => $condicion,
            'cuerpo' => $cuerpo
        ];
    }

    private function analizarMientras()
    {
        $this->consumir('PALABRA_CLAVE', 'mientras');
        $this->consumir('SIMBOLO', '(');
        $condicion = $this->analizarCondicion();
        $this->consumir('SIMBOLO', ')');
        $this->consumir('SIMBOLO', '{');
        $cuerpo = $this->analizarBloque();
        $this->consumir('SIMBOLO', '}');

        return [
            'tipo' => 'MIENTRAS',
            'condicion' => $condicion,
            'cuerpo' => $cuerpo
        ];
    }

    private function analizarBloque()
    {
        $sentencias = [];
        while ($this->pos < count($this->tokens) && !$this->coincide('SIMBOLO', '}')) {
            if ($this->coincide('SIMBOLO', ';')) {
                $this->pos++;
                continue;
            }
            $sentencia = $this->analizarSentencia();
            if ($sentencia) {
                $sentencias[] = $sentencia;
            }
        }
        return $sentencias;
    }

    private function analizarCondicion()
    {
        $izquierda = $this->consumirCualquiera(['NUMERO', 'IDENTIFICADOR'])['valor'];
        if ($this->coincide('OPERADOR', ['==', '!=', '<', '>', '<=', '>='])) {
            $operador = $this->consumir('OPERADOR')['valor'];
            $derecha = $this->consumirCualquiera(['NUMERO', 'IDENTIFICADOR'])['valor'];
            return [
                'tipo' => 'CONDICION',
                'izquierda' => $izquierda,
                'operador' => $operador,
                'derecha' => $derecha
            ];
        }
        throw new Exception("Error Sintáctico en la línea {$this->lineaActual()}: Se esperaba un operador de comparación");
    }

    private function analizarExpresion()
    {
        $izquierda = $this->consumirCualquiera(['NUMERO', 'IDENTIFICADOR'])['valor'];
        if ($this->coincide('OPERADOR', ['+', '-', '*', '/'])) {
            $operador = $this->consumir('OPERADOR')['valor'];
            $derecha = $this->consumirCualquiera(['NUMERO', 'IDENTIFICADOR'])['valor'];
            return [
                'tipo' => 'EXPRESION',
                'izquierda' => $izquierda,
                'operador' => $operador,
                'derecha' => $derecha
            ];
        }
        return $izquierda;
    }

    // Métodos auxiliares
    private function lineaActual()
    {
        return $this->tokens[$this->pos]['linea'] ?? 1;
    }

    private function coincide($tipo, $valor = null)
    {
        if ($this->pos >= count($this->tokens)) return false;
        $token = $this->tokens[$this->pos];
        return $token['tipo'] === $tipo && ($valor === null || (is_array($valor) ? in_array($token['valor'], $valor) : $token['valor'] === $valor));
    }

    private function consumir($tipo, $valor = null)
    {
        if ($this->coincide($tipo, $valor)) {
            return $this->tokens[$this->pos++];
        }
        $esperado = $valor ? "$tipo '$valor'" : $tipo;
        throw new Exception("Error Sintáctico en la línea {$this->lineaActual()}: Se esperaba $esperado");
    }

    private function consumirCualquiera($tipos)
    {
        if ($this->pos < count($this->tokens) && in_array($this->tokens[$this->pos]['tipo'], $tipos)) {
            return $this->tokens[$this->pos++];
        }
        throw new Exception("Error Sintáctico en la línea {$this->lineaActual()}: Se esperaba " . implode(' o ', $tipos));
    }

    public function obtenerVariables()
    {
        return $this->variables;
    }
}
