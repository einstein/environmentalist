<?php

namespace Environmentalist;

abstract class NamingConvention {

    static function underscore($class) {
        $namespaces = array_filter(preg_split('/\\\\|::/', $class));
        $segments = array();
        foreach ($namespaces as $namespace) {
            $path = preg_replace('/([A-Z]+)([A-Z][a-z])/', '\1_\2', $namespace);
            $path = preg_replace('/([a-z\d])([A-Z])/', '\1_\2', $path);
            $path = preg_replace('/[^A-Z^a-z^0-9]+/', '_', $path);
            $segments[] = strtolower($path);
        }
        return implode(DIRECTORY_SEPARATOR, $segments);
    }

}