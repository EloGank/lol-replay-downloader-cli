<?php

namespace EloGank\Component\Command\Handler;

use EloGank\Replay\ReplayInterface;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
interface FailureHandlerInterface
{
    /**
     * Executed on failure (error, exception, ...)
     *
     * @param ReplayInterface $replay
     * @param \Exception      $exception
     */
    public function onFailure(ReplayInterface $replay, \Exception $exception);
} 