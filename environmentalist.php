<?php

require 'lib/environmentalist.php';
require 'lib/environmentalist/naming_convention.php';

Environmentalist::enable();
Environmentalist::append_naming_convention('Environmentalist\NamingConvention::underscore');