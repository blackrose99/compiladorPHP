<?php

class Parser {
    private $tokens;
    private $pos = 0;
    
    public function __construct($tokens) {
        $this->tokens = $tokens;
    }
    
    public function parse() {
        $statements = [];
        
        while ($this->pos < count($this->tokens)) {
            $statements[] = $this->parseStatement();
        }
        
        return $statements;
    }
    
    private function parseStatement() {
        if ($this->match('KEYWORD', 'var')) {
            return $this->parseAssignment();
        }
        
        if ($this->match('KEYWORD', 'if')) {
            return $this->parseIf();
        }
        
        die("Error Sintáctico: Declaración inválida\n");
    }
    
    private function parseAssignment() {
        $varName = $this->consume('IDENTIFIER')['value'];
        $this->consume('OPERATOR', '=');
        $value = $this->consume('NUMBER')['value'];
        $this->consume('SEMICOLON');
        return ['type' => 'ASSIGNMENT', 'var' => $varName, 'value' => $value];
    }
    
    private function parseIf() {
        $this->consume('OPERATOR', '(');
        $left = $this->consume('IDENTIFIER')['value'];
        $operator = $this->consume('OPERATOR')['value'];
        $right = $this->consume('NUMBER')['value'];
        $this->consume('OPERATOR', ')');
        
        $this->consume('KEYWORD', 'var');
        $varName = $this->consume('IDENTIFIER')['value'];
        $this->consume('OPERATOR', '=');
        $value = $this->consume('NUMBER')['value'];
        $this->consume('SEMICOLON');
        
        return ['type' => 'IF', 'left' => $left, 'operator' => $operator, 'right' => $right, 'var' => $varName, 'value' => $value];
    }
    
    private function match($type, $value = null) {
        if ($this->pos < count($this->tokens) && $this->tokens[$this->pos]['type'] == $type) {
            if ($value === null || $this->tokens[$this->pos]['value'] == $value) {
                return true;
            }
        }
        return false;
    }
    
    private function consume($type, $value = null) {
        if ($this->match($type, $value)) {
            return $this->tokens[$this->pos++];
        }
        die("Error Sintáctico: Se esperaba '$type $value'\n");
    }
}

?>
