<?php

/*
 * This file is part of the "EloGank League of Legends Replay Downloader" package.
 *
 * https://github.com/EloGank/lol-replay-downloader-cli
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EloGank\Component\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
abstract class Command extends BaseCommand
{
    /**
     * Write a section title
     *
     * @param OutputInterface $output
     * @param string|null     $sectionTitle
     */
    protected function writeSection(OutputInterface $output, $sectionTitle = null)
    {
        $sectionLength = 80;
        $section = str_pad('[', $sectionLength - 1, '=') . ']';
        $output->writeln(array(
            '',
            $section
        ));

        if (null != $sectionTitle) {
            $length = ($sectionLength - strlen($sectionTitle)) / 2;
            // FIXME the ending length can be too long
            $output->writeln(array(
                str_pad('[', $length, ' ') . $sectionTitle . str_pad('', $sectionLength - strlen($sectionTitle) - $length, ' ') . ']',
                $section
            ));
        }

        $output->writeln('');
    }
}