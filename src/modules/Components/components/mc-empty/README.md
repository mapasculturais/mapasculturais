# Componente `<mc-empty>`

Componente vazio. Útil para substituir um componente por outro vazio

```php
$app->hook('component(NOME_DO_COMPONENTE):params', function (&$component_name) use ($app) {
    if (!$app->user->is('admin')) {
        $component_name = 'mc-empty';
    }
});
```
