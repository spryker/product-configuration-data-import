<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Zed\ProductConfigurationDataImport\Business\Model;

use Orm\Zed\ProductConfiguration\Persistence\SpyProductConfigurationQuery;
use Spryker\Zed\DataImport\Business\Exception\DataKeyNotFoundInDataSetException;
use Spryker\Zed\DataImport\Business\Model\DataImportStep\DataImportStepInterface;
use Spryker\Zed\DataImport\Business\Model\DataImportStep\PublishAwareStep;
use Spryker\Zed\DataImport\Business\Model\DataSet\DataSetInterface;
use Spryker\Zed\ProductConfigurationDataImport\Business\Model\DataSet\ProductConfigurationDataSet;

class ProductConfigurationWriterStep extends PublishAwareStep implements DataImportStepInterface
{
    /**
     * @uses \Spryker\Shared\ProductConfigurationStorage\ProductConfigurationStorageConfig::PRODUCT_CONFIGURATION_PUBLISH
     *
     * @var string
     */
    protected const EVENT_PRODUCT_CONFIGURATION_PUBLISH = 'Entity.spy_product_configuration.publish';

    /**
     * @param \Spryker\Zed\DataImport\Business\Model\DataSet\DataSetInterface $dataSet
     *
     * @throws \Spryker\Zed\DataImport\Business\Exception\DataKeyNotFoundInDataSetException
     *
     * @return void
     */
    public function execute(DataSetInterface $dataSet): void
    {
        $productConfigurationEntity = SpyProductConfigurationQuery::create()
            ->filterByFkProduct($dataSet[ProductConfigurationDataSet::ID_PRODUCT_CONCRETE])
            ->findOneOrCreate();

        if (empty($dataSet[ProductConfigurationDataSet::KEY_CONFIGURATION_KEY])) {
            throw new DataKeyNotFoundInDataSetException(
                sprintf(
                    '"%s" key must be in the data set. Given: "%s"',
                    ProductConfigurationDataSet::KEY_CONFIGURATION_KEY,
                    implode(', ', array_keys($dataSet->getArrayCopy())),
                ),
            );
        }

        $productConfigurationEntity->setDefaultConfiguration($dataSet[ProductConfigurationDataSet::KEY_DEFAULT_CONFIGURATION])
            ->setDefaultDisplayData($dataSet[ProductConfigurationDataSet::KEY_DEFAULT_DISPLAY_DATA])
            ->setConfiguratorKey($dataSet[ProductConfigurationDataSet::KEY_CONFIGURATION_KEY])
            ->setIsComplete($dataSet[ProductConfigurationDataSet::KEY_IS_COMPLETE]);

        $productConfigurationEntity->save();

        $this->addPublishEvents(
            static::EVENT_PRODUCT_CONFIGURATION_PUBLISH,
            $productConfigurationEntity->getIdProductConfiguration(),
        );
    }
}
