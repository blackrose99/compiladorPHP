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
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compilador</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="background-color: #f8f9fa; padding: 20px;">
    <div class="container">
        <h1 style="text-align: center; color: #343a40; margin-bottom: 20px;">Compilador Personalizado</h1>

        <!-- Instrucciones -->
        <div style="background-color: #ffffff; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <h4 style="color: #495057;">Instrucciones</h4>
            <p style="color: #6c757d;">
                Pegue su código en el área de texto y haga clic en "Compilar" para ejecutarlo. Use el botón "Ayuda" para ver la sintaxis válida. Ejemplo:
            <pre style="background-color: #e9ecef; padding: 10px; border-radius: 5px;">
entero bandera = 1;
si (bandera == 1) {
    entero contador = 0;
    mientras (contador < 3) {
        contador = contador + 1;
    }
    bandera = 0;
}
                </pre>
            </p>
        </div>

        <!-- Formulario -->
        <form method="post" style="background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <div class="mb-3">
                <label for="codigo" style="font-weight: bold; color: #343a40;">Código:</label>
                <textarea class="form-control" id="codigo" name="codigo" rows="10" style="resize: vertical; font-family: monospace;" placeholder="Pegue su código aquí..."><?php echo isset($_POST['codigo']) ? htmlspecialchars($_POST['codigo']) : ''; ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="margin-right: 10px;">Compilar</button>
            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#ayudaModal">Ayuda</button>
        </form>

        <!-- Resultados -->
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['codigo'])): ?>
            <div style="margin-top: 20px; background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h4 style="color: #495057;">Resultados:</h4>
                <?php
                $compilador = new Compilador();
                $resultado = $compilador->compilar($_POST['codigo']);
                if ($resultado['estado'] === 'exito') {
                    echo '<h5 style="color: #28a745;">Tokens:</h5>';
                    echo '<pre style="background-color: #e9ecef; padding: 10px; border-radius: 5px;">';
                    print_r($resultado['tokens']);
                    echo '</pre>';

                    echo '<h5 style="color: #28a745;">Árbol Sintáctico (AST):</h5>';
                    echo '<pre style="background-color: #e9ecef; padding: 10px; border-radius: 5px;">';
                    print_r($resultado['ast']);
                    echo '</pre>';

                    echo '<h5 style="color: #28a745;">Resultado:</h5>';
                    echo '<pre style="background-color: #e9ecef; padding: 10px; border-radius: 5px;">';
                    print_r($resultado['resultado']);
                    echo '</pre>';
                } else {
                    echo '<div class="alert alert-danger" role="alert">';
                    echo htmlspecialchars($resultado['error']);
                    echo '</div>';
                }
                ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal de Ayuda -->
    <div class="modal fade" id="ayudaModal" tabindex="-1" aria-labelledby="ayudaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ayudaModalLabel">Sintaxis Válida</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>El compilador soporta las siguientes construcciones:</p>
                    <ul>
                        <li><strong>Declaración de variables:</strong> <code>entero nombre = valor;</code> (solo valores numéricos).</li>
                        <li><strong>Asignación:</strong> <code>nombre = valor;</code> (variable debe estar declarada).</li>
                        <li><strong>Condicional:</strong> <code>si (condicion) { sentencias }</code> (condición con operadores <code>==</code>, <code>!=</code>, <code>&lt;</code>, <code>&gt;</code>, <code>&lt;=</code>, <code>&gt;=</code>).</li>
                        <li><strong>Bucle:</strong> <code>mientras (condicion) { sentencias }</code>.</li>
                        <li><strong>Expresiones:</strong> Operaciones con <code>+</code>, <code>-</code>, <code>*</code>, <code>/</code> (ejemplo: <code>contador = contador + 1;</code>).</li>
                    </ul>
                    <p><strong>Ejemplo válido:</strong></p>
                    <pre>
entero bandera = 1;
si (bandera == 1) {
    entero contador = 0;
    mientras (contador < 3) {
        contador = contador + 1;
    }
    bandera = 0;
}
                    </pre>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>