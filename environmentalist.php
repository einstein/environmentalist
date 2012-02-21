<?php

/**
 * Cleans up your PHP environment by managing include paths, error handlers, and autoloading
**/
abstract class Environmentalist {

    const SPL_AUTOLOAD_EXTENSION_SEPARATOR = ',';

    /**
     * Stores registered custom error handlers.
    **/
    static protected $error_handlers = array();

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
     * Appends a custom error handler with the Environment class.
     * If your custom error handler returns false, the error is passed to the next error handler registered with Environment.
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
     * Default autoload implementation.
     * Requires a file with the underscored version of a class name and subdirectories for each namespace.
     *
     * <code>
     * Environment::autoload('ActiveRecord\Base');  # => include 'active_record/base.php';
     * </code>
     *
     * @param string $class
     * @return void
     * @static
    **/
    static function autoload($class) {
        $filename = self::filename_for_class($class);
        foreach (self::autoload_extensions() as $extension) {
            if ($file = self::resolve_include_path($filename.$extension)) {
                include $file;
                break;
            }
        }
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
     * Default error handler implementation.
     * Passes errors to the handlers registered with the Environment class.
     * If all custom handlers do not handle the error, then the error is passed to the default php handler.
     *
     * @param string $number
     * @param string $message
     * @param string $file
     * @param string $line
     * @param string $context
     * @return void | boolean
    **/
    static function error_handler($number, $message, $file, $line, &$context) {
        foreach (self::$error_handlers as $handler) {
            if (call_user_func($handler, $number, $message, $file, $line, $context) !== false) return;
        }
        return false;
    }

    /**
     * Returns an array of registered error handlers.
     *
     * @return array
    **/
    static function error_handlers() {
        return self::$error_handlers;
    }

    /**
     * Returns the underscored version of $class prefixed with its namespaces as directories
     *
     * <code>
     * Environment::filename_for_class('ActiveRecord\Base');  # => 'active_record/base'
     * </code>
     *
     * @param string $class
     * @return string
    **/
    static function filename_for_class($class) {
        $namespaces = array_filter(preg_split('#\\\\|::#', $class));
        $parts = array_map(function($namespace) { return strtolower(preg_replace('/[^A-Z^a-z^0-9]+/', '_', preg_replace('/([a-z\d])([A-Z])/', '\1_\2', preg_replace('/([A-Z]+)([A-Z][a-z])/', '\1_\2', $namespace)))); }, $namespaces);
        return implode(DIRECTORY_SEPARATOR, $parts);
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
     * Returns the fully resolved include path of $filename, or false if it doesn't exist in include_paths.
     *
     * @param string $filename
     * @return string | false
    **/
    static function resolve_include_path($filename) {
        $resolved_include_path = false;
        if (function_exists('stream_resolve_include_path')) {
            $resolved_include_path = stream_resolve_include_path($filename);
        } else {
            foreach (self::include_paths() as $include_path) {
                $file = realpath($include_path.DIRECTORY_SEPARATOR.$filename);
                if ($file && file_exists($file)) {
                    $resolved_include_path = $file;
                    break;
                }
            }
        }
        return $resolved_include_path;
    }

    /**
     * Sets spl_autoload_extensions to the string or array of extensions specified.
     * Returns the old spl_autoload_extensions.
     *
     * @param string | array $extensions
     * @return string
    **/
    static function set_autoload_extensions($extensions) {
        if (is_array($extensions)) $extensions = implode(self::SPL_AUTOLOAD_EXTENSION_SEPARATOR, $extensions);
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
        self::$error_handlers = $handlers;
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
        if (is_array($paths)) $paths = implode(PATH_SEPARATOR, $paths);
        return set_include_path($paths);
    }

}

spl_autoload_register('Environmentalist::autoload');
set_error_handler('Environmentalist::error_handler');