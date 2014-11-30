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

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
interface FailureHandlerInterface
{
    /**
     * Executed on failure (error, exception, ...)
     *
     * @param string     $region
     * @param int        $gameId
     * @param string     $encryptionKey
     * @param string     $replayFolderPath
     * @param \Exception $exception
     */
    public function onFailure($region, $gameId, $encryptionKey, $replayFolderPath, \Exception $exception);
} 