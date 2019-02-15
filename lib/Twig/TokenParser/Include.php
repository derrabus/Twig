<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 * (c) Armin Ronacher
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Includes a template.
 *
 *   {% include 'header.html' %}
 *     Body
 *   {% include 'footer.html' %}
 */
class Twig_TokenParser_Include extends \Twig\TokenParser\AbstractTokenParser
{
    public function parse(\Twig\Token $token)
    {
        $expr = $this->parser->getExpressionParser()->parseExpression();

        list($variables, $only, $ignoreMissing) = $this->parseArguments();

        return new \Twig\Node\IncludeNode($expr, $variables, $only, $ignoreMissing, $token->getLine(), $this->getTag());
    }

    protected function parseArguments()
    {
        $stream = $this->parser->getStream();

        $ignoreMissing = false;
        if ($stream->nextIf(\Twig\Token::NAME_TYPE, 'ignore')) {
            $stream->expect(\Twig\Token::NAME_TYPE, 'missing');

            $ignoreMissing = true;
        }

        $variables = null;
        if ($stream->nextIf(\Twig\Token::NAME_TYPE, 'with')) {
            $variables = $this->parser->getExpressionParser()->parseExpression();
        }

        $only = false;
        if ($stream->nextIf(\Twig\Token::NAME_TYPE, 'only')) {
            $only = true;
        }

        $stream->expect(\Twig\Token::BLOCK_END_TYPE);

        return [$variables, $only, $ignoreMissing];
    }

    public function getTag()
    {
        return 'include';
    }
}

class_alias('Twig_TokenParser_Include', 'Twig\TokenParser\IncludeTokenParser', false);
