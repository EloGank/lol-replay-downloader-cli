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

use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
interface FailureHandlerInterface
{
    /**
     * Executed on failure (error, exception, ...)
     *
     * @param OutputInterface $output
     * @param string          $region
     * @param int             $gameId
     * @param string          $encryptionKey
     * @param string          $replayFolderPath
     * @param \Exception      $exception
     *
     * @return null|bool If returns true, the exception will be thrown if the configuration "replay.command.exception.throw" is set to "true".
     */
    public function onFailure(
        OutputInterface $output,
        $region,
        $gameId,
        $encryptionKey,
        $replayFolderPath,
        \Exception $exception
    );
}
