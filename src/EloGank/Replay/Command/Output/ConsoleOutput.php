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
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\OutputInterface as BaseOutputInterface;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class ConsoleOutput implements OutputInterface, BaseOutputInterface
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
     * @inheritdoc
     */
    public function writeln($messages, $type = self::OUTPUT_TYPE_NORMAL)
    {
        return $this->output->writeln($messages, $type);
    }

    /**
     * @inheritdoc
     */
    public function write($messages, $newline = false, $type = self::OUTPUT_TYPE_NORMAL)
    {
        return $this->output->write($messages, $newline, $type);
    }

    /**
     * @inheritdoc
     */
    public function getFormatter()
    {
        return $this->output->getFormatter();
    }

    /**
     * @inheritdoc
     */
    public function getVerbosity()
    {
        return $this->output->getVerbosity();
    }

    /**
     * @inheritdoc
     */
    public function setVerbosity($level)
    {
        $this->output->setVerbosity($level);
    }

    /**
     * @inheritdoc
     */
    public function setDecorated($decorated)
    {
        return $this->output->setDecorated($decorated);
    }

    /**
     * @inheritdoc
     */
    public function isDecorated()
    {
        return $this->output->isDecorated();
    }

    /**
     * @inheritdoc
     */
    public function setFormatter(OutputFormatterInterface $formatter)
    {
        return $this->output->setFormatter($formatter);
    }
}
