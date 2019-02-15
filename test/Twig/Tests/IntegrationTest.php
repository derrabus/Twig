<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// This function is defined to check that escaping strategies
// like html works even if a function with the same name is defined.
function html()
{
    return 'foo';
}

class Twig_Tests_IntegrationTest extends \Twig\Test\IntegrationTestCase
{
    public function getExtensions()
    {
        $policy = new \Twig\Sandbox\SecurityPolicy([], [], [], [], []);

        return [
            new \Twig\Extension\DebugExtension(),
            new \Twig\Extension\SandboxExtension($policy, false),
            new \Twig\Extension\StringLoaderExtension(),
            new TwigTestExtension(),
        ];
    }

    public function getFixturesDir()
    {
        return __DIR__.'/Fixtures/';
    }
}

function test_foo($value = 'foo')
{
    return $value;
}

class TwigTestFoo implements Iterator
{
    const BAR_NAME = 'bar';

    public $position = 0;
    public $array = [1, 2];

    public function bar($param1 = null, $param2 = null)
    {
        return 'bar'.($param1 ? '_'.$param1 : '').($param2 ? '-'.$param2 : '');
    }

    public function getFoo()
    {
        return 'foo';
    }

    public function getSelf()
    {
        return $this;
    }

    public function is()
    {
        return 'is';
    }

    public function in()
    {
        return 'in';
    }

    public function not()
    {
        return 'not';
    }

    public function strToLower($value)
    {
        return strtolower($value);
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function current()
    {
        return $this->array[$this->position];
    }

    public function key()
    {
        return 'a';
    }

    public function next()
    {
        ++$this->position;
    }

    public function valid()
    {
        return isset($this->array[$this->position]);
    }
}

class TwigTestTokenParser_§ extends \Twig\TokenParser\AbstractTokenParser
{
    public function parse(\Twig\Token $token)
    {
        $this->parser->getStream()->expect(\Twig\Token::BLOCK_END_TYPE);

        return new \Twig\Node\PrintNode(new \Twig\Node\Expression\ConstantExpression('§', -1), -1);
    }

    public function getTag()
    {
        return '§';
    }
}

class TwigTestExtension extends \Twig\Extension\AbstractExtension
{
    public function getTokenParsers()
    {
        return [
            new TwigTestTokenParser_§(),
        ];
    }

    public function getFilters()
    {
        return [
            new \Twig\TwigFilter('§', [$this, '§Filter']),
            new \Twig\TwigFilter('escape_and_nl2br', [$this, 'escape_and_nl2br'], ['needs_environment' => true, 'is_safe' => ['html']]),
            new \Twig\TwigFilter('nl2br', [$this, 'nl2br'], ['pre_escape' => 'html', 'is_safe' => ['html']]),
            new \Twig\TwigFilter('escape_something', [$this, 'escape_something'], ['is_safe' => ['something']]),
            new \Twig\TwigFilter('preserves_safety', [$this, 'preserves_safety'], ['preserves_safety' => ['html']]),
            new \Twig\TwigFilter('static_call_string', 'TwigTestExtension::staticCall'),
            new \Twig\TwigFilter('static_call_array', ['TwigTestExtension', 'staticCall']),
            new \Twig\TwigFilter('magic_call', [$this, 'magicCall']),
            new \Twig\TwigFilter('magic_call_string', 'TwigTestExtension::magicStaticCall'),
            new \Twig\TwigFilter('magic_call_array', ['TwigTestExtension', 'magicStaticCall']),
            new \Twig\TwigFilter('*_path', [$this, 'dynamic_path']),
            new \Twig\TwigFilter('*_foo_*_bar', [$this, 'dynamic_foo']),
        ];
    }

    public function getFunctions()
    {
        return [
            new \Twig\TwigFunction('§', [$this, '§Function']),
            new \Twig\TwigFunction('safe_br', [$this, 'br'], ['is_safe' => ['html']]),
            new \Twig\TwigFunction('unsafe_br', [$this, 'br']),
            new \Twig\TwigFunction('static_call_string', 'TwigTestExtension::staticCall'),
            new \Twig\TwigFunction('static_call_array', ['TwigTestExtension', 'staticCall']),
            new \Twig\TwigFunction('*_path', [$this, 'dynamic_path']),
            new \Twig\TwigFunction('*_foo_*_bar', [$this, 'dynamic_foo']),
        ];
    }

    public function getTests()
    {
        return [
            new \Twig\TwigTest('multi word', [$this, 'is_multi_word']),
            new \Twig\TwigTest('test_*', [$this, 'dynamic_test']),
        ];
    }

    public function §Filter($value)
    {
        return "§{$value}§";
    }

    public function §Function($value)
    {
        return "§{$value}§";
    }

    /**
     * nl2br which also escapes, for testing escaper filters.
     */
    public function escape_and_nl2br($env, $value, $sep = '<br />')
    {
        return $this->nl2br(twig_escape_filter($env, $value, 'html'), $sep);
    }

    /**
     * nl2br only, for testing filters with pre_escape.
     */
    public function nl2br($value, $sep = '<br />')
    {
        // not secure if $value contains html tags (not only entities)
        // don't use
        return str_replace("\n", "$sep\n", $value);
    }

    public function dynamic_path($element, $item)
    {
        return $element.'/'.$item;
    }

    public function dynamic_foo($foo, $bar, $item)
    {
        return $foo.'/'.$bar.'/'.$item;
    }

    public function dynamic_test($element, $item)
    {
        return $element === $item;
    }

    public function escape_something($value)
    {
        return strtoupper($value);
    }

    public function preserves_safety($value)
    {
        return strtoupper($value);
    }

    public static function staticCall($value)
    {
        return "*$value*";
    }

    public function br()
    {
        return '<br />';
    }

    public function is_multi_word($value)
    {
        return false !== strpos($value, ' ');
    }

    public function __call($method, $arguments)
    {
        if ('magicCall' !== $method) {
            throw new \BadMethodCallException('Unexpected call to __call');
        }

        return 'magic_'.$arguments[0];
    }

    public static function __callStatic($method, $arguments)
    {
        if ('magicStaticCall' !== $method) {
            throw new \BadMethodCallException('Unexpected call to __callStatic');
        }

        return 'static_magic_'.$arguments[0];
    }
}

/**
 * This class is used in tests for the "length" filter and "empty" test. It asserts that __call is not
 * used to convert such objects to strings.
 */
class MagicCallStub
{
    public function __call($name, $args)
    {
        throw new \Exception('__call shall not be called');
    }
}

class ToStringStub
{
    /**
     * @var string
     */
    private $string;

    public function __construct($string)
    {
        $this->string = $string;
    }

    public function __toString()
    {
        return $this->string;
    }
}

/**
 * This class is used in tests for the length filter and empty test to show
 * that when \Countable is implemented, it is preferred over the __toString()
 * method.
 */
class CountableStub implements \Countable
{
    private $count;

    public function __construct($count)
    {
        $this->count = $count;
    }

    public function count()
    {
        return $this->count;
    }

    public function __toString()
    {
        throw new \Exception('__toString shall not be called on \Countables');
    }
}

/**
 * This class is used in tests for the length filter.
 */
class IteratorAggregateStub implements \IteratorAggregate
{
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }
}
