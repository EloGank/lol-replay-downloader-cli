<?php

namespace EloGank\Component\Command\Handler;

use EloGank\Replay\ReplayInterface;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
interface SuccessHandlerInterface
{
    /**
     * Executed on success. Throws exception to stop the process, then onError($replay, $exception) will be thrown.
     *
     * @param ReplayInterface $replay
     */
    public function onSuccess(ReplayInterface $replay);
} 