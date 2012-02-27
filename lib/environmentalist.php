<?php

/**
 * Cleans up your PHP environment by managing include paths, error handlers, and autoloading
**/
abstract class Environmentalist {

    const SPL_AUTOLOAD_EXTENSION_SEPARATOR = ',';

    /**
     * Boolean which determines if this class' behavior has been enabled.
    **/
    static protected $enabled = false;

    /**
     * Stores registered error handlers.
    **/
    static protected $error_handlers = array();

    /**
     * Stores registered naming conventions.
    **/
    static protected $naming_conventions = array();

    /**
     * Stores the previously registered error handler if there was one.
    **/
    static protected $previous_error_handler;

    /**
     * Appends an extension to the end of spl_autoload_extensions.
     * Returns the new spl_autoload_extensions string.
     *
     * @param string $extension
     * @return string
    **/
    static function append_autoload_extension($extension) {
        return self::set_autoload_extensions(array_merge(self::autoload_extensions(), func_get_args()));
    }

    /**
     * Appends a handler to the error handlers stack.
     * If your custom error handler returns false, the error is passed to the next registered error handler.
     * Returns the old registered error handlers.
     *
     * @link http://us.php.net/set_error_handler#function.set-error-handler.parameters
     *
     * @param string | array $handler
     * @return array
    **/
    static function append_error_handler($handler) {
        return self::set_error_handlers(array_merge(self::$error_handlers, func_get_args()));
    }

    /**
     * Appends a path to the end of the include_path.
     * Returns the old include_path or false on failure.
     *
     * @param string $path
     * @return string
    **/
    static function append_include_path($path) {
        return self::set_include_paths(array_merge(self::include_paths(), func_get_args()));
    }

    /**
     * Appends a callback to the end of the naming conventions stack.
     *
     * @param string | array | closure $callback
     * @return array
    **/
    static function append_naming_convention($callback) {
        return self::set_naming_conventions(array_merge(self::naming_conventions(), func_get_args()));
    }

    /**
     * Default autoload implementation.
     * Includes a file with the underscored version of a class name and subdirectories for each namespace.
     *
     * <code>
     * Environmentalist::autoload('ActiveRecord\Base');  # => include 'active_record/base.php';
     * </code>
     *
     * @param string $class
     * @return void
    **/
    static function autoload($class) {
        $filenames = self::filenames_for_class($class);
        if ($file = self::resolve_include_path($filenames)) include $file;
    }

    /**
     * Returns an array of autoload extensions defined with spl_autoload_extensions.
     *
     * @return array
    **/
    static function autoload_extensions() {
        return preg_split('#\s*'.self::SPL_AUTOLOAD_EXTENSION_SEPARATOR.'\s*#', spl_autoload_extensions());
    }

    /**
     * Disables this class' behavior by unregistering its autoload method and restoring the previous error handler.
     *
     * @return void
    **/
    static function disable() {
        if (self::$enabled) {
            spl_autoload_unregister(__CLASS__.'::autoload');
            if (self::$previous_error_handler) {
                set_error_handler(self::$previous_error_handler);
                unset(self::$previous_error_handler);
            } else {
                restore_error_handler();
            }
            self::$enabled = false;
        }
    }

    /**
     * Enables this class' behavior by registering its autoload and error handler methods.
     *
     * @return void
    **/
    static function enable() {
        if (!self::$enabled) {
            spl_autoload_register(__CLASS__.'::autoload');
            self::$previous_error_handler = set_error_handler(__CLASS__.'::error_handler');
            self::$enabled = true;
        }
    }

    /**
     * Default error handler implementation.
     * Passes errors to the handlers registered with the Environment class.
     * If all custom handlers do not handle the error, then the error is passed to the previous error handler.
     * If one doesn't exist, then the default php error handler is called.
     *
     * @param string $number
     * @param string $message
     * @param string $file
     * @param string $line
     * @param string $context
     * @return void | boolean
    **/
    static function error_handler($number, $message, $file, $line, $context) {
        $arguments = func_get_args();
        foreach (self::$error_handlers as $handler) if (call_user_func_array($handler, $arguments) !== false) return;
        return self::$previous_error_handler ? call_user_func_array(self::$previous_error_handler, $arguments) : false;
    }

    /**
     * Returns an array of registered error handlers.
     *
     * @return array
    **/
    static function error_handlers() {
        return self::$error_handlers;
    }

