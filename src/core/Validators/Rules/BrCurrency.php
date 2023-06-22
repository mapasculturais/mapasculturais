<?php
namespace MapasCulturais\Validators\Rules;

use Respect\Validation\Rules\AbstractRule;

final class BrCurrency extends AbstractRule {
    
    public function validate($input): bool {
        return (bool) preg_match("#^((([1-9]\d?\d?)(\.\d{3})*)|([1-9]\d*)|0),(\d{2})$#", trim((string) $input));
    }
}