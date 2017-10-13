<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\FunctionalTestingFramework\Suite\Objects;

use Magento\FunctionalTestingFramework\Test\Objects\CestObject;

/**
 * Class SuiteObject
 */
class SuiteObject
{
    /**
     * Name of the Suite.
     *
     * @var string
     */
    private $name;

    /**
     * Array of Cests to include for the suite.
     *
     * @var array
     */
    private $includeCests = [];

    /**
     * Array of Cests to exclude for the suite.
     *
     * @var array
     */
    private $excludeCests = [];

    /**
     * SuiteObject constructor.
     * @param string $name
     * @param array $includeCests
     * @param array $excludeCests
     */
    public function __construct($name, $includeCests, $excludeCests)
    {
        $this->name = $name;
        $this->includeCests = $includeCests;
        $this->excludeCests = $excludeCests;
    }

    /**
     * Getter for suite name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns an array of Cest Objects based on specifications in exclude and include arrays.
     *
     * @return array
     */
    public function getCests()
    {
        return $this->resolveCests($this->includeCests, $this->excludeCests);
    }

    /**
     * Takes an array of Cest Objects to include and an array of Cest Objects to exlucde. Loops through each Cest
     * and determines any overlapping tests. Returns a resulting array of Cest Objects based on this logic. Exclusion is
     * preferred to exclusiong (i.e. a test is specified in both include and exclude, it will be excluded).
     *
     * @param array $includeCests
     * @param array $excludeCests
     * @return array
     */
    private function resolveCests($includeCests, $excludeCests)
    {
        $finalCestList = $includeCests;
        $matchingCests = array_intersect(array_keys($includeCests), array_keys($excludeCests));

        // filter out tests within cests here and any overlap
        foreach ($matchingCests as $cestName) {
            $testNamesForExclusion = array_keys($excludeCests[$cestName]->getTests());
            $relevantTestNames = array_diff(array_keys($includeCests[$cestName]->getTests()), $testNamesForExclusion);

            if (empty($relevantTestNames)) {
                unset($finalCestList[$cestName]);
                continue;
            }

            /** @var CestObject $tempCestObj */
            $tempCestObj = $includeCests[$cestName];
            $finalCestList[$tempCestObj->getName()] = new CestObject(
                $tempCestObj->getName(),
                $tempCestObj->getAnnotations(),
                $this->resolveTests($relevantTestNames, $includeCests[$cestName]->getTests()),
                $tempCestObj->getHooks()
            );
        }

        if (empty($finalCestList)) {
            trigger_error(
                "Current suite configuration for " .
                $this->name . " contains no cests.",
                E_USER_WARNING
            );
        }

        return $finalCestList;
    }

    /**
     * Takes an array of test names to be extracted from an array of test objects. Returns the resulting array of test
     * objects contained within the applicable test names.
     *
     * @param array $relevantTestNames
     * @param array $tests
     * @return array
     */
    private function resolveTests($relevantTestNames, $tests)
    {
        $relevantTests = [];
        foreach ($relevantTestNames as $testName) {
            $relevantTests[$testName] = $tests[$testName];
        }

        return $relevantTests;
    }

    /**
     * Getter for before hooks.
     *
     * @return void
     */
    public function getBeforeHook()
    {
        //TODO implement getter for before hooks
    }

    /**
     * Getter for after hooks.
     *
     * @return void
     */
    public function getAfterHook()
    {
        //TODO implement getter for after hooks
    }
}
