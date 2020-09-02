<?php

namespace Bdf\Prime\Shell\Util;

/**
 * Simple buffer for parse PHP tokens
 *
 * @todo reverse order
 */
final class TokensBuffer
{
    /**
     * @var array
     */
    private $tokens;

    /**
     * @var int
     */
    private $cursor = 0;

    /**
     * TokensBuffer constructor.
     *
     * @param array $tokens
     */
    public function __construct(array $tokens)
    {
        $this->tokens = $tokens;
    }

    /**
     * Get the current token
     * Return null if there is no token at the current position
     *
     * @return string|array|null
     */
    public function current()
    {
        return $this->tokens[$this->cursor] ?? null;
    }

    /**
     * Get the string (i.e. code) value of a token
     *
     * @return string|null The token string, or null if not found
     */
    public function asString(): ?string
    {
        if (!isset($this->tokens[$this->cursor])) {
            return null;
        }

        $token = $this->tokens[$this->cursor];

        return is_array($token) ? $token[1] : $token;
    }

    /**
     * Move cursor to buffer start
     *
     * @return $this
     */
    public function toStart(): TokensBuffer
    {
        $this->cursor = 0;

        return $this;
    }

    /**
     * Move cursor to buffer end
     *
     * @return $this
     */
    public function toEnd(): TokensBuffer
    {
        $this->cursor = count($this->tokens) - 1;

        return $this;
    }

    /**
     * Move cursor to the previous token
     *
     * @return $this
     */
    public function previous(): TokensBuffer
    {
        --$this->cursor;

        return $this;
    }

    /**
     * Move cursor to the next token
     *
     * @return $this
     */
    public function next(): TokensBuffer
    {
        ++$this->cursor;

        return $this;
    }

    /**
     * Check the token type
     *
     * @param int $type On of the T_* constant
     *
     * @return bool true if the token exists, and match with the given type
     */
    public function is(int $type): bool
    {
        $token = $this->current();

        return is_array($token) && $token[0] === $type;
    }

    /**
     * Check the current token string value
     *
     * @param string $value The expected token value
     *
     * @return bool true if the token exists and match with the given value
     */
    public function equals(string $value): bool
    {
        $token = $this->current();

        if (empty($token)) {
            return false;
        }

        if (is_array($token)) {
            $token = $token[1];
        }

        return $token == $value;
    }

    /**
     * Parse the tokens at the cursor position to extract a fully qualified class name (i.e. class name with namespace)
     *
     * @param bool $inOrder true to parse in order (cursor is on the first FQCN token, and should move forward), or false to parse in reverse order (cursor is on the class name, and should move backward)
     *
     * @return string
     */
    public function fullyQualifiedClassName(bool $inOrder): string
    {
        $className = '';

        while ($this->is(T_STRING) || $this->is(T_NS_SEPARATOR)) {
            if ($inOrder) {
                $className .= $this->asString();
                $this->next();
            } else {
                $className = $this->asString().$className;
                $this->previous();
            }
        }

        return $className;
    }

    /**
     * Check the next tokens on the buffer in the order
     * The first parameter is the current token, the second is the next, etc...
     *
     * @param string|int ...$tokens The tokens string value or type
     *
     * @return bool true if all match
     */
    public function matchInOrder(...$tokens): bool
    {
        return $this->match(true, ...$tokens);
    }

    /**
     * Check the previous tokens on the buffer in the reverse order
     * The first parameter is the current token, the second is the previous, etc...
     *
     * @param string|int ...$tokens The tokens string value or type
     *
     * @return bool true if all match
     */
    public function matchReverseOrder(...$tokens): bool
    {
        return $this->match(false, ...$tokens);
    }

    /**
     * Get all tokens before the cursor position
     *
     * @return array
     */
    public function before(): array
    {
        return array_slice($this->tokens, 0, $this->cursor);
    }

    private function match(bool $inOrder, ...$tokens): bool
    {
        $inc = $inOrder ? 1 : -1;

        foreach ($tokens as $token) {
            $match = is_int($token) ? $this->is($token) : $this->equals($token);

            if (!$match) {
                return false;
            }

            $this->cursor += $inc;
        }

        return true;
    }
}
