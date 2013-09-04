<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\SignalHandler;

use Ray\Aop\ReflectiveMethodInvocation;
use Ray\Di\Definition;
use Aura\Signal\Manager as Signal;
use ReflectionParameter;

/**
 * [At]Provides parameter handler
 *
 * If class has "Provides" annotated method, call that method and return value.
 */
class Provides implements HandleInterface
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(
        $return,
        ReflectionParameter $parameter,
        ReflectiveMethodInvocation $invocation,
        Definition $definition
    ) {
        /** @var \Ray\Di\Definition $definition */
        $provideMethods = $definition->getUserAnnotationMethodName('Provides');
        if (is_null($provideMethods)) {
            // failed
            return null;
        }
        $parameterMethod = [];
        foreach ($provideMethods as $provideMethod) {
            $annotation = $definition->getUserAnnotationByMethod($provideMethod)['Provides'][0];
            $parameterMethod[$annotation->value] = $provideMethod;
        }
        $hasMethod = isset($parameterMethod[$parameter->name]);
        if ($hasMethod === false) {
            // failed
            return null;
        }

        SUCCESS: {
            $providesMethod = $parameterMethod[$parameter->name];
            $object = $invocation->getThis();
            $func = [$object, $providesMethod];
            /** @var $func callable  */
            $providedValue = $func();
            $return->value = $providedValue;

            return Signal::STOP;
        }
    }
}
