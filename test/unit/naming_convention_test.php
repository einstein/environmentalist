<?php

class NamingConventionTest extends ztest\UnitTestCase {

    function setup() {
        $this->class = 'Environmentalist\NamingConvention';
    }

    function test_static_psr_0() {
        $assertions = array(
            'User'                            => 'User',
            'user'                            => 'user',
            'USER'                            => 'USER',
            'User99'                          => 'User99',
            'User_99'                         => 'User/99',
            'Namespaced\User'                 => 'Namespaced/User',
            'Namespaced::User'                => 'Namespaced/User',
            '\Namespaced\User'                => 'Namespaced/User',
            '\Namespaced::User'               => 'Namespaced/User',
            '::Namespaced::User'              => 'Namespaced/User',
            '::Namespaced\User'               => 'Namespaced/User',
            'CamelCased\User'                 => 'CamelCased/User',
            'under_scored\User'               => 'under_scored/User',
            'CamelCase_and_Under_Scored\User' => 'CamelCase_and_Under_Scored/User',
            '\Multi\Namespaced\User'          => 'Multi/Namespaced/User',
            'namespaced\With_Underscore'      => 'namespaced/With/Underscore'
        );
        $method = array($this->class, 'psr_0');
        foreach ($assertions as $class => $filename) assert_equal($filename, call_user_func($method, $class));
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
            'CamelCase_and_Under_Scored\User' => 'camel_case_and_under_scored/user',
            '\Multi\Namespaced\User'          => 'multi/namespaced/user'
        );
        $method = array($this->class, 'underscore');
        foreach ($assertions as $class => $filename) assert_equal($filename, call_user_func($method, $class));
    }

}