    static function filenames_for_class($class) {
        $filenames = array();
        foreach (self::naming_conventions() as $convention) $filenames[] = call_user_func($convention, $class);
        return $filenames;
    }

    /**
     * Returns an array of the current include paths.
     *
     * @return array
    **/
    static function include_paths() {
        return explode(PATH_SEPARATOR, get_include_path());
    }

    /**
     * Returns an array of registered naming conventions.
     *
     * @return array
    **/
    static function naming_conventions() {
        return self::$naming_conventions;
    }

    /**
     * Prepends an extension to the beginning of spl_autoload_extensions.
     * Returns the old spl_autoload_extensions string.
     *
     * @param string $extension
     * @return string
    **/
    static function prepend_autoload_extension($extension) {
        return self::set_autoload_extensions(array_merge(func_get_args(), self::autoload_extensions()));
    }

    /**
     * Prepends a custom error handler with the Environment class.
     * If your custom error handler returns false, the error is passed to the next error handler registered with Environment.
     * Returns the old registered error handlers.
     *
     * @link http://us.php.net/set_error_handler#function.set-error-handler.parameters
     *
     * @param string | array $handler
     * @return void
    **/
    static function prepend_error_handler($handler) {
        return self::set_error_handlers(array_merge(func_get_args(), self::$error_handlers));
    }

    /**
     * Prepends a path to the beginning of the include_path.
     * Returns the old include_path or false on failure.
     *
     * @param string $path
     * @return string
    **/
    static function prepend_include_path($path) {
        return self::set_include_paths(array_merge(func_get_args(), self::include_paths()));
    }

    /**
     * Removes an extension from the spl_autoload_extensions.
     * Returns the old spl_autoload_extensions string.
     *
     * @param string $extension
     * @return string
    **/
    static function remove_autoload_extension($extension) {
        return self::set_autoload_extensions(array_diff(self::autoload_extensions(), func_get_args()));
    }

    /**
     * Removes an extension from the spl_autoload_extensions.
     * Returns the old registered error handlers.
     *
     * @param string | array $handler
     * @return array
    **/
    static function remove_error_handler($handler) {
        return self::set_error_handlers(array_diff(self::error_handlers(), func_get_args()));
    }

    /**
     * Removes a path from the include_path.
     * Returns the old include_path or false on failure.
     *
     * @param string $path
     * @return string
    **/
    static function remove_include_path($path) {
        return self::set_include_paths(array_diff(self::include_paths(), func_get_args()));
    }

    /**
     * Returns the first matched fully resolved include path of $filenames, or false if one doesn't exist in include_paths.
     *
     * @param string | array $filenames
     * @return string | false
    **/
    static function resolve_include_path($filenames) {
        $pattern = '{'.implode(',', self::include_paths()).'}';
        $pattern .= DIRECTORY_SEPARATOR;
        $pattern .= '{'.implode(',', (array) $filenames).'}';
        $pattern .= '{'.implode(',', self::autoload_extensions()).'}';
        $matches = glob($pattern, GLOB_BRACE | GLOB_NOSORT);
        return empty($matches) ? false : $matches[0];
    }

    /**
     * Sets spl_autoload_extensions to the string or array of extensions specified.
     * Returns the old spl_autoload_extensions.
     *
     * @param string | array $extensions
     * @return string
    **/
    static function set_autoload_extensions($extensions) {
        if (is_array($extensions)) $extensions = implode(self::SPL_AUTOLOAD_EXTENSION_SEPARATOR, array_unique($extensions));
        $old_spl_autoload_extensions = spl_autoload_extensions();
        spl_autoload_extensions($extensions);
        return $old_spl_autoload_extensions;
    }

    /**
     * Sets the current registered error handlers to the array of handlers specified.
     * Returns the old registered error handlers.
     *
     * @param array $handlers
     * @return array
    **/
    static function set_error_handlers($handlers) {
        $old_handlers = self::error_handlers();
        self::$error_handlers = array_unique($handlers);
        return $old_handlers;
    }

    /**
     * Replaces the current include_path with $paths.
     * Returns the old include_path or false on failure.
     *
     * @param string | array $paths
     * @return string
    **/
    static function set_include_paths($paths) {
        if (is_array($paths)) $paths = implode(PATH_SEPARATOR, array_unique($paths));
        return set_include_path($paths);
    }

    static function set_naming_conventions($callbacks) {
        $old_callbacks = self::naming_conventions();
        self::$naming_conventions = array_unique($callbacks);
        return $old_callbacks;
    }

}