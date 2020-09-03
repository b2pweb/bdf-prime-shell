<?php

namespace Bdf\Prime\Shell\Matcher;

use Bdf\Prime\Entity\Model;
use Bdf\Prime\Repository\RepositoryInterface;
use Bdf\Prime\Shell\Util\StreamTrait;
use Bdf\Prime\Shell\Util\TokensBuffer;
use Psy\TabCompletion\Matcher\AbstractMatcher;
use ReflectionMethod;

/**
 * Add active record model methods auto complete
 */
final class ModelMatcher extends AbstractMatcher
{
    use StreamTrait;

    /**
     * {@inheritdoc}
     */
    public function getMatches(array $tokens, array $info = [])
    {
        $buffer = new TokensBuffer($tokens);
        $buffer->reverse();

        $input = '';

        if ($buffer->is(T_STRING)) {
            $input = $buffer->asString();
            $buffer->next();
        }

        $buffer->next();

        /** @var class-string<Model> $class */
        $class = $buffer->fullyQualifiedClassName();

        $baseName = ltrim(strrchr($class, '\\') ?: $class, '\\');

        /** @var RepositoryInterface $repository */
        $repository = $class::repository();

        return $this->methodsStream($repository, $repository->queries(), $repository->queries()->builder())
            ->filter(function (ReflectionMethod $method) use($input) {
                return self::startsWith($input, $method->getName());
            })
            ->map(function (ReflectionMethod $method) use($baseName) {
                return $baseName.'::'.$method->getName().'('.($method->getNumberOfRequiredParameters() > 0 ? '' : ')');
            })
            ->distinct()
            ->toArray(false)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function hasMatched(array $tokens)
    {
        if (!Model::isActiveRecordEnabled()) {
            return false;
        }

        $buffer = new TokensBuffer($tokens);
        $buffer->reverse();

        // @todo nextIf
        if ($buffer->is(T_STRING)) {
            $buffer->next();
        }

        if (!$buffer->is(T_DOUBLE_COLON)) {
            return false;
        }

        return is_subclass_of($buffer->next()->fullyQualifiedClassName(), Model::class);
    }
}
