<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\FloatType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;

final class FloatTypeMapper implements TypeMapperInterface
{
    /**
     * @var PhpVersionProvider
     */
    private $phpVersionProvider;

    public function __construct(PhpVersionProvider $phpVersionProvider)
    {
        $this->phpVersionProvider = $phpVersionProvider;
    }

    public function getNodeClass(): string
    {
        return FloatType::class;
    }

    /**
     * @param FloatType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type): TypeNode
    {
        return new IdentifierTypeNode('float');
    }

    /**
     * @param FloatType $type
     */
    public function mapToPhpParserNode(Type $type, ?string $kind = null): ?Node
    {
        if (! $this->phpVersionProvider->isAtLeast(PhpVersionFeature::SCALAR_TYPES)) {
            return null;
        }

        return new Identifier('float');
    }

    public function mapToDocString(Type $type, ?Type $parentType = null): string
    {
        return $type->describe(VerbosityLevel::typeOnly());
    }
}
