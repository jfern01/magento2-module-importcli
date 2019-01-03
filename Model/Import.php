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

namespace Jfern01\ImportCli\Model;

use Magento\ImportExport\Model\Import as MagentoImport;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class Import
{
    /**
     * @var \Magento\ImportExport\Model\Import
     */
    protected $importModel;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    protected $readFactory;

    /**
     * @var \Magento\ImportExport\Model\Import\Source\CsvFactory
     */
    protected $csvSourceFactory;

    /**
     * @var \Magento\Indexer\Model\Indexer\CollectionFactory
     */
    protected $indexerCollectionFactory;

    /**
     * @param \Magento\ImportExport\Model\Import $importModel
     * @param \Magento\ImportExport\Model\Import\Source\CsvFactory $csvSourceFactory
     * @param \Magento\Indexer\Model\Indexer\CollectionFactory $indexerCollectionFactory
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory
     */
    public function __construct(
        \Magento\ImportExport\Model\Import $importModel,
        \Magento\ImportExport\Model\Import\Source\CsvFactory $csvSourceFactory,
        \Magento\Indexer\Model\Indexer\CollectionFactory $indexerCollectionFactory,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory
    ) {
        $this->importModel = $importModel;
        $this->csvSourceFactory = $csvSourceFactory;
        $this->indexerCollectionFactory = $indexerCollectionFactory;
        $this->readFactory = $readFactory;

        $this->importModel->setData(
            [
                MagentoImport::FIELD_NAME_IMG_FILE_DIR => 'pub/media/catalog/product',
                MagentoImport::FIELD_NAME_VALIDATION_STRATEGY => ProcessingErrorAggregatorInterface::VALIDATION_STRATEGY_SKIP_ERRORS
            ]
        );
    }

    /**
     * @param string $entity
     * @return void
     */
    public function setEntity($entity)
    {
        $this->importModel->setEntity($entity);
    }

    /**
     * @param string $filePath Absolute file path to CSV file
     * @return void
     */
    public function setFile($filePath)
    {
        if (!file_exists($filePath)) {
            throw new FileNotFoundException();
        }

        // Hacky but quick fix for https://github.com/cedricblondeau/magento2-module-catalog-import-command/issues/1
        $pathInfo = pathinfo($filePath);

        $validate = $this->importModel->validateSource(
            $this->csvSourceFactory->create(
                [
                    'file' => $pathInfo['basename'],
                    'directory' => $this->readFactory->create($pathInfo['dirname'])
                ]
            )
        );

        if (!$validate) {
            throw new \InvalidArgumentException();
        }
    }

    /**
     * @param $imagesPath
     */
    public function setImagesPath($imagesPath)
    {
        $this->importModel->setData(MagentoImport::FIELD_NAME_IMG_FILE_DIR, $imagesPath);
    }

    /**
     * @param string $behavior
     * @return void
     */
    public function setBehavior($behavior)
    {
        if (in_array($behavior, array(
            MagentoImport::BEHAVIOR_APPEND,
            MagentoImport::BEHAVIOR_ADD_UPDATE,
            MagentoImport::BEHAVIOR_REPLACE,
            MagentoImport::BEHAVIOR_DELETE
        ))) {
            $this->importModel->setData('behavior', $behavior);
        }
    }

    /**
     * @param bool $fieldsEnclosure
     * @return void
     */
    public function setFieldsEnclosure($fieldsEnclosure)
    {
        $this->importModel->setData(MagentoImport::FIELDS_ENCLOSURE, $fieldsEnclosure ? 1 : 0);
    }

    /**
     * @param string $separator
     * @return void
     */
    public function setFieldSeparator($separator)
    {
        $this->importModel->setData(MagentoImport::FIELD_FIELD_SEPARATOR, $separator);
    }

    /**
     * @param string $separator
     * @return void
     */
    public function setMultiValueSeparator($separator)
    {
        $this->importModel->setData(MagentoImport::FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR, $separator);
    }

    /**
     * @return bool
     */
    public function execute()
    {
        $result = $this->importModel->importSource();

        if ($result) {
            $this->importModel->invalidateIndex();
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getFormattedLogTrace()
    {
        // Yep, there is a typo here, see https://github.com/magento/magento2/pull/2771
        return $this->importModel->getFormatedLogTrace();
    }

    /**
     * @return MagentoImport\ErrorProcessing\ProcessingError[]
     */
    public function getErrors()
    {
        return $this->importModel->getErrorAggregator()->getAllErrors();
    }
}