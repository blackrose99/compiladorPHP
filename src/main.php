<?php
require_once 'lexer.php';
require_once 'parser.php';
require_once 'interpreter.php';

class Compilador
{
    private $lexico;
    private $sintactico;
    private $interprete;

    public function __construct()
    {
        $this->interprete = new Interprete();
    }

    public function compilar($codigo)
    {
        try {
            // Paso 1: Análisis Léxico
            $this->lexico = new Lexico($codigo);
            $tokens = $this->lexico->tokenizar();

            // Verificar errores léxicos
            foreach ($tokens as $token) {
                if ($token['tipo'] === 'ERROR') {
                    throw new Exception("Error Léxico en la línea {$token['linea']}: {$token['valor']}");
                }
            }

            // Paso 2: Análisis Sintáctico
            $this->sintactico = new Sintactico($tokens);
            $sentencias = $this->sintactico->analizar();

            // Paso 3: Análisis Semántico
            $this->interprete->analizarSemantica($sentencias);

            // Paso 4: Ejecución
            $resultado = $this->interprete->ejecutar($sentencias);

            return [
                'tokens' => $tokens,
                'ast' => $sentencias,
                'resultado' => $resultado,
                'estado' => 'exito'
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
                'estado' => 'error'
            ];
        }
    }
}

// Uso
$codigo = <<<CODIGO
entero bandera = 1;
si (noDeclarada == 1) {
    entero contador = 0;
    mientras (contador < 3) {
        contador = contador + 1;
    }
    bandera = 0;
}
CODIGO;

$compilador = new Compilador();
$resultado = $compilador->compilar($codigo);

if ($resultado['estado'] === 'exito') {
    echo "Tokens:\n";
    print_r($resultado['tokens']);
    echo "\nÁrbol Sintáctico (AST):\n";
    print_r($resultado['ast']);
    echo "\nResultado:\n";
    print_r($resultado['resultado']);
} else {
    echo "Error: " . $resultado['error'] . "\n";
}
