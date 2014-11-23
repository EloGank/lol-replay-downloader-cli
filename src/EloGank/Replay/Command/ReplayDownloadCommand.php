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
use EloGank\Replay\Client\ReplayClient;
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

        // The command will start a new process to download the replay, it's a non-blocking command
        if ($input->getOption('async')) {
            $this->info($output, 'Downloading replay #' . $input->getArgument('game_id') . ' (' . $input->getArgument('region') . ') - Asynchronous');

            $consolePath = __DIR__ . '/../../../..';
            $replayDownloader->download(
                $input->getArgument('region'),
                $input->getArgument('game_id'),
                $input->getArgument('encryption_key'),
                $consolePath,
                true,
                $input->getOption('override')
            );

            return 0;
        }

        $this->info($output, 'Downloading replay #' . $input->getArgument('game_id') . ' (' . $input->getArgument('region') . ')');

        if (!preg_match('/[A-Z]+/', $input->getArgument('region'), $matches)) {
            throw new \RuntimeException('Cannot determine game region : ' . $input->getArgument('region'));
        }

        $replay = $replayDownloader->createReplay(
            $input->getArgument('region'),
            $input->getArgument('game_id'),
            $input->getArgument('encryption_key')
        );

        // Metas
        try {
            // Download metas
            $output->write("Retrieve metas...\t\t\t");
            $replayDownloader->downloadMetas($replay);
            $output->writeln('<info>OK</info>');

            // Validate game criterias based on metas
            $output->write("Validate game criterias...\t\t");
            if ($replayDownloader->isValid($replay, $output)) {
                $output->writeln('<info>OK</info>');
            }

            // Only on sync call, because async do the same thing before (ReplayDownloader::download()::createDirs())
            if ($input->getOption('override')) {
                if (!is_dir($replayDownloader->getReplayDirPath($replay->getRegion(), $replay->getGameId()))) {
                    // Create directories
                    $replayDownloader->createDirs($replay->getRegion(), $replay->getGameId());
                }
            }
            else {
                // Create directories
                $replayDownloader->createDirs($replay->getRegion(), $replay->getGameId());
            }

            // Retrieve last infos to download previous files
            $output->write("Retrieve last infos...\t\t\t");
            $lastChunkInfo = $replayDownloader->getLastChunkInfos($replay);
            $replay->setLastChunkId($lastChunkInfo['chunkId']);
            $replay->setLastKeyframeId($lastChunkInfo['keyFrameId']);
            $output->writeln('<info>OK</info>');

            // Download previous chunks
            $output->write("Download all previous chunks (" . $replay->getLastChunkId() . ")...\t");
            $replayDownloader->downloadChunks($replay);
            $output->writeln('<info>OK</info>');

            // Download previous keyframes
            $output->write("Download all previous keyframes (" . $replay->getLastKeyframeId() . ")...\t");
            $replayDownloader->downloadKeyframes($replay, $output);
            $output->writeln(array('<info>OK</info>', ''));

            // Download current chunks & keyframes
            $output->writeln("Download current game data :");
            $replayDownloader->downloadCurrentData($replay, $output);
            $output->writeln('');

            // Update metas
            $output->write("Update metas...\t\t\t\t");
            $replayDownloader->updateMetas($replay);
            $output->writeln('<info>OK</info>');

            // Execute handler
            $this->onSuccess($replay);
        }
        catch (\Exception $e) {
            // Execute handler
            $this->onFailure($replay, $e);

            throw $e;
        }

        $this->success($output, 'Finished without error');
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
     * @param ReplayInterface $replay
     */
    public function onSuccess(ReplayInterface $replay)
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
                throw new \InvalidArgumentException('The success handler class ' . $successHandler . ' should implement \EloGank\Component\Handler\SuccessHandlerInterface');
            }

            $successHandlerClass->onSuccess($replay);
        }
    }

    /**
     * Executed on failure
     *
     * @param ReplayInterface $replay
     * @param \Exception      $exception
     */
    public function onFailure(ReplayInterface $replay, \Exception $exception)
    {
        $failureHandler = null;
        try {
            $failureHandler = Config::get('replay.command.handler.failure');
        }
        catch (ConfigurationKeyNotFoundException $e) {
            // Failure handler is not set
        }

        if (null != $failureHandler) {
            $failureHandlerClass = new $failureHandler();
            if (!$failureHandlerClass instanceof FailureHandlerInterface) {
                throw new \InvalidArgumentException('The failure handler class ' . $failureHandler . ' should implement \EloGank\Component\Handler\FailureHandlerInterface');
            }

            $failureHandlerClass->onFailure($replay, $exception);
        }
    }
}
