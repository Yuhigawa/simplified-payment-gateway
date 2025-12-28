<?php

declare(strict_types=1);

namespace App\Module\Account\Domain\ValueObject;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;
use Hyperf\Snowflake\Configuration;
use Hyperf\Snowflake\IdGenerator\SnowflakeIdGenerator;
use Hyperf\Snowflake\MetaGeneratorInterface;
use Hyperf\Snowflake\MetaGenerator\RandomMilliSecondMetaGenerator;

#[Constants]
class Id extends AbstractConstants
{
    public static function generateSnowFlakeId(): int
    {
        $config = new Configuration();
        $generator = new SnowflakeIdGenerator(
            new RandomMilliSecondMetaGenerator($config, MetaGeneratorInterface::DEFAULT_BEGIN_SECOND)
        );

        return $generator->generate();
    }
}
