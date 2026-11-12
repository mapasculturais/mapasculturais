<?php

// Executa o endpoint em outro processo porque o download encerra a requisição com exit.
$source = realpath(__DIR__ . '/../../src/core') ?: realpath(__DIR__ . '/../../../src/core');
spl_autoload_register(function (string $class) use ($source): void {
    if (str_starts_with($class, 'MapasCulturais\\')) {
        $path = $source . '/' . str_replace('\\', '/', substr($class, 15)) . '.php';
        if (is_file($path)) {
            require_once $path;
        }
    }
});

$scenario = json_decode(stream_get_contents(STDIN), true, flags: JSON_THROW_ON_ERROR);
$opportunities = [];
foreach ($scenario['phases'] as $phase) {
    $opportunities[$phase['id']] = (object) [
        'id' => $phase['id'],
        'name' => $phase['name'],
        'isDataCollection' => $phase['isDataCollection'] ?? true,
    ];
}
$first = reset($opportunities);
foreach ($opportunities as $opportunity) {
    $opportunity->firstPhase = $first;
    $opportunity->allPhases = array_values(array_intersect_key(
        $opportunities,
        array_flip($scenario['phaseIds'] ?? array_keys($opportunities))
    ));
}

$registrations = [];
foreach ($scenario['registrations'] as $data) {
    $data['opportunity'] = $opportunities[$data['opportunity']];
    foreach ($data['files'] as &$files) {
        $files = is_array($files)
            ? array_map(fn ($path) => (object) ['path' => $path], $files)
            : (object) ['path' => $files];
    }
    unset($files);
    $registrations[] = new class($data) extends \MapasCulturais\Entities\Registration {
        public function __construct(private array $data) {
            $this->id = $data['id'];
            $this->number = $data['number'];
        }

        public function __get($name) {
            return $this->data[$name] ?? null;
        }

        public function canUser($action, $userOrAgent = null) {
            return $this->data['canView'] ?? true;
        }

        public function checkPermission($action, $userOrAgent = null) {
            if (!$this->canUser($action, $userOrAgent)) {
                throw new \RuntimeException('Permission denied');
            }
        }
    };
}

$repository = new class($registrations) {
    public function __construct(private array $registrations) {}

    public function findBy(array $criteria): array {
        return array_values(array_filter($this->registrations, function ($registration) use ($criteria) {
            foreach ($criteria as $key => $value) {
                if (is_array($value) ? !in_array($registration->$key, $value, true) : $registration->$key !== $value) {
                    return false;
                }
            }
            return true;
        }));
    }
};

// A aplicação não precisa ser inicializada: autenticação e persistência são isoladas neste teste.
$app = (new ReflectionClass(\MapasCulturais\App::class))->newInstanceWithoutConstructor();
(new ReflectionProperty(\MapasCulturais\App::class, '_instances'))->setValue(null, ['web' => $app]);
$GLOBALS['i18n']['default'] = new class {
    public function translate($text) {
        return $text;
    }
};
\MapasCulturais\i::$replacements = [];

$controller = new class($registrations[$scenario['requested']], $repository) extends \MapasCulturais\Controllers\Registration {
    public function __construct(private $registration, private $registrationRepository) {}

    public function __get($name) {
        return match ($name) {
            'requestedEntity' => $this->registration,
            'repository' => $this->registrationRepository,
            default => null,
        };
    }

    public function requireAuthentication(string $redirect_url = null) {}

    public function json($data, $status = 200) {
        echo json_encode(['status' => $status, 'data' => $data], JSON_THROW_ON_ERROR);
        exit;
    }
};
$controller->GET_createZipFiles();
