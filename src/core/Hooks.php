<?php
declare(strict_types=1);

namespace MapasCulturais;

class Hooks {
    protected App $app;

    protected array $_hooks = [];
    protected array $_excludeHooks = [];
    protected array $_hookCache = [];
    protected int $hook_count = 0;
    protected array $hookStack = [];

    function __construct(App $app) {
        $this->app = $app;
    }

    /**
     * Clear hook listeners
     *
     * Clear all listeners for all hooks. If `$name` is
     * a valid hook name, only the listeners attached
     * to that hook will be cleared.
     *
     * @param  string   $name   A hook name (Optional)
     */
    public function clear(string $name = null) {
        if (is_null($name)) {
            $this->_hooks = [];
            $this->_excludeHooks = [];
        } else {
            $hooks = $this->getCallables($name);
            foreach ($this->_excludeHooks as $hook => $cb) {
                if (in_array($cb, $hooks))
                    unset($this->_excludeHooks[$hook]);
            }

            foreach ($this->_hooks as $hook => $cbs) {
                foreach ($cbs as $i => $cb)
                    unset($this->_hooks[$hook][$i]);
            }
        }
    }


    /**
     * Get hook listeners
     *
     * Return an array of registered hooks. If `$name` is a valid
     * hook name, only the listeners attached to that hook are returned.
     * Else, all listeners are returned as an associative array whose
     * keys are hook names and whose values are arrays of listeners.
     *
     * @param  string     $name     A hook name (Optional)
     * @return array|null
     */
    public function get(string $name = null) {
        return $this->getCallables($name);
    }

    /**
     * Assign hook
     * @param  string   $name       The hook name
     * @param  callable    $callable   A callable object
     * @param  int      $priority   The hook priority; 0 = high, 10 = low
     */
    function hook(string $name, callable $callable, int $priority = 10) {
        $this->hook_count++;
        $priority += ($this->hook_count / 100000);

        $this->_hookCache = [];
        $_hooks = explode(',', $name);
        foreach ($_hooks as $hook) {
            if (trim($hook)[0] === '-') {
                $hook = $this->_compile($hook);
                if (!key_exists($hook, $this->_excludeHooks))
                    $this->_excludeHooks[$hook] = [];

                $this->_excludeHooks[$hook][] = $callable;
            }else {
                $priority_key = "$priority";
                $hook = $this->_compile($hook);

                if (!key_exists($hook, $this->_hooks))
                    $this->_hooks[$hook] = [];

                if (!key_exists($priority_key, $this->_hooks[$hook]))
                    $this->_hooks[$hook][$priority_key] = [];

                $this->_hooks[$hook][$priority_key][] = $callable;

                ksort($this->_hooks[$hook]);
            }
        }
    }


    protected function _log(string $name) {
        $n = 1;

        if(strpos($name, 'template(') === 0){
            $n = 2;
        }

        $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $filename = $bt[$n]['file'];
        $fileline = $bt[$n]['line'];
        $lines = file($filename);
        $line = trim($lines[$fileline - 1]);

        $this->app->log->debug("hook >> \033[37m$name \033[0m(\033[33m$filename:$fileline\033[0m)");
        $this->app->log->debug("     >> \033[32m$line\033[0m\n");
    }


    /**
     * Invoke hook
     * @param  string   $name       The hook name
     * @param  mixed    $hookArgs   (Optional) Argument for hooked functions
     * 
     * @return callable[]
     */
    function apply(string $name, array $hookArg = []): array {

        if ($this->app->config['app.log.hook']){
            $conf = $this->app->config['app.log.hook'];
            if(is_bool($conf) || preg_match('#' . str_replace('*', '.*', $conf) . '#i', $name)){
                $this->_log($name);

            }
        }

        $this->hookStack[] = (object) [
            'name' => $name,
            'args' => $hookArg,
            'bound' => false,
        ];

        $callables = $this->getCallables($name);
        foreach ($callables as $callable) {
            call_user_func_array($callable, $hookArg);
        }

        array_pop($this->hookStack);

        return $callables;
    }

    /**
     * Invoke hook binding callbacks to the target object
     *
     * @param  object $target_object Object to bind hook
     * @param  string   $name       The hook name
     * @param  mixed    $hookArgs   (Optional) Argument for hooked functions
     * 
     * @return callable[]
     */
    function applyBoundTo(object $target_object, string $name, array $hookArg = []) {

        if ($this->app->config['app.log.hook']){
            $conf = $this->app->config['app.log.hook'];
            if(is_bool($conf) || preg_match('#' . str_replace('*', '.*', $conf) . '#i', $name)){
                $this->_log($name);
            }
        }

        $this->hookStack[] = (object) [
            'name' => $name,
            'args' => $hookArg,
            'bound' => false,
        ];

        $callables = $this->getCallables($name);
        foreach ($callables as $callable) {
            $callable = \Closure::bind($callable, $target_object);
            call_user_func_array($callable, $hookArg);
        }

        array_pop($this->hookStack);

        return $callables;
    }


    /**
     * 
     * @param string $name 
     * @return \Closure[]
     */
    function getCallables(string $name):array  {
        if(isset($this->_hookCache[$name])){
            return $this->_hookCache[$name];
        }
        $exclude_list = [];
        $result = [];

        foreach ($this->_excludeHooks as $hook => $callables) {
            if (preg_match($hook, $name))
                $exclude_list = array_merge($callables);
        }

        foreach ($this->_hooks as $hook => $_callables) {
            if (preg_match($hook, $name)) {
                foreach ($_callables as $priority => $callables) {
                    foreach ($callables as $callable) {
                        if (!in_array($callable, $exclude_list)){
                            $result[] = (object) ['callable' => $callable, 'priority' => (float) $priority];
                        }
                    }
                }
            }
        }

        usort($result, function($a,$b){
            if($a->priority > $b->priority){
                return 1;
            } elseif ($a->priority < $b->priority) {
                return -1;
            } else {
                return 0;
            }
        });

        $result = array_map(function($el) { return $el->callable; }, $result);

        $this->_hookCache[$name] = $result;

        return $result;
    }

    protected function _compile(string $hook):string {
        $hook = trim($hook);

        if ($hook[0] === '-')
            $hook = substr($hook, 1);

        $replaces = [];

        while (preg_match("#\<\<([^<>]+)\>\>#", $hook, $matches)) {
            $uid = uniqid('@');
            $replaces[$uid] = $matches;

            $hook = str_replace($matches[0], $uid, $hook);
        }

        $hook = '#^' . preg_quote($hook) . '$#i';

        foreach ($replaces as $uid => $matches) {
            $regex = str_replace('*', '[^\(\)\:]*', $matches[1]);

            $hook = str_replace($uid, '(' . $regex . ')', $hook);
        }

        return $hook;
    }
}