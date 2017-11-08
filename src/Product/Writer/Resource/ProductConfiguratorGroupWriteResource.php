<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Resource;

use Shopware\Api\Write\Field\IntField;
use Shopware\Api\Write\Field\LongTextField;
use Shopware\Api\Write\Field\StringField;
use Shopware\Api\Write\Field\SubresourceField;
use Shopware\Api\Write\Field\TranslatedField;
use Shopware\Api\Write\Field\UuidField;
use Shopware\Api\Write\Flag\Required;
use Shopware\Api\Write\WriteResource;
use Shopware\Context\Struct\TranslationContext;
use Shopware\Product\Event\ProductConfiguratorGroupWrittenEvent;
use Shopware\Shop\Writer\Resource\ShopWriteResource;

class ProductConfiguratorGroupWriteResource extends WriteResource
{
    protected const UUID_FIELD = 'uuid';
    protected const NAME_FIELD = 'name';
    protected const DESCRIPTION_FIELD = 'description';
    protected const POSITION_FIELD = 'position';

    public function __construct()
    {
        parent::__construct('product_configurator_group');

        $this->primaryKeyFields[self::UUID_FIELD] = (new UuidField('uuid'))->setFlags(new Required());
        $this->fields[self::NAME_FIELD] = (new StringField('name'))->setFlags(new Required());
        $this->fields[self::DESCRIPTION_FIELD] = new LongTextField('description');
        $this->fields[self::POSITION_FIELD] = (new IntField('position'))->setFlags(new Required());
        $this->fields[self::NAME_FIELD] = new TranslatedField('name', ShopWriteResource::class, 'uuid');
        $this->fields[self::DESCRIPTION_FIELD] = new TranslatedField('description', ShopWriteResource::class, 'uuid');
        $this->fields['translations'] = (new SubresourceField(ProductConfiguratorGroupTranslationWriteResource::class, 'languageUuid'))->setFlags(new Required());
    }

    public function getWriteOrder(): array
    {
        return [
            self::class,
            ProductConfiguratorGroupTranslationWriteResource::class,
        ];
    }

    public static function createWrittenEvent(array $updates, TranslationContext $context, array $rawData = [], array $errors = []): ProductConfiguratorGroupWrittenEvent
    {
        $event = new ProductConfiguratorGroupWrittenEvent($updates[self::class] ?? [], $context, $rawData, $errors);

        unset($updates[self::class]);

        /**
         * @var WriteResource
         * @var string[]      $identifiers
         */
        foreach ($updates as $class => $identifiers) {
            if (!array_key_exists($class, $updates) || count($updates[$class]) === 0) {
                continue;
            }

            $event->addEvent($class::createWrittenEvent($updates, $context));
        }

        return $event;
    }
}