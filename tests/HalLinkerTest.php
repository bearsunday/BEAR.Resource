<?php

declare(strict_types=1);

namespace BEAR\Resource;

use ArrayObject;
use BEAR\Resource\Annotation\Link;
use BEAR\Resource\Exception\LinkException;
use BEAR\Resource\Module\EmbedResourceModule;
use BEAR\Resource\Module\HalModule;
use BEAR\Resource\Module\ResourceModule;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Ray\Aop\WeavedInterface;
use Ray\Di\Injector;

use function json_decode;

use const JSON_THROW_ON_ERROR;

class HalLinkerTest extends TestCase
{
    private HalLinker $linker;

    protected function setUp(): void
    {
        $this->linker = new HalLinker(new class implements ReverseLinkerInterface {
            /** @param array<string, mixed> $query */
            public function __invoke(string $uri, array $query): string
            {
                return '/r' . $uri;
            }
        });
    }

    public function testExpandHrefPassesTheExpandedQueryToTheReverseLinker(): void
    {
        $reverseLinker = new FakeQueryRecordingReverseLinker();
        $linker = new HalLinker($reverseLinker);

        $href = $linker->expandHref(new Link(rel: 'blog', href: '/blog{?id,tag}'), ['id' => 1, 'tag' => 'php']);

        $this->assertSame('/blog?id=1&tag=php', $href);
        $this->assertSame([['id' => '1', 'tag' => 'php']], $reverseLinker->queries);
    }

    public function testExpandHrefWithoutQueryPassesAnEmptyQuery(): void
    {
        $reverseLinker = new FakeQueryRecordingReverseLinker();
        $linker = new HalLinker($reverseLinker);

        $href = $linker->expandHref(new Link(rel: 'blog', href: '/blog/{id}'), ['id' => 1]);

        $this->assertSame('/blog/1', $href);
        $this->assertSame([[]], $reverseLinker->queries);
    }

    public function testLinkIsTheHalLink(): void
    {
        $ro = $this->fakeHalHref(['page' => 2]);

        $this->assertSame(['href' => '/r/posts?page=2', 'title' => 'Next page'], $this->linker->link($ro, 'next'));
        $this->assertSame($this->halLink($ro, $this->linker, 'next'), $this->linker->link($ro, 'next'));
    }

    public function testLinkPassesTheExpandedQueryToTheReverseLinker(): void
    {
        $reverseLinker = new FakeQueryRecordingReverseLinker();
        $ro = $this->fakeHalHref(['page' => 2]);

        (new HalLinker($reverseLinker))->link($ro, 'next');

        $this->assertSame([['page' => '2']], $reverseLinker->queries);
    }

    public function testAMissingVariableIsLeftOut(): void
    {
        $ro = $this->fakeHalHref([]);

        $this->assertSame('/r/posts', $this->linker->link($ro, 'next')['href']);
        $this->assertSame($this->halLink($ro, $this->linker, 'next'), $this->linker->link($ro, 'next'));
    }

    public function testAnUntitledLinkHasAnEmptyTitle(): void
    {
        $ro = $this->fakeHalHref([]);

        $this->assertSame(['href' => '/r/posts', 'title' => ''], $this->linker->link($ro, 'first'));
        $this->assertSame(['href' => '/r/posts'], $this->halLink($ro, $this->linker, 'first'));
    }

    public function testABodyLinkCarriesItsOwnTitle(): void
    {
        $ro = $this->fakeHalHref(['_links' => ['next' => ['href' => '/override', 'title' => 'Override']]]);

        $this->assertSame(['href' => '/override', 'title' => 'Override'], $this->linker->link($ro, 'next'));
        $this->assertSame($this->halLink($ro, $this->linker, 'next'), $this->linker->link($ro, 'next'));
    }

    public function testABodyLinkWithoutATitleDoesNotTakeTheDeclaredTitle(): void
    {
        $ro = $this->fakeHalHref(['page' => 2, '_links' => ['next' => ['href' => '/override']]]);

        $this->assertSame(['href' => '/override', 'title' => ''], $this->linker->link($ro, 'next'));
        $this->assertSame(['href' => '/override'], $this->halLink($ro, $this->linker, 'next'));
    }

