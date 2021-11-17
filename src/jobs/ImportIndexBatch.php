<?php

namespace rias\scout\jobs;

use craft\queue\BaseJob;
use rias\scout\engines\Engine;
use rias\scout\Scout;

class ImportIndexBatch extends BaseJob
{
    /** @var string */
    public $indexName;

    /** @var int[] */
    public $elementIds;

    protected $batchSize = 10;

    public function execute($queue)
    {
        /** @var Engine $engine */
        $engine = Scout::$plugin->getSettings()->getEngines()->first(function (Engine $engine) {
            return $engine->scoutIndex->indexName === $this->indexName;
        });

        if (!$engine) {
            return;
        }

        $elementsQuery = $engine->scoutIndex->criteria->id($this->elementIds);

        $elementsCount = $elementsQuery->count();
        $batchCount = ceil($elementsCount / $this->batchSize);

        foreach ($elementsQuery->batch($this->batchSize) as $key => $elements) {
            $firstElementIndex = $key * $this->batchSize;
            $this->setProgress($queue, $key / $batchCount, "{$firstElementIndex} of {$elementsCount}");
            $engine->update($elements);
        }
    }

    protected function defaultDescription()
    {
        return sprintf(
            'Indexing element(s) in “%s”',
            $this->indexName
        );
    }
}
