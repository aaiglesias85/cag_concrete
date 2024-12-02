<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Runtime;

/**
 * @author Nicolas Grekas <p@tchwork.com>
 */
interface ResolverInterface
{
    /**
     * @return array{0: callable, 1: mixed[]}
     */
    public function resolve(): array;
}
