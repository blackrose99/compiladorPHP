<?php
class Interprete
{
    private $variables = [];

    public function analizarSemantica($sentencias)
    {
        // Reiniciar variables para análisis semántico
        $this->variables = [];
        foreach ($sentencias as $sentencia) {
            $this->verificarSemanticaSentencia($sentencia);
        }
    }

    private function verificarSemanticaSentencia($sentencia)
    {
        switch ($sentencia['tipo']) {
            case 'DECLARACION':
                if (array_key_exists($sentencia['variable'], $this->variables)) {
                    throw new Exception("Error Semántico: Variable '{$sentencia['variable']}' ya declarada");
                }
                // Registrar la variable durante la verificación semántica
                $this->variables[$sentencia['variable']] = null;
                $valor = $this->evaluar($sentencia['valor']);
                if (!is_numeric($valor)) {
                    throw new Exception("Error Semántico: Se esperaba un valor numérico para la variable '{$sentencia['variable']}'");
                }
                break;
            case 'ASIGNACION':
                if (!array_key_exists($sentencia['variable'], $this->variables)) {
                    throw new Exception("Error Semántico: Variable '{$sentencia['variable']}' no declarada");
                }
                $valor = $this->evaluar($sentencia['valor']);
                if (!is_numeric($valor)) {
                    throw new Exception("Error Semántico: Se esperaba un valor numérico para la variable '{$sentencia['variable']}'");
                }
                break;
            case 'SI':
                // Verificar variables en la condición
                if ($sentencia['condicion']['tipo'] === 'CONDICION') {
                    $izquierda = $sentencia['condicion']['izquierda'];
                    $derecha = $sentencia['condicion']['derecha'];
                    if (!is_numeric($izquierda) && !array_key_exists($izquierda, $this->variables)) {
                        throw new Exception("Error Semántico: Variable '$izquierda' no declarada en la condición del si");
                    }
                    if (!is_numeric($derecha) && !array_key_exists($derecha, $this->variables)) {
                        throw new Exception("Error Semántico: Variable '$derecha' no declarada en la condición del si");
                    }
                }
                // Verificar cuerpo del si
                foreach ($sentencia['cuerpo'] as $sentenciaCuerpo) {
                    $this->verificarSemanticaSentencia($sentenciaCuerpo);
                }
                break;
            case 'MIENTRAS':
                // Verificar variables en la condición
                if ($sentencia['condicion']['tipo'] === 'CONDICION') {
                    $izquierda = $sentencia['condicion']['izquierda'];
                    $derecha = $sentencia['condicion']['derecha'];
                    if (!is_numeric($izquierda) && !array_key_exists($izquierda, $this->variables)) {
                        throw new Exception("Error Semántico: Variable '$izquierda' no declarada en la condición del mientras");
                    }
                    if (!is_numeric($derecha) && !array_key_exists($derecha, $this->variables)) {
                        throw new Exception("Error Semántico: Variable '$derecha' no declarada en la condición del mientras");
                    }
                }
                // Verificar cuerpo del mientras
                foreach ($sentencia['cuerpo'] as $sentenciaCuerpo) {
                    $this->verificarSemanticaSentencia($sentenciaCuerpo);
                }
                break;
        }
    }

    public function ejecutar($sentencias)
    {
        $salida = [];
        foreach ($sentencias as $sentencia) {
            $resultado = $this->ejecutarSentencia($sentencia);
            if ($resultado) {
                $salida = array_merge($salida, $resultado);
            }
        }
        return $salida;
    }

    private function ejecutarSentencia($sentencia)
    {
        $salida = [];
        switch ($sentencia['tipo']) {
            case 'DECLARACION':
                $this->variables[$sentencia['variable']] = $this->evaluar($sentencia['valor']);
                $salida[$sentencia['variable']] = $this->variables[$sentencia['variable']];
                break;
            case 'ASIGNACION':
                $this->variables[$sentencia['variable']] = $this->evaluar($sentencia['valor']);
                $salida[$sentencia['variable']] = $this->variables[$sentencia['variable']];
                break;
            case 'SI':
                if ($this->evaluarCondicion($sentencia['condicion'])) {
                    foreach ($sentencia['cuerpo'] as $sentenciaCuerpo) {
                        $resultado = $this->ejecutarSentencia($sentenciaCuerpo);
                        if ($resultado) {
                            $salida = array_merge($salida, $resultado);
                        }
                    }
                }
                break;
            case 'MIENTRAS':
                while ($this->evaluarCondicion($sentencia['condicion'])) {
                    foreach ($sentencia['cuerpo'] as $sentenciaCuerpo) {
                        $resultado = $this->ejecutarSentencia($sentenciaCuerpo);
                        if ($resultado) {
                            $salida = array_merge($salida, $resultado);
                        }
                    }
                }
                break;
        }
        return $salida;
    }

    private function evaluar($valor)
    {
        if (is_array($valor) && $valor['tipo'] === 'EXPRESION') {
            $izquierda = is_numeric($valor['izquierda']) ? $valor['izquierda'] : $this->variables[$valor['izquierda']];
            $derecha = is_numeric($valor['derecha']) ? $valor['derecha'] : $this->variables[$valor['derecha']];

            switch ($valor['operador']) {
                case '+':
                    return $izquierda + $derecha;
                case '-':
                    return $izquierda - $derecha;
                case '*':
                    return $izquierda * $derecha;
                case '/':
                    return $izquierda / $derecha;
            }
        }
        return is_numeric($valor) ? $valor : $this->variables[$valor];
    }

    private function evaluarCondicion($condicion)
    {
        if ($condicion['tipo'] === 'CONDICION') {
            $izquierda = is_numeric($condicion['izquierda']) ? $condicion['izquierda'] : $this->variables[$condicion['izquierda']];
            $derecha = is_numeric($condicion['derecha']) ? $condicion['derecha'] : $this->variables[$condicion['derecha']];

            switch ($condicion['operador']) {
                case '==':
                    return $izquierda == $derecha;
                case '!=':
                    return $izquierda != $derecha;
                case '<':
                    return $izquierda < $derecha;
                case '>':
                    return $izquierda > $derecha;
                case '<=':
                    return $izquierda <= $derecha;
                case '>=':
                    return $izquierda >= $derecha;
            }
        }
        return false;
    }
}
