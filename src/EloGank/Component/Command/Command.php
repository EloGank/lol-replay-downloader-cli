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
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
abstract class Command extends BaseCommand
{
    const OUTPUT_COLOR_BLACK   = 'black';
    const OUTPUT_COLOR_RED     = 'red';
    const OUTPUT_COLOR_GREEN   = 'green';
    const OUTPUT_COLOR_YELLOW  = 'yellow';
    const OUTPUT_COLOR_BLUE    = 'blue';
    const OUTPUT_COLOR_MAGENTA = 'magenta';
    const OUTPUT_COLOR_CYAN    = 'cyan';
    const OUTPUT_COLOR_WHITE   = 'white';

    const OUTPUT_STYLE_BOLD      = 'bold';
    const OUTPUT_STYLE_UNDERLINE = 'underscore';
    const OUTPUT_STYLE_BLINK     = 'blink';
    const OUTPUT_STYLE_REVERSE   = 'reverse';
    const OUTPUT_STYLE_CONCEAL   = 'conceal';

    /**
     * Write a section title
     *
     * @param OutputInterface $output
     * @param string          $message
     * @param string          $bg
     * @param string          $fg
     * @param string|array    $styles
     */
    protected function writeSection(OutputInterface $output, $message = null, $bg = null, $fg = null, $styles = null)
    {
        if (null != $styles && !is_array($styles)) {
            $styles = [$styles];
        }
        elseif (null == $styles) {
            $styles = [];
        }

        $style = new OutputFormatterStyle($fg, $bg, $styles);
        $output->getFormatter()->setStyle('color', $style);

        $sectionLength = 80;
        $section = str_pad('[', $sectionLength - 1, ' ') . ']';
        $output->writeln(array(
            '',
            '<color>' . $section . '</color>'
        ));

        if (null != $message) {
            $titleLength = strlen($message);
            $length = ($sectionLength - $titleLength) / 2;
            $output->writeln(array(
                '<color>' . str_pad('[', $length, ' ') . $message . str_pad('', $sectionLength - $titleLength - (0 != $titleLength % 2 ? $length : $length + 1), ' ') . ']</color>',
                '<color>' . $section . '</color>'
            ));
        }

        $output->writeln('');
    }

    /**
     * @param OutputInterface $output
     * @param string          $message
     */
    protected function error(OutputInterface $output, $message)
    {
        $this->writeSection($output, $message, self::OUTPUT_COLOR_RED, self::OUTPUT_COLOR_WHITE, ['bold']);
    }

    /**
     * @param OutputInterface $output
     * @param string          $message
     */
    protected function success(OutputInterface $output, $message)
    {
        $this->writeSection($output, $message, self::OUTPUT_COLOR_GREEN, self::OUTPUT_COLOR_BLACK);
    }

    /**
     * @param OutputInterface $output
     * @param string          $message
     */
    protected function warning(OutputInterface $output, $message)
    {
        $this->writeSection($output, $message, self::OUTPUT_COLOR_YELLOW, self::OUTPUT_COLOR_BLACK);
    }

    /**
     * @param OutputInterface $output
     * @param string          $message
     */
    protected function info(OutputInterface $output, $message)
    {
        $this->writeSection($output, $message, self::OUTPUT_COLOR_BLUE, self::OUTPUT_COLOR_WHITE);
    }

    /**
     * @param OutputInterface $output
     * @param string          $message
     */
    protected function notice(OutputInterface $output, $message)
    {
        $this->writeSection($output, $message, self::OUTPUT_COLOR_WHITE, self::OUTPUT_COLOR_BLACK);
    }
}