    public function testABodyLinkTheMethodDoesNotDeclareIsReturned(): void
    {
        $ro = $this->fakeHalHref(['_links' => ['author' => ['href' => '/author/1']]]);

        $this->assertSame(['href' => '/author/1', 'title' => ''], $this->linker->link($ro, 'author'));
        $this->assertSame(['href' => '/author/1'], $this->halLink($ro, $this->linker, 'author'));
    }

    #[DataProvider('hrefsThatAreNotStrings')]
    public function testABodyLinkWithoutAStringHrefTakesTheDeclaredLink(mixed $href): void
    {
        $ro = $this->fakeHalHref(['page' => 2, '_links' => ['next' => ['href' => $href, 'title' => 'Override']]]);

        $this->assertSame(['href' => '/r/posts?page=2', 'title' => 'Next page'], $this->linker->link($ro, 'next'));
    }

    /** @return iterable<string, array{mixed}> */
    public static function hrefsThatAreNotStrings(): iterable
    {
        yield 'an int' => [1];
        yield 'an array' => [['/override']];
    }

    #[DataProvider('titlesThatAreNotStrings')]
    public function testABodyTitleThatIsNotAStringIsEmpty(mixed $title): void
    {
        $ro = $this->fakeHalHref(['_links' => ['next' => ['href' => '/override', 'title' => $title]]]);

        $this->assertSame(['href' => '/override', 'title' => ''], $this->linker->link($ro, 'next'));
    }

    /** @return iterable<string, array{mixed}> */
    public static function titlesThatAreNotStrings(): iterable
    {
        yield 'an int' => [1];
        yield 'null' => [null];
    }

    #[DataProvider('bodiesThatAreNotArrays')]
    public function testABodyThatIsNotAnArrayExpandsNoVariable(object $body): void
    {
        $ro = $this->fakeHalHref([]);
        $ro->body = $body;

        $this->assertSame(['href' => '/r/posts', 'title' => 'Next page'], $this->linker->link($ro, 'next'));
    }

    /** @return iterable<string, array{object}> */
    public static function bodiesThatAreNotArrays(): iterable
    {
        yield 'an object' => [(object) ['page' => 2]];
        yield 'an ArrayObject' => [new ArrayObject(['page' => 2])];
    }

    public function testAnUndeclaredRelThrows(): void
    {
        $ro = $this->fakeHalHref([]);

        $this->expectException(LinkException::class);
        $this->expectExceptionMessage('rel:prev class:' . FakeHalHref::class);

        $this->linker->link($ro, 'prev');
    }

    public function testAMethodTheResourceDoesNotHaveThrows(): void
    {
        $ro = $this->fakeHalHref([]);
        $ro->uri->method = 'put';

        $this->expectException(LinkException::class);

        $this->linker->link($ro, 'next');
    }

    public function testAWeavedResourceObject(): void
    {
        $injector = new Injector(new HalModule(new EmbedResourceModule(new ResourceModule('FakeVendor\Sandbox'))), __DIR__ . '/tmp');
        $linker = $injector->getInstance(HalLinker::class);
        $ro = $injector->getInstance(ResourceInterface::class)->get('app://self/bird/birds', ['id' => '1']);

        $this->assertInstanceOf(WeavedInterface::class, $ro);
        $this->assertSame('app://self/bird/suzume', $linker->link($ro, 'bird3')['href']);
        $this->assertSame($this->halLink($ro, $linker, 'bird3')['href'], $linker->link($ro, 'bird3')['href']);
    }

    /** @param array<string, mixed> $body */
    private function fakeHalHref(array $body): FakeHalHref
    {
        $ro = new FakeHalHref();
        $ro->uri = new Uri('app://self/posts');
        $ro->body = $body;

        return $ro;
    }

    /** @return array{href: string, title?: string} */
    private function halLink(ResourceObject $ro, HalLinker $linker, string $rel): array
    {
        (new HalRenderer($linker))->renderHal($ro);
        /** @var array{_links: array<string, array{href: string, title?: string}>} $hal */
        $hal = json_decode((string) $ro->view, true, 512, JSON_THROW_ON_ERROR);

        return $hal['_links'][$rel];
    }
}
