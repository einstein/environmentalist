<?php

class NamingConventionTest extends ztest\UnitTestCase {

    function setup() {
        $this->class = 'Environmentalist\NamingConvention';
    }

    function test_static_underscore() {
        $assertions = array(
            'User'                            => 'user',
            'user'                            => 'user',
            'USER'                            => 'user',
            'User99'                          => 'user99',
            'Namespaced\User'                 => 'namespaced/user',
            'Namespaced::User'                => 'namespaced/user',
            '\Namespaced\User'                => 'namespaced/user',
            '\Namespaced::User'               => 'namespaced/user',
            '::Namespaced::User'              => 'namespaced/user',
            '::Namespaced\User'               => 'namespaced/user',
            'CamelCased\User'                 => 'camel_cased/user',
            'under_scored\User'               => 'under_scored/user',
            'CamelCase_and_Under_Scored\User' => 'camel_case_and_under_scored/user'
        );
        $method = array($this->class, 'underscore');
        foreach ($assertions as $class => $filename) assert_equal($filename, call_user_func($method, $class));
    }

}