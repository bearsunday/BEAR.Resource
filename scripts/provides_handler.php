<?php
/**
 * "Provides" annotation signal handler
 *
 *  this scripts provide parameter with "Provides" annotation provider
 *
 *  @return \Aura\Signal\Manager::STOP | null
 */
return function (
        $return,
        \ReflectionParameter $parameter,
        \Ray\Aop\ReflectiveMethodInvocation $invovation,
        \Ray\Di\Definition $definition
) {
    $class = $parameter->getDeclaringFunction()->class;
    $method = $parameter->getDeclaringFunction()->name;
    $msg = '$' . "{$parameter->name} in {$class}::{$method}()";
    /** @var Ray\Di\Definition $definition */
    $provideMethods = $definition->getUserAnnotationMethodName('Provides');
    if (is_null($provideMethods)) {
        goto PROVIDE_FAILD;
    }
    $parameterMethod = [];
    foreach ($provideMethods as $provideMethod) {
        $annotation = $definition->getUserAnnotationByMethod($provideMethod)['Provides'][0];
        $parameterMethod[$annotation->value] = $provideMethod;
    }
    $hasMethod = isset($parameterMethod[$parameter->name]);
    if ($hasMethod === false) {
        goto PROVIDE_FAILD;
    }
    $providesMethod = $parameterMethod[$parameter->name];
    $object = $invovation->getThis();
    $f = [$object, $providesMethod];
    $providedValue = $f();
    $return->value = $providedValue;
    return \Aura\Signal\Manager::STOP;
PROVIDE_FAILD:
    return null;
};