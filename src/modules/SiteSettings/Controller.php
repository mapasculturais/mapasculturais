<?php

namespace SiteSettings;

use DateTime;
use Exception;
use Throwable;
use TypeError;
use RuntimeException;
use \MapasCulturais\App;
use InvalidArgumentException;
use MapasCulturais\Exceptions\NotFound;
use MapasCulturais\Traits\ControllerAPI;
use MapasCulturais\Exceptions\MailTemplateNotFound;
use MapasCulturais\Exceptions\PermissionDenied;
use MapasCulturais\Exceptions\WorkflowRequest;
use MapasCulturais\i;
use SiteSettings\Entities\Settings;

class Controller  extends \MapasCulturais\Controllers\EntityController
{
    use ControllerAPI;

    function __construct()
    {
        parent::__construct();
        $this->entityClassName = '\SiteSettings\Entities\Settings';
    }

    /**
     * @return void 
     * @throws RuntimeException 
     * @throws InvalidArgumentException 
     * @throws NotFound 
     * @throws Exception 
     */
    public function GET_steps(): void
    {
        $app = App::i();

        $this->requireAuthentication();

        if (!$app->user->is('admin')) {
            $app->pass();
        }

        $this->render('settings', []);
    }


    /**
     * @return void 
     * @throws RuntimeException 
     * @throws InvalidArgumentException 
     * @throws NotFound 
     * @throws Exception 
     * @throws MailTemplateNotFound 
     * @throws TypeError 
     * @throws Throwable 
     */
    public function POST_sendMailTest(): void
    {
        $app = App::i();

        $this->requireAuthentication();

        if (!$app->user->is('admin')) {
            $app->pass();
        }

        $email = $this->data['email'];
        $params = [
            'siteName' => $app->siteName
        ];

        $message = $app->renderMailerTemplate('email_teste_settings', $params);
        $email_params = [
            'from' => $app->config['mailer.from'],
            'to' => $email,
            'subject' => $message['title'],
            'body' => $message['body'],
        ];

        $send = $app->createAndSendMailMessage($email_params);
        $this->json($send);
    }

    /**
     * @return void 
     * @throws RuntimeException 
     * @throws InvalidArgumentException 
     * @throws NotFound 
     * @throws PermissionDenied 
     * @throws WorkflowRequest 
     */
    public function POST_upload()
    {
        $app = App::i();

        $this->requireAuthentication();

        if (!$app->user->is('admin')) {
            $app->pass();
        }

        if (isset($_FILES['ocFileUpload']) && $_FILES['ocFileUpload']['error'] === UPLOAD_ERR_OK) {
            $oldName = basename($_FILES['ocFileUpload']['name']);
            $fileTmpPath = $_FILES['ocFileUpload']['tmp_name'];
            $new_name = (new DateTime("now"))->getTimestamp();
            $ext = pathinfo($oldName, PATHINFO_EXTENSION);
            $prop = $this->data['prop'];

            if (isset($this->data['imageFinalName'])) {
                $new_name = $this->data['imageFinalName'];
            }

            try {
                $dir = $this->resolveSiteSettingsUploadDir($this->data);
            } catch (RuntimeException $e) {
                $app->log->warning($e->getMessage());
                $this->json(false);
                return;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $new_name . '.' . $ext;
            if (file_exists($path)) {
                unlink($path);
            }

            /** @var Settings $settings */
            if ($settings = $app->repo('SiteSettings\\Entities\\Settings')->find($this->data['id'])) {

                $metadataFiles = $settings->fromToFilesMetadata();
                $metadata = $metadataFiles[$prop];

                $bannerImageData = [];
                $old_image = null;
                if ($bannerImageData = $settings->$metadata) {
                    $old_image = $bannerImageData->path;
                }

                $bannerImageData = [
                    'prop' => $prop,
                    'path' => $path,
                    'settingsId' => $settings->id,
                    'oldName' => $oldName,
                    'ext' => $ext,
                    'dateUpload' => (new DateTime("now"))->format('Y-m-d H:i:s'),
                    'new_name' => $new_name . '.' . $ext,
                ];

                if (move_uploaded_file($fileTmpPath, $path)) {
                    @chmod($path, 0664);

                    if ($old_image && $old_image !== $path && is_file($old_image)) {
                        @unlink($old_image);
                    }

                    $themeAssetKey = $prop === 'mail-image'
                        ? ('img/' . basename($path))
                        : ('img/home/' . basename($path));
                    $bannerImageData['url'] = Module::resolvePublicAssetUrl($app, $path, $themeAssetKey);

                    $settings->$metadata = $bannerImageData;
                    $settings->save(true);
                    $this->json($bannerImageData);
                    return;
                }
            }
        }

        $this->json(false);
    }

    /**
     * Diretório dentro de PUBLIC_PATH (document root). Ex.: assets/img/home → favicons, banner, etc.
     *
     * @param array<string, mixed> $data
     */
    private function resolveSiteSettingsUploadDir(array $data): string
    {
        $default = 'assets/img/home';
        $sub = isset($data['dir']) && is_string($data['dir']) ? $data['dir'] : $default;
        $sub = str_replace(["\0", '\\'], '', $sub);
        $sub = trim($sub, '/');
        if ($sub === '' || str_contains($sub, '..')) {
            $sub = $default;
        }
        if (!preg_match('#^[a-zA-Z0-9][a-zA-Z0-9_./-]*$#', $sub)) {
            $sub = $default;
        }

        $full = rtrim(PUBLIC_PATH, '/\\') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $sub);
        if (!is_dir($full)) {
            if (!@mkdir($full, 0775, true) && !is_dir($full)) {
                throw new RuntimeException(i::__('Não foi possível criar a pasta de upload.'));
            }
        }
        if (!is_writable($full)) {
            throw new RuntimeException(i::__('A pasta de uploads não tem permissão de escrita.'));
        }

        return $full;
    }


    public function POST_clearCache(): void
    {
        $app = App::i();

        $this->requireAuthentication();

        if (!$app->user->is('admin')) {
            $app->pass();
        }

        $this->clearSettingsCustomizerColorsCache($app);
        $this->applyElevatedCacheFlush($app);

        $this->json(true);
    }

    /**
     * Limpeza via GET (ex.: link direto): admins apagam cache de cores; perfis elevados seguem com flush amplo.
     */
    function ALL_clearCache(): void
    {
        $app = App::i();

        $this->requireAuthentication();

        if (!$app->user->is('admin')) {
            $app->pass();
        }

        $url = $app->createUrl('settings', 'steps');

        $this->clearSettingsCustomizerColorsCache($app);
        $this->applyElevatedCacheFlush($app);

        header("Location: {$url}");
        exit;
    }

    private function clearSettingsCustomizerColorsCache(App $app): void
    {
        $cache_id = 'settingsCustomizerColors';
        if ($app->mscache->contains($cache_id)) {
            $app->mscache->delete($cache_id);
        }
    }

    private function applyElevatedCacheFlush(App $app): void
    {
        if ($app->user->is('superAdmin')) {
            $app->cache->flushAll();
        }

        if ($app->user->is('saasSuperAdmin')) {
            $app->mscache->flushAll();
        }
    }
}
