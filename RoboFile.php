<?php
require_once 'vendor/autoload.php';

class Robofile extends \Robo\Tasks
{
    use \Codeception\Task\MergeReports;
    use \Codeception\Task\SplitTestsByGroups;

    public function parallelSplitTests()
    {
// Split your tests by files
        $this->taskSplitTestFilesByGroups(5)
            ->projectRoot('.')
            ->testsFrom('tests/acceptance')
            ->groupsTo('tests/_data/paracept_')
            ->run();

        /*
        // Split your tests by single tests (alternatively)
        $this->taskSplitTestsByGroups(5)
            ->projectRoot('.')
            ->testsFrom('tests/acceptance')
            ->groupsTo('tests/_data/paracept_')
            ->run();
        */
    }

    public function parallelRun()
    {
        $parallel = $this->taskParallelExec();
        for ($i = 1; $i <= 5; $i++) {
            $parallel->process(
                $this->taskCodecept() // use built-in Codecept task
                ->suite('acceptance') // run acceptance tests
                ->group("paracept_$i") // for all paracept_* groups
                ->xml("tests/_log/result_$i.xml") // save XML results
                ->html("tests/_log/result_$i.html")
            );
        }
        return $parallel->run();
    }

    public function parallelMergeResults()
    {
        $merge = $this->taskMergeXmlReports();
        for ($i=1; $i<=5; $i++) {
            $merge->from("tests/_output/tests/_log/result_$i.html");

//            $merge->from("tests/_output/tests/_log/result_$i.xml");
        }
//        $merge->into("tests/_output/result_paracept.xml")->run();
        $merge->into("tests/_output/result_paracept.html")->run();

    }
}