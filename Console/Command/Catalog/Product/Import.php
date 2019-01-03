<?php
/**
 * Copyright (c) 2018 Jose Fernandez
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Jfern01\ImportCli\Console\Command\Catalog\Product;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Import extends Command
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\State $state
    ) {
        $this->objectManager = $objectManager;
        $this->state = $state;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $this->state->setAreaCode('adminhtml');
            
        try {
            $this->getImportModel()->setEntity('catalog_product');
            $this->getImportModel()->setImagesPath($input->getOption('images_path'));
            $this->getImportModel()->setBehavior($input->getOption('behavior'));
            $this->getImportModel()->setFieldSeparator($input->getOption('field_separator'));
            $this->getImportModel()->setMultiValueSeparator($input->getOption('multi_value_separator'));
            $this->getImportModel()->setFieldsEnclosure($input->getOption('fields_enclosure'));
            $this->getImportModel()->setFile(realpath($input->getArgument('filename')));

            if ($input->getOption('validate')) {
                $output->writeln($this->getImportModel()->getFormattedLogTrace());
            } else {                
                $result = $this->getImportModel()->execute();
                
                if ($result) {
                    $output->writeln('<info>The import was successful.</info>');
                    $output->writeln('Log trace:');
                    $output->writeln($this->getImportModel()->getFormattedLogTrace());
                } else {
                    $output->writeln('<error>Import failed.</error>');
                    
                    foreach ($this->getImportModel()->getErrors() as $error) {
                        $output->writeln('<error>' . $error->getErrorMessage() . ' - ' . $error->getErrorDescription() . '</error>');
                    }
                }
            }
        } catch (FileNotFoundException $e) {
            $output->writeln('<error>File not found.</error>');
        } catch (\InvalidArgumentException $e) {
            $output->writeln('<error>Invalid source.</error>');
            $output->writeln('Log trace:');
            $output->writeln($this->getImportModel()->getFormattedLogTrace());
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('catalog:product:import')
            ->setDescription('Import catalog')
            ->addArgument('filename', InputArgument::REQUIRED, 'CSV file path')
            ->addOption('behavior', 'b', InputOption::VALUE_OPTIONAL, 'Behavior', 'append')
            ->addOption('field_separator', null, InputOption::VALUE_OPTIONAL, 'Field separator (delimiter)', ',')
            ->addOption('multi_value_separator', null, InputOption::VALUE_OPTIONAL, 'Muliple field separator', ',')
            ->addOption('fields_enclosure', 'f', InputOption::VALUE_NONE, 'Fields Enclosure')
            ->addOption('validate', null, InputOption::VALUE_NONE, 'Validate data only (no import)')
            ->addOption('images_path', 'i', InputOption::VALUE_OPTIONAL, 'Images path', 'pub/media/catalog/product');

        parent::configure();
    }

    /**
     * @return \CedricBlondeau\CatalogImportCommand\Model\Import
     */
    protected function getImportModel()
    {
        return $this->objectManager->get('Jfern01\ImportCli\Model\Import');
    }
}
