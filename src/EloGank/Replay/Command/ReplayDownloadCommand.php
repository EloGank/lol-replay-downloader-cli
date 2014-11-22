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
use EloGank\Component\Configuration\Config;
use EloGank\Replay\Client\ReplayClient;
use EloGank\Replay\Downloader\ReplayDownloader;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class ReplayDownloadCommand extends Command
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
        $replayDownloader = new ReplayDownloader(new ReplayClient(), Config::get('replay.path'));

        if ($input->getOption('async')) {
            $this->writeSection($output, 'Downloading replay #' . $input->getArgument('game_id') . ' (' . $input->getArgument('region') . ') - Asynchronous');

            $replayDownloader->download($input->getArgument('region'), $input->getArgument('game_id'), $input->getArgument('encryption_key'));

            return;
        }

        $this->writeSection($output, 'Downloading replay #' . $input->getArgument('game_id') . ' (' . $input->getArgument('region') . ')');

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
            $output->write("Retrieve metas...\t\t\t");
            $replayDownloader->downloadMetas($replay);
            $output->writeln('OK');

            // Validate game criterias
            $output->write("Validate game criterias...\t\t");
            if ($replayDownloader->isValid($replay)) {
                $output->writeln('OK');
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

            // LastChunkInfos
            $output->write("Retrieve last infos...\t\t\t");
            $lastChunkInfo = $replayDownloader->getLastChunkInfos($replay);
            $replay->setLastChunkId($lastChunkInfo['chunkId']);
            $replay->setLastKeyframeId($lastChunkInfo['keyFrameId']);
            $output->writeln('OK');

            // Previous chunks
            $output->write("Download all previous chunks (" . $replay->getLastChunkId() . ")...\t");
            $replayDownloader->downloadChunks($replay);
            $output->writeln('OK');

            // Previous keyframes
            $output->write("Download all previous keyframes (" . $replay->getLastKeyframeId() . ")...\t");
            $replayDownloader->downloadKeyframes($replay, $output);
            $output->writeln(array('OK', ''));

            // Free memory
            gc_collect_cycles();

            // Current chunks & keyframes
            $output->writeln("Download current game data :");
            $replayDownloader->downloadCurrentData($replay, $output);
            $output->writeln('');

            // Update metas
            $output->write("Update metas...\t\t\t\t");
            $replayDownloader->updateMetas($replay);
            $output->writeln('OK');

            // TODO config
            //$this->onSuccess($replay);
        }
        catch (\Exception $e) {
            // TODO config
            //$this->onError($replay, $e);

            throw $e;
        }

        $this->writeSection($output, 'Finished without error');
    }
}