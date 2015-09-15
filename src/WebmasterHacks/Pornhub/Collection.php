<?php

namespace WebmasterHacks\Pornhub;

abstract class Collection
{
    protected $items = [];

    public function add($item)
    {
        array_push($this->items, $item);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->count() === 0;
    }

    /**
     * @return bool
     */
    public function isNotEmpty()
    {
        return !$this->isEmpty();
    }

    /**
     * @param \Closure $callback
     */
    public function each(\Closure $callback)
    {
        foreach ($this->items as $item) {
            $callback($item);
        }
    }

    /**
     * @return mixed
     */
    public function first()
    {
        if ($this->isEmpty()) {
            return null;
        }

        return $this->items[0];
    }

    /**
     * @return mixed
     */
    public function last()
    {
        if ($this->isEmpty())
        {
            return null;
        }

        return $this->items[$this->count() - 1];
    }
}
