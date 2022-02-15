<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\AntragoGradeOverview\Model;

use ILIAS\Plugin\AntragoGradeOverview\Repository\GradeDataRepository;
use ILIAS\Plugin\AntragoGradeOverview\Exception\ValueConvertException;

/**
 * Class Datasets
 * @package ILIAS\Plugin\AntragoGradeOverview\Model
 * @author  Marvin Beym <mbeym@databay.de>
 */
class Datasets
{
    /**
     * @var GradeData[]
     */
    private $new;
    /**
     * @var GradeData[]
     */
    private $changed;
    /**
     * @var GradeData[]
     */
    private $unchanged;

    /**
     * @param GradeData[] $gradesData
     * @throws ValueConvertException
     */
    public function __construct(array $gradesData)
    {
        $existingGradesData = GradeDataRepository::getInstance()->readAll();
        $this->map($gradesData, $existingGradesData);
    }

    /**
     * @param GradeData[] $newDatasets
     * @param GradeData[] $existingDatasets
     */
    private function map(array $newDatasets, array $existingDatasets) : void
    {
        $new = [];
        $changed = [];
        $unchanged = [];

        foreach ($existingDatasets as $existingDataset) {
            $existingComparisonString = $this->createDatasetComparisonString($existingDataset);
            foreach ($newDatasets as $newDataset) {
                $newComparisonString = $this->createDatasetComparisonString($newDataset);

                if ($existingComparisonString === $newComparisonString) {
                    $newDataset->setId($existingDataset->getId());
                    $changed[$newComparisonString] = $newDataset;
                }
            }
        }

        foreach ($newDatasets as $newDataset) {
            if ($newDataset->getId() === null) {
                $new[$this->createDatasetComparisonString($newDataset)] = $newDataset;
            }
        }

        foreach ($existingDatasets as $existingDataset) {
            $existingComparisonString = $this->createDatasetComparisonString($existingDataset);
            if (
                !array_key_exists($existingComparisonString, $new)
                && !array_key_exists($existingComparisonString, $changed)
            ) {
                $unchanged[$existingComparisonString] = $existingDataset;
            }
        }

        $this->setNew($new);
        $this->setChanged($changed);
        $this->setUnchanged($unchanged);
    }

    private function createDatasetComparisonString(GradeData $dataset) : string
    {
        $dateString = $dataset->getDate() ? $dataset->getDate()->format("d.m.Y H:i:s") : "INVALID_DATE";
        return "{$dataset->getFpIdNr()}_{$dataset->getTlnNameLong()}_$dateString";
    }

    /**
     * @return GradeData[]
     */
    public function getNew() : array
    {
        return $this->new;
    }

    /**
     * @param GradeData[] $new
     * @return Datasets
     */
    private function setNew(array $new) : Datasets
    {
        $this->new = $new;
        return $this;
    }

    /**
     * @return GradeData[]
     */
    public function getChanged() : array
    {
        return $this->changed;
    }

    /**
     * @param GradeData[] $changed
     * @return Datasets
     */
    private function setChanged(array $changed) : Datasets
    {
        $this->changed = $changed;
        return $this;
    }

    /**
     * @return GradeData[]
     */
    public function getUnchanged() : array
    {
        return $this->unchanged;
    }

    /**
     * @param GradeData[] $unchanged
     * @return Datasets
     */
    private function setUnchanged(array $unchanged) : Datasets
    {
        $this->unchanged = $unchanged;
        return $this;
    }

}
