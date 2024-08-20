<?php
namespace MapasCulturais\Traits;
use MapasCulturais\App;

trait EntityLock {

    /**
     * Esta entidade utiliza Lock
     *
     * @return bool true
     */
    public static function usesLock(): bool {
        return true;
    }

    /**
     * Faz o lock na entidade.
     *
     * @param int $timeout Lock Tempo limite do bloqueio em segundos (padrão é 60).
     * @return string Token gerado para o bloqueio.
     */
    function lock(int $timeout = null, string $token = null): string {
        /** @var \MapasCulturais\Entity $this */
        $app = App::i();
        $this->checkPermission('lock');

        $timeout = $timeout ?: $app->config['entity.lock.timeout']; 
        $token = $token ?: $app->getToken(32);
        $filename = $this->generateLockFilename();
        
        $lock_data = [
            'token' => $token,
            'lockTimestamp' => date('Y-m-d H:i:s'),
            'timeout' => $timeout,
            'validUntil' => date('Y-m-d H:i:s', time() + $timeout),
            'userId' => $app->user->id,
            'agent' => $app->user->profile->simplify('id,name,avatar')
        ];

        $lock_data_json = json_encode($lock_data, JSON_PRETTY_PRINT);

        file_put_contents($filename, $lock_data_json);

        return $token;
    }

    /**
     * Verifica se a entidade está atualmente bloqueada.
     *
     * @return array|false Array de dados do lock se estiver bloqueado, caso contrário false.
     */
    public function isLocked() {
        $filename = $this->generateLockFilename();

        if(file_exists($filename)) {
            $lock_data_json = file_get_contents($filename);
            $lock_data = json_decode($lock_data_json, true);

            $valid_until = strtotime($lock_data['validUntil']);
            
            if($valid_until < time()) {
                $this->unlock();
                return false;
            } else {
                return $lock_data;
            }
        }

        return false;
    }

    /**
     * Libera o lock da entidade.
     *
     * @return void
     */
    function unlock(): void {
        $filename = $this->generateLockFilename();

        if(file_exists($filename)) {
            unlink($filename);
        }
    }

    /**
     * Renova o lock se ainda estiver válido e corresponder ao token fornecido.
     *
     * @param string $token Token para renovar o lock.
     * @return bool True se o bloqueio foi renovado com sucesso, false caso contrário.
     */
    function renewLock(string $token): bool {
        $filename = $this->generateLockFilename();
        $app = App::i();
        if($lock_data = $this->isLocked()) {
            $valid_until = strtotime($lock_data['validUntil']);
            $token_data = $lock_data['token'];
            if($token == $token_data) {
                $lock_data['validUntil'] = date('Y-m-d H:i:s', time() + $lock_data['timeout']);
                $lock_data_json = json_encode($lock_data, JSON_PRETTY_PRINT);

                file_put_contents($filename, $lock_data_json);

                return true;
            } else {
                return false;
            }
        } else {
            $this->lock(token: $token);
            return true;
        }
    }

    /**
     * Gera um nome de arquivo para armazenar os dados do lock.
     *
     * @return string Nome do arquivo gerado.
     */
    private function generateLockFilename(): string {
        $app = App::i();
        
        $name = $app->slugify("{$this}");
        $filename = sys_get_temp_dir() . "/lock-{$name}.lock";

        return $filename;
    }
}
