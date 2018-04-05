<?php

declare(strict_types=1);

namespace Soyuka\RedisMessengerAdapter\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ListMessengerReceivers extends Command
{
    private $receiversIds;

    public function __construct($receiversIds = array())
    {
        parent::__construct();
        $this->receiversIds = $receiversIds;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('redis_messenger:list_receivers')
            ->setDescription('List available receivers.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->receiversIds as $id) {
            $output->writeLn("$id");
        }
    }
}
