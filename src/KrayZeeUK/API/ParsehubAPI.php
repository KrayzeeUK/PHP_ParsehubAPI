<?php

	namespace KrayZeeUK\API;

	use Exception;

	//declare(strict_types=1);

	/**
	 *
	 */
	class ParsehubAPI {

		/**
		 * @var string API Key storage
		 */
		private $apiKey = "";

		/**
		 * @param string $apiKey Parsehub API Key
		 */
		public function __construct( string $apiKey = "" ) {
			if ( !empty( $apiKey ) ) {
				$this->apiKey = $apiKey;
			}
		}

		/**
		 * Set Parsehub API key
		 *
		 * @param string $apiKey
		 */
		public function setApiKey( string $apiKey ): void {
			$this->apiKey = $apiKey;
		} // The API key for your account.

		/**
		 * This will return the project object for a specific project.
		 *
		 * @param string $projectToken   Token for the project you wish to return
		 * @param int    $offset         Specifies the offset from which to start the run_list.
		 *                               E.g. in order to get most recent runs 21-40, specify an offset of 20. Defaults to 0.
		 * @param bool   $includeOptions Includes the “options_json” key in the result returned. For performance reasons,
		 *                               we exclude this key by default.
		 * @param bool   $decodeJSON     Decode the JSON data into PHP Array
		 *
		 * @return array                If successful, returns the project identified by $projectToken.
		 *                               The project will have an additional run_list attribute which has a list of the most
		 *                               recent 20 runs, starting at the offset the most recent. The run_list has no order guarantees;
		 *                               you must sort it yourself if you’d like to have it sorted by some attribute.
		 * @throws Exception
		 */
		public function getProject( string $projectToken, int $offset = 0, bool $includeOptions = FALSE, bool $decodeJSON = FALSE ): array {

			if ( empty( $this->apiKey ) ) {
				throw new Exception( "API Key must be set before calling" );
			}

			if ( empty( $projectToken ) ) {
				throw new Exception( "Invalid Project Token provided" );
			}

			$params = http_build_query(
				array(
					"api_key" => $this->apiKey,
					"offset" => $offset,
					"include_options" => ( $includeOptions ? 1 : 0 )
				)
			);

			$options = array( 'http' => array( 'method' => 'GET' ) );

			return $this->callParsehubAPI(
				"https://parsehub.com/api/v2/projects/$projectToken",
				$options,
				$params,
				$decodeJSON
			);
		}

		/**
		 * This will start running an instance of the project on the ParseHub cloud. It will create a new run object.
		 * This method will return immediately, while the run continues in the background.
		 * You can use webhooks or polling to figure out when the data for this run is ready in order to retrieve it.
		 *
		 * @param string $projectToken       Token for the project you wish to return
		 * @param string $startURL           The url to start running on. Defaults to the project’s start_site.
		 * @param string $startTemplate      The template to start running with.
		 *                                   Defaults to the project's start_template (inside the options_json).
		 * @param string $startValueOverride The starting global scope for this run.
		 *                                   This can be used to pass parameters to your run.
		 *                                   For example, you can pass {"query": "San Francisco"} to use the query somewhere in your run.
		 *                                   Defaults to the project’s start_value.
		 * @param bool   $sendEmail          If set to anything other than 0, send an email when the run either
		 *                                   completes successfully or fails due to an error. Defaults to 0.
		 * @param bool   $decodeJSON         Decode the JSON data into PHP Array
		 *
		 * @return array                    If successful, returns the run object that was created.
		 * @throws Exception
		 */
		public function runProject( string $projectToken, string $startURL = "", string $startTemplate = "", string $startValueOverride = "", bool $sendEmail = FALSE, bool $decodeJSON = FALSE ): array {

			if ( empty( $this->apiKey ) ) {
				throw new Exception( "API Key must be set before calling" );
			}

			if ( empty( $projectToken ) ) {
				throw new Exception( "Invalid Project Token provided" );
			}

			$params["api_key"] = $this->apiKey;
			if ( !empty( $startURL ) ) {
				$params["start_url"] = $startURL;
			}
			if ( !empty( $startTemplate ) ) {
				$params["start_template"] = $startTemplate;
			}
			if ( !empty( $startURL ) ) {
				$params["start_value_override"] = $startValueOverride;
			}

			$params["send_email"] = $sendEmail ? 1 : 0;

			$options = array(
				'http' => array(
					'method' => 'POST',
					'header' => 'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
					'content' => http_build_query( $params )
				)
			);

			return $this->callParsehubAPI(
				"https://parsehub.com/api/v2/projects/$projectToken/run",
				$options,
				"",
				$decodeJSON
			);
		}

		/**
		 * This gets a list of projects in your account
		 *
		 * @param int  $offset          Specifies the offset from which to start the projects.
		 *                              E.g. in order to get projects 21-40, specify an offset of 20. Defaults to 0.
		 * @param int  $limit           Specifies how many entries will be returned in projects.
		 *                              Accepts values between 1 and 20 inclusively. Defaults to 20.
		 * @param bool $includeOptions  Adds options_json, main_template, main_site and webhook to the entries of projects.
		 *                              Set this parameter to 1 if you intend to use them in ParseHub API calls.
		 *                              This parameter requires use of the offset and limit parameters to access
		 *                              the full list of projects.
		 * @param bool $decodeJSON      Decode the JSON data into PHP Array
		 *
		 * @return array               If successful, returns an object
		 * @throws Exception
		 */
		public function ListProjects( int $offset = 0, int $limit = 20, bool $includeOptions = FALSE, bool $decodeJSON = FALSE ): array {

			if ( empty( $this->apiKey ) ) {
				throw new Exception( "API Key must be set before calling" );
			}

			$params = http_build_query( array(
				"api_key" => $this->apiKey,
				"offset" => $offset,
				"limit" => $limit,
				"include_options" => $includeOptions ? 1 : 0
			) );

			$options = array(
				'http' => array(
					'method' => 'GET'
				)
			);

			return $this->callParsehubAPI(
				"https://parsehub.com/api/v2/projects",
				$options,
				$params,
				$decodeJSON
			);
		}

		/**
		 * This returns the run object for a given run token.
		 * You can call this method repeatedly to poll for when a run is done, though we recommend using a webhook instead.
		 * This method is rate-limited. For each run, you may make at most 25 calls during the first 5 minutes after the run started,
		 * and at most one call every 3 minutes after that.
		 *
		 * @param string $runToken   Token referring to the run you wish to return
		 * @param bool   $decodeJSON Decode the JSON data into PHP Array
		 *
		 * @return array           If successful, returns the run identified by $runToken
		 * @throws Exception
		 */
		public function getRun( string $runToken, bool $decodeJSON = FALSE ): array {

			if ( empty( $this->apiKey ) ) {
				throw new Exception( "API Key must be set before calling" );
			}

			if ( empty( $runToken ) ) {
				throw new Exception( "Invalid Run Token provided" );
			}

			$params = http_build_query( array(
				"api_key" => $this->apiKey
			) );

			$options = array(
				'http' => array(
					'method' => 'GET'
				)
			);

			return $this->callParsehubAPI(
				"https://parsehub.com/api/v2/runs/$runToken",
				$options,
				$params,
				$decodeJSON
			);
		}

		/**
		 * This returns the data that was extracted by a run.
		 *
		 * @param string $runToken   Token referring to the run you wish to return
		 * @param string $format     The format that you would like to get the data in. Possible values csv or json. Defaults to json.
		 * @param bool   $decodeJSON Decode the JSON data into PHP Array
		 *
		 * @return array             If successful, returns the data in either csv or json format, depending on the format parameter.
		 *                           Note: The Content-Encoding of this response is always gzip.
		 * @throws Exception
		 */
		public function getRunData( string $runToken, string $format = "json", bool $decodeJSON = FALSE ): array {

			if ( empty( $this->apiKey ) ) {
				throw new Exception( "API Key must be set before calling" );
			}

			if ( empty( $runToken ) ) {
				throw new Exception( "Invalid Run Token provided" );
			}

			$params = http_build_query( array(
				"api_key" => $this->apiKey,
				"format" => $format
			) );

			$options = array(
				'http' => array(
					'method' => 'GET'
				)
			);

			return $this->callParsehubAPI(
				"https://parsehub.com/api/v2/runs/$runToken/data",
				$options,
				$params,
				$decodeJSON
			);
		}

		/**
		 * This returns the data for the most recent ready run for a project.
		 * You can use this method in order to have a synchronous interface to your project.
		 *
		 * @param string $projectToken Token for the project you wish to return
		 * @param string $format       The format that you would like to get the data in. Possible values csv or json. Defaults to json.
		 * @param bool   $decodeJSON   Decode the JSON data into PHP Array
		 *
		 * @return array
		 * @throws Exception
		 */
		public function getLastReadyData( string $projectToken, string $format = "json", bool $decodeJSON = FALSE ): array {

			if ( empty( $this->apiKey ) ) {
				throw new Exception( "API Key must be set before calling" );
			}

			if ( empty( $projectToken ) ) {
				throw new Exception( "Invalid Project Token provided" );
			}

			$params = http_build_query( array(
				"api_key" => $this->apiKey,
				"format" => $format
			) );

			$options = array(
				'http' => array(
					'method' => 'GET'
				)
			);

			return $this->callParsehubAPI(
				"https://parsehub.com/api/v2/projects/$projectToken/last_ready_run/data",
				$options,
				$params,
				$decodeJSON
			);
		}

		/**
		 * This cancels a run in progress and changes its status to cancelled. Any data that was extracted so far will be available.
		 *
		 * @param string $runToken   Token referring to the run you wish to cancel
		 * @param bool   $decodeJSON Decode the JSON data into PHP Array
		 *
		 * @return array             If successful, returns the run identified by $runToken
		 * @throws Exception
		 */
		public function cancelRun( string $runToken, bool $decodeJSON = FALSE ): array {

			if ( empty( $this->apiKey ) ) {
				throw new Exception( "API Key must be set before calling" );
			}

			if ( empty( $runToken ) ) {
				throw new Exception( "Invalid Run Token provided" );
			}

			$params = array(
				"api_key" => $this->apiKey,
			);

			$options = array(
				'http' => array(
					'method' => 'POST',
					'header' => 'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
					'content' => http_build_query( $params )
				)
			);

			return $this->callParsehubAPI(
				"https://parsehub.com/api/v2/runs/$runToken/cancel",
				$options,
				"",
				$decodeJSON
			);
		}

		/**
		 * This cancels a run if running, and deletes the run and its data.
		 *
		 * @param string $runToken   Token referring to the run you wish to cancel
		 * @param bool   $decodeJSON Decode the JSON data into PHP Array
		 *
		 * @return array             If successful, returns an object with
		 * @throws Exception
		 */
		public function deleteRun( string $runToken, bool $decodeJSON = FALSE ): array {

			if ( empty( $this->apiKey ) ) {
				throw new Exception( "API Key must be set before calling" );
			}

			if ( empty( $runToken ) ) {
				throw new Exception( "Invalid Run Token provided" );
			}

			$params = http_build_query( array(
				"api_key" => $this->apiKey
			) );

			$options = array(
				'http' => array(
					'method' => 'DELETE'
				)
			);

			return $this->callParsehubAPI(
				"https://parsehub.com/api/v2/runs/$runToken",
				$options,
				$params,
				$decodeJSON
			);
		}

		/*
		 * Internal Functions
		 */

		/**
		 * @param string $url        API URL To call
		 * @param array  $options    API call options
		 * @param string $parameters Parameters of call.  Must be pre-encoded using http_build_query
		 *
		 * @return array
		 * @throws Exception
		 */
		private function callParsehubAPI( string $url, array $options, string $parameters = "", bool $decodeArray = FALSE ): array {

			$url .= ( !empty( $parameters ) ) ? "?$parameters" : "";

			$apiReturn = @file_get_contents(
				$url,
				FALSE,
				stream_context_create( $options )
			); // Get API Response

			if ( $apiReturn === FALSE ) {
				throw new Exception( "Failed to retrieve results from API" );
			}

			if ( in_array( "Content-Encoding: gzip", $http_response_header ) ) {
				$apiReturn = gzdecode( $apiReturn ); // Contents are compressed so decompress them.
			}

			if ( $decodeArray ) {
				return json_decode( $apiReturn, TRUE, 512, JSON_PRETTY_PRINT ) ?? array(); // Json decode results
			}

			return array( "json" => $apiReturn ); // Return raw json
		}
	}