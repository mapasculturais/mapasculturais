<?php
namespace MapasCulturais\Traits;
use MapasCulturais\App;

trait EntityLock {

    /**
     * This entity uses Lock
     *
     * @return bool true
     */
    public static function usesLock() {
        return true;
    }

    function lock($timeout = 60) {
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

    function unlock() {
        $filename = $this->generateFilename();

        if(file_exists($filename)) {
            unlink($filename);
        }
    }

    function renewLock($token) {
        $filename = $this->generateFilename();

        if(file_exists($filename)) {
            $lock_data_json = file_get_contents($filename);
            $lock_data = json_decode($lock_data_json, true);

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

    function generateFilename() {
        $app = App::i();
        
        $name = $app->slugify("{$this}");
        $filename = sys_get_temp_dir() . "/lock-{$name}.lock";

        return $filename;
    }
}