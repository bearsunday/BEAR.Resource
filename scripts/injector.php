<?php
namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationReader as Reader;

return new Injector(new Container(new Forge(new Config(new Annotation(new Definition, new Reader)))));