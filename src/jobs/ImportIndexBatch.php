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

    public function execute($queue)
    {
        /** @var Engine $engine */
        $engine = Scout::$plugin->getSettings()->getEngines()->first(function (Engine $engine) {
            return $engine->scoutIndex->indexName === $this->indexName;
        });

        if (!$engine) {
            return;
        }

        $elements = $engine->scoutIndex->criteria->id($this->elementIds)->all();

        $engine->update($elements);
    }

    protected function defaultDescription()
    {
        return sprintf(
            'Indexing element(s) in “%s”',
            $this->indexName
        );
    }
}
