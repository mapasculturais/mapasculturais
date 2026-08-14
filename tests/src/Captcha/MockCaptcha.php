<?php

namespace Tests\Captcha;

use MapasCulturais\App;

/**
 * Configura um provider de captcha de teste que responde success=true
 * via arquivo local (sem HTTP externo e sem stream wrapper).
 */
class MockCaptcha
{
    protected static ?string $verifyFile = null;

    public static function register(): void
    {
        if (self::$verifyFile === null) {
            self::$verifyFile = tempnam(sys_get_temp_dir(), 'mc-captcha-');
            file_put_contents(self::$verifyFile, json_encode(['success' => true]));
        }

        $app = App::i();
        $app->config['captcha']['provider'] = 'test';
        $app->config['captcha']['providers']['test'] = [
            'url' => 'file://' . self::$verifyFile,
            'verify' => self::$verifyFile,
            'key' => 'test-key',
            'secret' => 'test-secret',
        ];
    }
}
