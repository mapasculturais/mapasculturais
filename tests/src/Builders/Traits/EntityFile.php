<?php

namespace Tests\Builders\Traits;

use MapasCulturais\Entities\File;

/** @property Entities\Agent $instance */
trait EntityFile
{
    protected function addFileToInstance(string $group_name): void
    {
        $minimal_png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        $tmp_path = sys_get_temp_dir() . '/test-file-' . uniqid() . '.png';
        file_put_contents($tmp_path, $minimal_png);

        $tmp_file = [
            'error' => UPLOAD_ERR_OK,
            'name' => $group_name . '.png',
            'type' => 'image/png',
            'tmp_name' => $tmp_path,
            'size' => strlen($minimal_png),
        ];

        $file_class_name = $this->instance::getFileClassName();

        /** @var File $file */
        $file = new $file_class_name($tmp_file);
        $file->owner = $this->instance;
        $file->group = $group_name;
        $file->save(true);

        @unlink($tmp_path);
    }

    function addAvatarFile(): self
    {
        $this->addFileToInstance('avatar');

        return $this;
    }
}
