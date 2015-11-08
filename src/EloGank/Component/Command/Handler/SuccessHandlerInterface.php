<?php

/*
 * This file is part of the "EloGank League of Legends Replay Downloader" package.
 *
 * https://github.com/EloGank/lol-replay-downloader-cli
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EloGank\Component\Command\Handler;

use EloGank\Replay\ReplayInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
interface SuccessHandlerInterface
{
    /**
     * Executed on success. Throws exception to stop the process, then failure handler will be thrown.
     *
     * @param OutputInterface $output
     * @param ReplayInterface $replay
     * @param string          $replayFolderPath
     */
    public function onSuccess(OutputInterface $output, ReplayInterface $replay, $replayFolderPath);
}
