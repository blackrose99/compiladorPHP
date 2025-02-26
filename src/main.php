<?php

require_once 'lexer.php';
require_once 'parser.php';
require_once 'interpreter.php';

// Código de prueba (puede venir de un archivo o entrada de usuario)
$code = <<<CODE
int x = 5;
int y = 7;
in/t menu = 1;
int z = x + y;

CODE;
/*while(menu != 0){
    int z = x + y;
    menu = 0;
}*/


$lexer = new Lexer($code);
$tokens = $lexer->tokenize();

print_r($tokens);


// Pasar tokens al parser
$parser = new Parser($tokens);
$statements = $parser->parse();

/*// Ejecutar en el intérprete
$interpreter = new Interpreter();
$interpreter->execute($statements);*/

?>
