<?php

/*
 * This file is part of the "EloGank League of Legends Replay Downloader" package.
 *
 * https://github.com/EloGank/lol-replay-downloader-cli
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EloGank\Replay\Command\Output;

use EloGank\Replay\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutput as BaseConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface as BaseOutputInterface;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class ConsoleOutput extends BaseConsoleOutput implements OutputInterface
{
    /**
     * @var BaseOutputInterface
     */
    protected $output;


    /**
     * @param BaseOutputInterface $output
     */
    public function __construct(BaseOutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @param array|string $messages
     * @param int          $type
     */
    public function writeln($messages, $type = self::OUTPUT_NORMAL)
    {
        return $this->output->writeln($messages, $type);
    }

    /**
     * @param array|string $messages
     * @param bool         $newline
     * @param int          $type
     */
    public function write($messages, $newline = false, $type = self::OUTPUT_NORMAL)
    {
        return $this->output->write($messages, $newline, $type);
    }
}