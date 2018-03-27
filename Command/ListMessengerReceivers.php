<?php

declare(strict_types=1);

namespace App\Messenger\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ListMessengerReceivers extends Command
{
    private $receiversIds;

    public function __construct($receiversIds)
    {
        parent::__construct();
        $this->receiversIds = $receiversIds;
    }

    protected function configure()
    {
        $this->setName('app:messenger:list_receivers')
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
