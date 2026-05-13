<?php
namespace MapasCulturais\Controllers;

use MapasCulturais\App;
use MapasCulturais\Traits;
/**
 * File Controller
 *
 * By default this controller is registered with the id 'file'.
 *
 */
class EventOccurrence extends EntityController {
    use Traits\ControllerAPI;
    
    public function POST_index($data = null) {
        $this->POST_create($data);
    }

    public function PUT_single($data = null){
        $this->POST_edit();
    }

    function GET_create() {
        App::i()->pass();
    }

    function GET_edit() {
        App::i()->pass();
    }

    function GET_index() {
        App::i()->pass();
    }

    function GET_single() {
        App::i()->pass();
    }

    function POST_single() {
        App::i()->pass();
    }


    function POST_create($data = null){
        $this->requireAuthentication();
        $app = App::i();
        $app->applyHookBoundTo($this, "POST({$this->id}.create):data", ['data' => &$data]);
        
        $event = $app->repo('Event')->find($this->postData['eventId']);
        $occurrence = new \MapasCulturais\Entities\EventOccurrence;
        $occurrence->event = $event;
        $occurrence->description = $this->postData['description'];
        
        if (isset($this->postData['price'])) {
            $occurrence->price = $this->postData['price'];
        }

        if (isset($this->postData['priceInfo'])) {
            $occurrence->priceInfo = $this->postData['priceInfo'];
        }

        if (isset($this->postData['type'])) {
            $occurrence->type = $this->postData['type'];
        }

        if (isset($this->postData['metadata'])) {
            $occurrence->metadata = $this->_validate_metadata($this->postData['metadata']);
        }
        
        if (@$this->postData['spaceId']) {
            $occurrence->space = $app->repo('Space')->find($this->postData['spaceId']);
        } elseif ($occurrence->type === 'virtual') {
            $occurrence->space = $app->repo('Space')->find(0);
        }
        
        $post_data = $this->postData;
        unset($post_data['eventId']);
        unset($post_data['type']);
        unset($post_data['metadata']);
        $occurrence->rule = $post_data;

        if ($errors = $occurrence->validationErrors) {
            $this->errorJson($errors);
        } else {
            $this->_finishRequest($occurrence, true);
        }
    }

    function POST_edit($data = null){
        $this->requireAuthentication();
        
        $app = App::i();
        $app->applyHookBoundTo($this, "POST({$this->id}.edit):data", ['data' => &$data]);
        
        $occurrence = $this->requestedEntity;
        $post_data = $this->postData;
        unset($post_data['eventId']);

        if (isset($post_data['type'])) {
            $occurrence->type = $post_data['type'];
            unset($post_data['type']);
        }

        if (isset($post_data['metadata'])) {
            $occurrence->metadata = $this->_validate_metadata($post_data['metadata']);
            unset($post_data['metadata']);
        }

        $occurrence->rule = $post_data;

        if (@$this->postData['spaceId']) {
            $occurrence->space = $app->repo('Space')->find($this->postData['spaceId']);
        } elseif ($occurrence->type === 'virtual') {
            $occurrence->space = $app->repo('Space')->find(0);
        }

        if ($errors = $occurrence->validationErrors) {
            $this->errorJson($errors);
        } else {
            $this->_finishRequest($occurrence);
        }
    }

    private function _validate_metadata($metadata) {
        if (isset($metadata['links']) && is_array($metadata['links'])) {
            $validated_links = [];
            foreach ($metadata['links'] as $url) {
                $url = filter_var(trim($url), FILTER_VALIDATE_URL);
                if ($url) {
                    $validated_links[] = [
                        'url' => $url,
                        'platform' => $this->_detect_platform($url)
                    ];
                }
            }
            $metadata['links'] = $validated_links;
        }
        return $metadata;
    }

    private function _detect_platform($url) {
        $patterns = [
            'youtube'     => '/youtube\.com|youtu\.be/i',
            'tiktok'      => '/tiktok\.com/i',
            'instagram'   => '/instagram\.com/i',
            'zoom'        => '/zoom\.us/i',
            'google-meet' => '/meet\.google\.com/i',
            'facebook'    => '/facebook\.com|fb\.watch/i',
            'twitch'      => '/twitch\.tv/i',
            'teams'       => '/teams\.microsoft\.com/i',
        ];
        
        foreach ($patterns as $platform => $pattern) {
            if (preg_match($pattern, $url)) {
                return $platform;
            }
        }
        
        return 'outros';
    }

}
