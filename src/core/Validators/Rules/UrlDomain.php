<?php
namespace MapasCulturais\Validators\Rules;

use Respect\Validation\Rules\AbstractRule;

final class UrlDomain extends AbstractRule {

    private string $domain;

    public function __construct(string $domain) {
        $this->domain = $domain;
    }

    public function validate($input): bool {
        return (bool) preg_match("#^https?://[^/]*{$this->domain}/?#i", trim((string) $input));
    }
}