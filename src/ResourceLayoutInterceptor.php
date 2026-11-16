<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\AbstractResourceLayout;
use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\Exception\EmbedException;
use BEAR\Resource\Exception\ResourceLayoutException;
use Override;
use Ray\Aop\MethodInvocation;
use Ray\Aop\WeavedInterface;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionClassConstant;

use function array_map;
use function assert;
use function count;
use function in_array;
use function sprintf;

/**
 * A method #[Embed] of the same rel wins over a part, whichever of the two interceptors runs first.
 *
 * @psalm-import-type LayoutEmbedMap from Types
 */
final readonly class ResourceLayoutInterceptor implements ResourceLayoutInterceptorInterface
{
    public function __construct(
        private EmbedInterceptor $embedInterceptor,
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * @throws EmbedException
     * @throws ResourceLayoutException
     */
    #[Override]
    public function invoke(MethodInvocation $invocation)
    {
        $ro = $invocation->getThis();
        assert($ro instanceof ResourceObject);
        $class = new ReflectionClass($ro);
        if ($ro instanceof WeavedInterface) {
            $class = $class->getParentClass();
            assert($class instanceof ReflectionClass);
        }

        $layouts = $class->getAttributes(AbstractResourceLayout::class, ReflectionAttribute::IS_INSTANCEOF);
        if (count($layouts) > 1) {
            throw new ResourceLayoutException(sprintf('%s has more than one layout.', $class->name));
        }

        $embeds = [];
        $methodRels = array_map(
            static fn (ReflectionAttribute $embed): string => $embed->newInstance()->rel,
            $invocation->getMethod()->getAttributes(Embed::class),
        );
        foreach ($layouts as $layout) {
            /** @var class-string<AbstractResourceLayout> $layoutClass */
            $layoutClass = $layout->getName();
            foreach ($this->getParts($layoutClass) as $rel => $src) {
                if (in_array($rel, $methodRels, true)) {
                    continue;
                }

                $embeds[] = new Embed(rel: $rel, src: $src);
            }
        }

        $this->embedInterceptor->embedResource($embeds, $invocation);

        return $invocation->proceed();
    }

    /**
     * @param class-string<AbstractResourceLayout> $layout
     *
     * @return LayoutEmbedMap
     *
     * @throws ResourceLayoutException
     */
    private function getParts(string $layout): array
    {
        $parts = new ReflectionClassConstant($layout, 'EMBED');
        if ($parts->getDeclaringClass()->name === AbstractResourceLayout::class) {
            throw new ResourceLayoutException(sprintf('%s does not declare EMBED.', $layout));
        }

        /** @var LayoutEmbedMap $embed */
        $embed = $parts->getValue();

        return $embed;
    }
}
