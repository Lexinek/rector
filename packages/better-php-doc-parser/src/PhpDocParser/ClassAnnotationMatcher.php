<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocParser;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * Matches "@ORM\Entity" to FQN names based on use imports in the file
 */
final class ClassAnnotationMatcher
{
    public function isTagMatchToNodeAndClass(string $tag, Node $node, string $matchingClass): bool
    {
        $tag = ltrim($tag, '@');

        $useNodes = $node->getAttribute(AttributeKey::USE_NODES);
        if ($useNodes === null) {
            return $matchingClass === $tag;
        }

        $fullyQualifiedClassNode = $this->matchFullAnnotationClassWithUses($tag, $useNodes);
        if ($fullyQualifiedClassNode === null) {
            return false;
        }

        return Strings::lower($fullyQualifiedClassNode) === Strings::lower($matchingClass);
    }

    /**
     * @param Use_[] $uses
     */
    private function matchFullAnnotationClassWithUses(string $tag, array $uses): ?string
    {
        foreach ($uses as $use) {
            foreach ($use->uses as $useUse) {
                if (! $this->isUseMatchingName($tag, $useUse)) {
                    continue;
                }

                return $this->resolveName($tag, $useUse);
            }
        }

        return null;
    }

    private function isUseMatchingName(string $tag, UseUse $useUse): bool
    {
        $shortName = $useUse->alias !== null ? $useUse->alias->name : $useUse->name->getLast();
        $shortNamePattern = preg_quote($shortName, '#');

        return (bool) Strings::match($tag, '#' . $shortNamePattern . '(\\\\[\w]+)?#i');
    }

    private function resolveName(string $tag, UseUse $useUse): string
    {
        if ($useUse->alias === null) {
            return $useUse->name->toString();
        }

        $unaliasedShortClass = Strings::substring($tag, Strings::length($useUse->alias->toString()));
        if (Strings::startsWith($unaliasedShortClass, '\\')) {
            return $useUse->name . $unaliasedShortClass;
        }

        return $useUse->name . '\\' . $unaliasedShortClass;
    }
}
