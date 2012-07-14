<?php
namespace Ray\Di;

return new Injector(new Container(new Forge(new Config(new Annotation(new Definition)))));