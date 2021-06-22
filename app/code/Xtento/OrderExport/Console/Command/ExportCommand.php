<?php

/**
 * Product:       Xtento_OrderExport (2.5.2)
 * ID:            ALOS9nyJR4GmLp9b0POAXWBdZQz7n1C/haY72X8BIV4=
 * Packaged:      2018-04-13T12:30:09+00:00
 * Last Modified: 2017-07-11T13:03:10+00:00
 * File:          app/code/Xtento/OrderExport/Console/Command/ExportCommand.php
 * Copyright:     Copyright (c) 2018 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Console\Command;

use Magento\Framework\App\State as AppState;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCommand extends Command
{
    /**
     * @var AppState
     */
    protected $appState;

    /**
     * @var \Xtento\OrderExport\Model\ProfileFactory
     */
    protected $profileFactory;

    /**
     * @var \Xtento\OrderExport\Model\ExportFactory
     */
    protected $exportFactory;

    /**
     * @var \Xtento\OrderExport\Cron\Export
     */
    protected $cronExport;

    /**
     * ExportCommand constructor.
     *
     * @param \Xtento\OrderExport\Model\ProfileFactory $profileFactory
     * @param \Xtento\OrderExport\Model\ExportFactory $exportFactory
     * @param \Xtento\OrderExport\Cron\Export $cronExport
     */
    public function __construct(
        AppState $appState,
        \Xtento\OrderExport\Model\ProfileFactory $profileFactory,
        \Xtento\OrderExport\Model\ExportFactory $exportFactory,
        \Xtento\OrderExport\Cron\Export $cronExport
    ) {
        $this->appState = $appState;
        $this->profileFactory = $profileFactory;
        $this->exportFactory = $exportFactory;
        $this->cronExport = $cronExport;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('xtento:orderexport:export')
            ->setDescription('Export XTENTO order export profile. "Cronjob Export" filters will be applied.')
            ->setDefinition(
                [
                    new InputArgument(
                        'profile',
                        InputArgument::REQUIRED,
                        'Profile IDs to export (multiple IDs: comma-separated, no spaces)'
                    )
                ]
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->appState->setAreaCode('adminhtml');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            // intentionally left empty
        }
        echo(sprintf("[Debug] App Area: %s\n", $this->appState->getAreaCode())); // Required to avoid "area code not set" error

        $profileIds = explode(",", $input->getArgument('profile'));
        if (empty($profileIds)) {
            $output->writeln("<error>Profile IDs to export missing.</error>");
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        foreach ($profileIds as $profileId) {
            $profileId = intval($profileId);
            if ($profileId < 1) {
                $output->writeln("<error>Invalid profile ID: %s</error>", $profileId);
                continue;
            }

            try {
                $profile = $this->profileFactory->create()->load($profileId);
                if (!$profile->getId()) {
                    $output->writeln(sprintf("<error>Profile ID %d does not exist.</error>", $profileId));
                    continue;
                }
                if (!$profile->getEnabled()) {
                    $output->writeln(sprintf("<error>Profile ID %d is disabled.</error>", $profileId));
                    continue;
                }

                $output->writeln(sprintf("<info>Exporting profile ID %d.</info>", $profileId));
                $exportModel = $this->exportFactory->create()->setProfile($profile);
                $filters = $this->cronExport->addProfileFilters($profile);
                $exportModel->cronExport($filters);
                $output->writeln(sprintf('<info>Export for profile ID %d completed. Check "Execution Log" for detailed results.</info>', $profileId));
            } catch (\Exception $exception) {
                $output->writeln(sprintf("<error>Exception for profile ID %d: %s</error>", $profileId, $exception->getMessage()));
                continue;
            }
        }
        $output->writeln("<info>Finished command.</info>");
        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }
}
