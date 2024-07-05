<?php
namespace MapasCulturais\Traits;
use MapasCulturais\App;

trait EntityLock {

    /**
     * This entity uses Lock
     *
     * @return bool true
     */
    public static function usesLock(): bool {
        return true;
    }

    /**
     * Acquires a lock on the entity.
     *
     * @param int $timeout Lock timeout in seconds (default is 60).
     * @return string Generated token for the lock.
     */
    function lock(int $timeout = 60) {
        $app = App::i();
        $token = $app->getToken(32);
        $filename = $this->generateFilename();
        
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
     * Checks if the entity is currently locked.
     *
     * @return array|false Lock data array if locked, otherwise false.
     */
    public function isLocked() {
        $filename = $this->generateFilename();

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
     * Releases the lock on the entity.
     *
     * @return void
     */
    function unlock() {
        $filename = $this->generateFilename();

        if(file_exists($filename)) {
            unlink($filename);
        }
    }

    /**
     * Renews the lock if it's still valid and matches the provided token.
     *
     * @param string $token Token to renew the lock.
     * @return bool True if the lock was successfully renewed, false otherwise.
     */
    function renewLock(string $token) {
        $filename = $this->generateFilename();

        if($lock_data = $this->isLocked()) {
            $valid_until = strtotime($lock_data['validUntil']);
            $token_data = $lock_data['token'];

            if($valid_until < time() && $token == $token_data) {
                $lock_data['validUntil'] = date('Y-m-d H:i:s', time() + $lock_data['timeout']);
                $lock_data_json = json_encode($lock_data, JSON_PRETTY_PRINT);

                file_put_contents($filename, $lock_data_json);

                return false;
            } else {
                return true;
            }
        }

        return true;
    }

    /**
     * Generates a unique filename for storing the lock data.
     *
     * @return string Generated filename.
     */
    function generateFilename() {
        $app = App::i();
        
        $name = $app->slugify("{$this}");
        $filename = sys_get_temp_dir() . "/lock-{$name}.lock";

        return $filename;
    }
}