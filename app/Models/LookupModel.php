<?php

namespace App\Models;

/**
 * Port of CI3 models/Lookup_model.php: the small lookup tables, as dropdown options.
 */
class LookupModel extends ListModel
{
    protected $table = 'testType';

    /**
     * CI3 put "None" first, stored as 0.
     *
     * @return array<int|string, string>
     */
    public function sequencingCenters(): array
    {
        return $this->options('sequencingCenter', 'sequencingCenterID', 'sequencingCenterDesc', [0 => 'None']);
    }

    /** @return array<int|string, string> */
    public function testTypes(): array
    {
        return $this->options('testType', 'testTypeID', 'testType');
    }

    /** @return array<int|string, string> */
    public function libraryPreps(): array
    {
        return $this->options('libraryPrep', 'libraryPrepID', 'libraryPrep');
    }

    /** @return array<int|string, string> */
    public function platforms(): array
    {
        return $this->options('platform', 'platformID', 'platform');
    }
}
