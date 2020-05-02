<?php
namespace Netdotter\Rank;

class Rank
{
    /**
     * @var \SplDoublyLinkedList
     *
     * Used to store the working data as a double linked list
     */
    private $list;

    private $input;

    private $output;

    private $rankKey = "rank";

    private $uniqueIdentifier = false;

    private $uniqueIdentifierKey = false;

    private $startRank = 1;

    public function __construct(array $input)
    {
        $this->init();
    }

    private function init() : void
    {
        $this->list = new \SplDoublyLinkedList();
        $this->isListReady();
        $this->acceptInput();
    }

    private function isListReady()
    {
        if(!($this->list instanceof \SplDoublyLinkedList))
            throw new \Exception();
    }

    private function isInputEmpty()
    {
        if(count($this->input) === 0)
            return new \Exception();
    }

    private function push($item)
    {
        try {
            $this->list->push($item);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function acceptInput()
    {
        $this->isInputEmpty();
        $this->isListReady();

        foreach($this->input as $item) {
            $this->push($item);
        }
    }

    private function shiftDown(int $from, int $to)
    {
        $value = $this->list->offsetGet($from);
        $this->list->add($to, $value);
        $this->list->offsetUnset($from);
    }

    private function shiftUp(int $from, int $to)
    {
        $value = $this->list->offsetGet($from);
        $this->list->offsetUnset($from);
        $this->list->add($to, $value);
    }

    public function setUniqueIdentifierKey(string $key) : Rank
    {
        $this->uniqueIdentifierKey = $key;
        return $this;
    }

    public function setUniqueIdentifier($id) : Rank
    {
        $this->uniqueIdentifier = $id;
        return $this;
    }

    public function setStartRank(int $start) : Rank
    {
        $this->startRank = $start;
        return $this;
    }

    private function rank()
    {
        $this->output = [];
        $rank = $this->startRank;
        for ($this->list->rewind(); $this->list->valid(); $this->list->next()) {
            $value = $this->list->offsetGet($this->list->key());
            $value[$this->rankKey] = $rank;
            $this->list->offsetSet($this->list->key(), $value);
            $this->output[] = $value;
            $rank++;
        }
    }

    public function findValueIndex($key, $value) : int
    {
        for ($this->list->rewind(); $this->list->valid(); $this->list->next()) {
            $item = $this->list->offsetGet($this->list->key());
            if(isset($item[$key]) && $item[$key] == $value) {
                return (int) $this->list->key();
            }
        }

        throw new \Exception();
    }

    public function process(int $from, int $to)
    {
        if($this->uniqueIdentifier) {
            $this->rank();
            $indexOfEntry = $this->findValueIndex($this->uniqueIdentifierKey, $this->uniqueIdentifier);
            $item = $this->list->offsetGet($indexOfEntry);
            $from = $item[$this->rankKey];
        }

        try {

            if ($from > $to) {
                $toOffset = $to - 1;
                if ($toOffset < 0) {
                    $toOffset = 0;
                }

                $from = $from - 1;
                if($from < 0) {
                    $from = 0;
                }

                $this->shiftUp($from, $toOffset);
            }

            if ($to > $from) {

                $fromOffset = $from - 1;
                if ($fromOffset < 0) {
                    $fromOffset = 0;
                }

                $this->shiftDown($fromOffset, $to);
            }

            $this->rank();
            return $this->output;

        } catch(\Exception $e) {
            return $this->input;
        }
    }
}