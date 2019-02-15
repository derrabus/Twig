<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Deprecates a section of a template.
 *
 *    {% deprecated 'The "base.twig" template is deprecated, use "layout.twig" instead.' %}
 *    {% extends 'layout.html.twig' %}
 *
 * @author Yonel Ceruto <yonelceruto@gmail.com>
 *
 * @final
 */
class Twig_TokenParser_Deprecated extends \Twig\TokenParser\AbstractTokenParser
{
    public function parse(\Twig\Token $token)
    {
        $expr = $this->parser->getExpressionParser()->parseExpression();

        $this->parser->getStream()->expect(\Twig\Token::BLOCK_END_TYPE);

        return new \Twig\Node\DeprecatedNode($expr, $token->getLine(), $this->getTag());
    }

    public function getTag()
    {
        return 'deprecated';
    }
}

class_alias('Twig_TokenParser_Deprecated', 'Twig\TokenParser\DeprecatedTokenParser', false);
