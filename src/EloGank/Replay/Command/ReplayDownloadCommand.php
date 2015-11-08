<?php

/*
 * This file is part of the "EloGank League of Legends Replay Downloader" package.
 *
 * https://github.com/EloGank/lol-replay-downloader-cli
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EloGank\Replay\Command;

use EloGank\Component\Command\Command;
use EloGank\Component\Command\Handler\FailureHandlerInterface;
use EloGank\Component\Command\Handler\SuccessHandlerInterface;
use EloGank\Component\Configuration\Config;
use EloGank\Component\Configuration\Exception\ConfigurationKeyNotFoundException;
use EloGank\Replay\Downloader\Client\ReplayClient;
use EloGank\Replay\Command\Output\ConsoleOutput;
use EloGank\Replay\Downloader\ReplayDownloader;
use EloGank\Replay\ReplayInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class ReplayDownloadCommand extends Command implements SuccessHandlerInterface, FailureHandlerInterface
{
    /**
     * Configure the command
     */
    protected function configure()
    {
        $this
            ->setName('elogank:replay:download')
            ->setDescription('Download a replay game')
            ->addArgument('region', InputArgument::REQUIRED, 'The game region')
            ->addArgument('game_id', InputArgument::REQUIRED, 'The game id')
            ->addArgument('encryption_key', InputArgument::REQUIRED, 'The game encryption key')
            ->addOption('async', null, InputOption::VALUE_NONE, 'The replay download will be asynchronous')
            ->addOption('override', null, InputOption::VALUE_NONE, 'If exists, the replay folder will be override')
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     *
     * @throws \EloGank\Replay\Downloader\Exception\GameNotFoundException
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output = new ConsoleOutput($output);
        $replayDownloader = $this->createReplayDownloader();

        $region = $input->getArgument('region');
        $gameId = $input->getArgument('game_id');
        $encryptionKey = $input->getArgument('encryption_key');

        // The command will start a new process to download the replay, it's a non-blocking command
        if ($input->getOption('async')) {
            $this->info($output, 'Downloading replay #' . $gameId . ' (' . $region . ') - Asynchronous');

            $consolePath = __DIR__ . '/../../../..';
            $replayDownloader->download(
                $region,
                $gameId,
                $encryptionKey,
                $output,
                $input->getOption('override'),
                true,
                $consolePath
            );

            return 0;
        }

        $this->info($output, 'Downloading replay #' . $gameId . ' (' . $region . ')');

        $regions = Config::get('replay.http_client.servers');
        if (!isset($regions[$region])) {
            throw new \RuntimeException('Cannot determine game region : ' . $region);
        }

        // Metas
        try {
            $replay = $replayDownloader->download(
                $region,
                $gameId,
                $encryptionKey,
                $output,
                $input->getOption('override')
            );

            // Execute handler
            $this->onSuccess($output, $replay, $replayDownloader->getReplayDirPath($region, $gameId));

            $this->success($output, 'Finished without error');
        }
        catch (\Exception $e) {
            // Execute handler
            $this->onFailure(
                $output,
                $region,
                $gameId,
                $encryptionKey,
                $replayDownloader->getReplayDirPath($region, $gameId),
                $e
            );
        }
    }

    /**
     * @return ReplayDownloader
     */
    protected function createReplayDownloader()
    {
        return new ReplayDownloader(new ReplayClient([
            'buzz.class'                 => Config::get('buzz.class'),
            'buzz.timeout'               => Config::get('buzz.timeout'),
            'replay.http_client.servers' => Config::get('replay.http_client.servers')
        ]), Config::get('replay.path'), [
            'php.executable_path'       => Config::get('php.executable_path'),
            'replay.class'              => Config::get('replay.class'),
            'replay.decoder.enable'     => Config::get('replay.decoder.enable'),
            'replay.decoder.save_files' => Config::get('replay.decoder.save_files'),
            'replay.download.retry'     => Config::get('replay.download.max_retry'),
        ]);
    }

    /**
     * Executed on success
     *
     * @param OutputInterface $output
     * @param ReplayInterface $replay
     * @param string          $replayFolderPath
     */
    public function onSuccess(OutputInterface $output, ReplayInterface $replay, $replayFolderPath)
    {
        $successHandler = null;
        try {
            $successHandler = Config::get('replay.command.handler.success');
        }
        catch (ConfigurationKeyNotFoundException $e) {
            // Success handler is not set
        }

        if (null != $successHandler) {
            $successHandlerClass = new $successHandler();
            if (!$successHandlerClass instanceof SuccessHandlerInterface) {
                throw new \InvalidArgumentException(
                    'The success handler class '
                    . $successHandler . ' should implement \EloGank\Component\Handler\SuccessHandlerInterface'
                );
            }

            $successHandlerClass->onSuccess($output, $replay, $replayFolderPath);
        }
    }

    /**
     * Executed on failure
     *
     * @param OutputInterface $output
     * @param string          $region
     * @param int             $gameId
     * @param string          $encryptionKey
     * @param string          $replayFolderPath
     * @param \Exception      $exception
     *
     * @throws \Exception
     *
     * @return null|bool
     */
    public function onFailure(
        OutputInterface $output,
        $region,
        $gameId,
        $encryptionKey,
        $replayFolderPath,
        \Exception $exception
    ) {
        $failureHandler = null;

        try {
            $failureHandler = Config::get('replay.command.handler.failure');
        }
        catch (ConfigurationKeyNotFoundException $e) {
            // Failure handler is not set
        }

        $throwException = true;

        if (null != $failureHandler) {
            $failureHandlerClass = new $failureHandler();

            if (!$failureHandlerClass instanceof FailureHandlerInterface) {
                throw new \InvalidArgumentException(
                    'The failure handler class '
                    . $failureHandler . ' should implement \EloGank\Component\Handler\FailureHandlerInterface'
                );
            }

            $throwException = $failureHandlerClass->onFailure(
                $output,
                $region,
                $gameId,
                $encryptionKey,
                $replayFolderPath,
                $exception
            );
        }

        if (Config::get('replay.command.exception.throw') && true === $throwException) {
            throw $exception;
        }

        $output->writeln('');

        $this->error($output, $exception->getMessage());
    }
}
