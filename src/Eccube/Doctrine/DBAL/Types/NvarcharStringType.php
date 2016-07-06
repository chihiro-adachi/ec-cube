<?php

namespace Eccube\Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

class NvarcharStringType extends StringType
{
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        if (isset($fieldDeclaration['length']) && $fieldDeclaration['length'] > $platform->getVarcharMaxLength()) {
//            return $this->getClobTypeDeclarationSQL($field);
            return 'NVARCHAR(MAX)';
        }

        return parent::getSQLDeclaration($fieldDeclaration, $platform);
    }
}
