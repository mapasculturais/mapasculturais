<?php
namespace MapasCulturais\Validators\Rules;

use Respect\Validation\Rules\AbstractRule;

final class BrPhone extends AbstractRule {
    
    public function validate($input): bool {
        return (bool) preg_match("#^\(?\d{2}\)?[ ]*\d{4,5}-?\d{4}$#", trim((string) $input));
    }
}