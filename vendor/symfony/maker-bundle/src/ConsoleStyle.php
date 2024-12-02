<?php

/*
 * This file is part of the Symfony MakerBundle package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\MakerBundle;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 * @author Ryan Weaver <weaverryan@gmail.com>
 */
final class ConsoleStyle extends SymfonyStyle
{
    public function __construct(
        InputInterface $input,
        private OutputInterface $output,
    ) {
        parent::__construct($input, $output);
    }

    public function success($message): void
    {
        $this->writeln('<fg=green;options=bold,underscore>OK</> '.$message);
    }

    public function comment($message): void
    {
        $this->text($message);
    }

    public function getOutput(): OutputInterface
    {
        return $this->output;
    }
}
