<?php

class Interpreter {
    private $variables = [];
    
    public function execute($statements) {
        foreach ($statements as $stmt) {
            if ($stmt['type'] == 'ASSIGNMENT') {
                $this->variables[$stmt['var']] = $stmt['value'];
            }
            
            if ($stmt['type'] == 'IF') {
                $condition = $this->evaluateCondition($stmt['left'], $stmt['operator'], $stmt['right']);
                if ($condition) {
                    $this->variables[$stmt['var']] = $stmt['value'];
                }
            }
        }
        
        print_r($this->variables);
    }
    
    private function evaluateCondition($left, $operator, $right) {
        if (!isset($this->variables[$left])) {
            die("Error Semántico: Variable '$left' no definida\n");
        }
        $leftValue = $this->variables[$left];
        switch ($operator) {
            case '==': return $leftValue == $right;
            case '<': return $leftValue < $right;
            case '>': return $leftValue > $right;
        }
        die("Error Semántico: Operador desconocido '$operator'\n");
    }
}

?>
