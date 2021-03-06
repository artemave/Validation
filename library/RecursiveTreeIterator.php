<?php

/**
 * Required for hhvm compatibility, can be ignored for Zend PHP.
 * Code from PHP itself: https://raw.githubusercontent.com/php/php-src/master/ext/spl/internal/recursivetreeiterator.inc
 */

/**
 * @file recursivetreeiterator.inc
 * @ingroup SPL
 * @brief   class RecursiveTreeIterator
 * @author  Marcus Boerger, Johannes Schlueter
 * @date    2005 - 2009
 *
 * SPL - Standard PHP Library
 */

/**
 * @ingroup SPL
 * @brief   RecursiveIteratorIterator to generate ASCII graphic trees for the
 *          entries in a RecursiveIterator
 * @author  Marcus Boerger, Johannes Schlueter
 * @version 1.1
 * @since   PHP 5.3
 */
class RecursiveTreeIterator extends RecursiveIteratorIterator
{
    const BYPASS_CURRENT = 0x00000004;
    const BYPASS_KEY     = 0x00000008;

    private $ritFlags;

    /**
     * @param it         iterator to use as inner iterator
     * @param ritFlags   flags passed to RecursiveIteratoIterator (parent)
     * @param citFlags   flags passed to RecursiveCachingIterator (for hasNext)
     * @param mode       mode  passed to RecursiveIteratoIterator (parent)
     */
    public function __construct(
        RecursiveIterator $it,
        $ritFlags = self::BYPASS_KEY,
        $citFlags = CachingIterator::CATCH_GET_CHILD,
        $mode = self::SELF_FIRST
    ) {
        parent::__construct(new RecursiveCachingIterator($it, $citFlags), $mode, $ritFlags);
        $this->ritFlags = $ritFlags;
    }

    private $prefix = array(0 => '', 1 => '| ',2  => '  ', 3 => '|-', 4 => '\-', 5 => '');

    /** Prefix used to start elements. */
    const PREFIX_LEFT         = 0;

    /** Prefix used if $level < depth and hasNext($level) == true. */
    const PREFIX_MID_HAS_NEXT = 1;

    /** Prefix used if $level < depth and hasNext($level) == false. */
    const PREFIX_MID_LAST     = 2;

    /** Prefix used if $level == depth and hasNext($level) == true. */
    const PREFIX_END_HAS_NEXT = 3;

    /** Prefix used if $level == depth and hasNext($level) == false. */
    const PREFIX_END_LAST     = 4;

    /** Prefix used right in front of the current element. */
    const PREFIX_RIGHT        = 5;

    /**
     * Set prefix part as used in getPrefix() and stored in $prefix.
     * @param $part   any PREFIX_* const.
     * @param $value  new prefix string for specified part.
     * @throws OutOfRangeException if 0 > $part or $part > 5.
     */
    public function setPrefixPart($part, $value)
    {
        if (0 > $part || $part > 5) {
            throw new OutOfRangeException();
        }
        $this->prefix[$part] = (string) $value;
    }

    /** @return string to place in front of current element
     */
    public function getPrefix()
    {
        $tree = '';
        for ($level = 0; $level < $this->getDepth(); $level++) {
            $tree .= $this->getSubIterator($level)->hasNext() ? $this->prefix[1] : $this->prefix[2];
        }
        $tree .= $this->getSubIterator($level)->hasNext() ? $this->prefix[3] : $this->prefix[4];

        return $this->prefix[0] . $tree . $this->prefix[5];
    }

    /**
     * @return string presentation build for current element
     */
    public function getEntry()
    {
        return @(string) parent::current();
    }

    /**
     * @return string to place after the current element
     */
    public function getPostfix()
    {
        return '';
    }

    /**
     * @return the current element prefixed and postfixed
     */
    public function current()
    {
        if ($this->ritFlags & self::BYPASS_CURRENT) {
            return parent::current();
        } else {
            return $this->getPrefix() . $this->getEntry() .  $this->getPostfix();
        }
    }

    /**
     * @return the current key prefixed and postfixed
     */
    public function key()
    {
        if ($this->ritFlags & self::BYPASS_KEY) {
            return parent::key();
        } else {
            return $this->getPrefix() . parent::key() .  $this->getPostfix();
        }
    }

    /**
     * Aggregates the inner iterator
     */
    public function __call($func, $params)
    {
        return call_user_func_array(array($this->getSubIterator(), $func), $params);
    }
}
