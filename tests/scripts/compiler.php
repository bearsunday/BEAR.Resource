<?php

namespace BEAR\Resource;

use Ray\Aop\Compiler;

return new Compiler(
    sys_get_temp_dir()
);
