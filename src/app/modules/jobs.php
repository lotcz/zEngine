<?php

/**
* Module that handles running of cron jobs.
*/
class jobsModule extends zModule {

	//url base for running jobs
	public $base_url = 'jobs';

	public $security_token = 'jobs123';

	public function onEnabled() {
		$this->base_url = $this->getConfigValue('base_url', $this->base_url);
		$this->security_token = $this->getConfigValue('security_token', $this->security_token);
	}

	public function onBeforeInit() {
		if ($this->z->core->getPath(0) == $this->base_url) {
			$security_token = z::get('security_token', $this->z->core->getPath(-1));
			if ($security_token == $this->security_token) {
				$job_name = z::get('job', $this->z->core->getPath(-2));
				$job_path = $this->z->core->app_dir . "jobs/$job_name.j.php";

				if (!file_exists($job_path)) {
					$job_path = $this->z->core->default_app_dir . "jobs/$job_name.j.php";
				}

				include $job_path;
				exit;

			} else {
				http_response_code(403);
				die('Wrong security token.');
			}
		}
	}

	public function listJobs() {
		$core_job_files = z::listFiles(__DIR__ . '/../jobs');
		$app_dir = $this->z->app_dir . 'jobs';
		if (file_exists($app_dir)) {
			$app_job_files = z::listFiles($app_dir);
		} else {
			$app_job_files = [];
		}
		$all_job_files = z::mergeAssocArrays($core_job_files, $app_job_files);
		$jobs = [];
		foreach ($all_job_files as $job_file) {
			$jobs[] = substr($job_file, 0,strlen($job_file) - 6);
		}
		return $jobs;
	}

	public function getJobUrl($name) {
		return $this->z->core->url('jobs?job=' . $name . '&security_token=' . $this->security_token);
	}

}
