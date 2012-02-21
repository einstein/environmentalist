# Environmentalist

Cleans up your PHP environment by managing include paths, error handlers, and autoloading


## Usage

### Enabling/disabling

`Environmentalist::enable()` is called when loaded. If you'd like to disable its behavior, simply call `Environmentalist::disable()`.

### Autoload extensions

    Environmentalist::autoload_extensions()
    Environmentalist::append_autoload_extension($extension)
    Environmentalist::prepend_autoload_extension($extension)
    Environmentalist::set_autoload_extensions($extensions)

### Error handlers

    Environmentalist::error_handlers()
    Environmentalist::append_error_handler($handler)
    Environmentalist::prepend_error_handler($handler)
    Environmentalist::set_error_handlers($handlers)

### Include paths

    Environmentalist::include_paths()
    Environmentalist::append_include_path($path)
    Environmentalist::prepend_include_path($path)
    Environmentalist::set_include_paths($paths)


## Testing

`Environmentalist` tests require [jaz303/ztest](http://github.com/jaz303/ztest)

Simply download it to `environmentalist/test/ztest` (or anywhere else in your PHP `include_path`), then run `test/run`


## Todo

* Update README documentation
* Allow other filename naming conventions to be registered
* Consider using `glob()` in `Environmentalist::resolve_include_path()`