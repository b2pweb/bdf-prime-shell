<?php

namespace Bdf\Prime\Shell\Util;

/**
 * Simple buffer for parse PHP tokens
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
     * @var int
     */
    private $direction = 1;

    /**
     * TokensBuffer constructor.
     *
     * @param array<int, string|array> $tokens
     */
    public function __construct(array $tokens)
    {
        $this->tokens = array_values($tokens);
    }

    /**
     * Reverse the buffer order :
     * - The buffer cursor will be set to the end
     * - next() calls will move to previous token
     *
     * @return $this
     */
    public function reverse(): TokensBuffer
    {
        $this->cursor = count($this->tokens) - 1;
        $this->direction = -1;

        return $this;
    }

    /**
     * Reset the cursor and move forward
     * - The buffer cursor will be set to the start
     * - next() calls will move to next token
     *
     * @return $this
     */
    public function forward(): TokensBuffer
    {
        $this->cursor = 0;
        $this->direction = 1;

        return $this;
    }

    /**
     * Get the current token
     * Return null if there is no token at the current position
     *
     * @param int $offset The token offset. Positive integer for next token, or negative for previous
     *
     * @return string|array|null
     */
    public function get(int $offset = 0)
    {
        return $this->tokens[$this->cursor + $offset * $this->direction] ?? null;
    }

    /**
     * Check if the current cursor position is valid
     *
     * @return bool
     */
    public function valid(): bool
    {
        return isset($this->tokens[$this->cursor]);
    }

    /**
     * Get the string (i.e. code) value of a token
     *
     * @param int $offset The token offset. Positive integer for next token, or negative for previous
     *
     * @return string|null The token string, or null if not found
     * @psalm-ignore-nullable-return
     */
    public function asString(int $offset = 0): ?string
    {
        $token = $this->get($offset);

        return is_array($token) ? $token[1] : $token;
    }

    /**
     * Move cursor to the next token
     *
     * @param int $count number of tokens to skip
     * @return $this
     */
    public function next(int $count = 1): TokensBuffer
    {
        $this->cursor += $count * $this->direction;

        return $this;
    }

    /**
     * Go to the given position (absolute)
     * The position starts at 0
     *
     * @param int $position The token position (absolute)
     *
     * @return $this
     */
    public function goTo(int $position): TokensBuffer
    {
        $this->cursor = $position;

        return $this;
    }

    /**
     * Check the token type
     *
     * @param int $type On of the T_* constant
     * @param int $offset The token offset. Positive integer for next token, or negative for previous
     *
     * @return bool true if the token exists, and match with the given type
     */
    public function is(int $type, int $offset = 0): bool
    {
        $token = $this->get($offset);

        return is_array($token) && $token[0] === $type;
    }

    /**
     * Check the current token string value
     *
     * @param string $value The expected token value
     * @param int $offset The token offset. Positive integer for next token, or negative for previous
     *
     * @return bool true if the token exists and match with the given value
     */
    public function equals(string $value, int $offset = 0): bool
    {
        $token = $this->get($offset);

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
     * @return string
     */
    public function fullyQualifiedClassName(): string
    {
        $className = '';

        while ($this->is(T_STRING) || $this->is(T_NS_SEPARATOR) || (PHP_MAJOR_VERSION >= 8 && defined('T_NAME_QUALIFIED') && $this->is(T_NAME_QUALIFIED))) {
            if ($this->direction === 1) {
                $className .= $this->asString();
            } else {
                $className = $this->asString().$className;
            }

            $this->next();
        }

        return $className;
    }

    /**
     * Check the next tokens on the buffer
     * The first parameter is the current token, the second is the next, etc...
     *
     * @param string|int ...$tokens The tokens string value or type
     *
     * @return bool true if all match
     */
    public function match(...$tokens): bool
    {
        foreach ($tokens as $token) {
            $match = is_int($token) ? $this->is($token) : $this->equals($token);

            if (!$match) {
                return false;
            }

            $this->cursor += $this->direction;
        }

        return true;
    }

    /**
     * Get a new TokensBuffer containing all tokens before the cursor position
     *
     * @return TokensBuffer The new buffer instance
     */
    public function before(): TokensBuffer
    {
        return new TokensBuffer(array_slice($this->tokens, 0, $this->cursor));
    }

    /**
     * Get all tokens
     *
     * @return array<int, string|array>
     */
    public function all(): array
    {
        return $this->tokens;
    }
}
