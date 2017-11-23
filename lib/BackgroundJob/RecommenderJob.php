<?php
/**
 * Created by PhpStorm.
 * User: dogano
 * Date: 02.11.17
 * Time: 15:51
 */

namespace OCA\DoganMachineLearning\BackgroundJob;


use OC\BackgroundJob\TimedJob;
use OCA\DoganMachineLearning\AppInfo\Application;

class RecommenderJob extends TimedJob {

	public function __construct() {
		$this->setInterval(1);
	}

	protected function run($argument) {
		$app = new Application();
		$container = $app->getContainer();
		$recommenderService = $container->query(Application::RECOMMENDER_JOB_NAME);
		$recommenderService->run();

	}
}




