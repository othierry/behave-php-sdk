<?php

if (!function_exists('curl_init')) {
  throw new Exception('Behave needs the CURL PHP extension.');
}

if (!function_exists('json_decode')) {
  throw new Exception('Behaveeds the JSON PHP extension.');
}

class Behave {

  /**
   * Version.
   */
  const VERSION = '1.0.0';

  /**
   * API Root URL.
   */
  const API_ROOT_URL                 = 'http://api.behave.io';

  /**
   * Leaderboard types
   */
  const LEADERBOARD_TYPE_SCORE       = 0;
  const LEADERBOARD_TYPE_BEHAVIOURAL = 1;

  /**
   * Leaderboard scoring types
   */
  const LEADERBOARD_SCORE_MAX        = 0;
  const LEADERBOARD_SCORE_SUM        = 1;

  /**
   * Leaderboard time frames
   */
  const LEADERBOARD_TIME_ALLTIME     = 0;
  const LEADERBOARD_TIME_DAILY       = 1;
  const LEADERBOARD_TIME_WEEKLY      = 2;
  const LEADERBOARD_TIME_MONTHLY     = 3;

  /**
   * Singleton instance
   */
  private static $instance;

  /**
   * API App secret.
   */
  private $secret;

  /**
   * Private constructor
   */
  private function __construct($secret) {
    $this->secret = $secret;
  }

  /**
   * Designated initializer
   */
  public static function init($secret) {
    if (!Behave::$instance) {
      Behave::$instance = new Behave($secret);
    } 
    return Behave::$instance;
  }

  /**
   * Make an API call.
   *
   * @param string $path The API endpoint path
   * @param string $method The HTTP Method to use (GET, POST, PUT, DELETE, ...) 
   * @return mixed The decoded response
   */
  public function api($path, $method = 'GET', $params = array()) {
    $request = curl_init();

    if (substr($path, 0, 1) !== '/') {
      $path = '/' . $path;
    }

    curl_setopt($request, CURLOPT_URL, self::API_ROOT_URL . $path);
    curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($request, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($request, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json',
      'X-Behave-Api-Token: ' . $this->secret
    ));

    if ($method === 'POST' || $method === 'PUT') {
      curl_setopt($request, CURLOPT_POSTFIELDS, json_encode($params));
    } 

    $response = curl_exec($request);

    curl_close($request);

    return json_decode($response);
  }

  ///////////////////////////////////////
  /// API Player helpers              ///
  ///////////////////////////////////////

  /**
   * Track a player's behaviour
   *
   * @param $verb string The behaviour
   * @param $context mixed Optional The context it which the behaviour was taken, it can contain
   * any information you need for use within your recipes.
   * @return mixed The API response. It may contains unlocked rewards like badge, points, mission etc
   */
  public static function track($playerReferenceId, $verb, $context = array()) {
    Behave::raiseIfNotInitialized();
    $response = Behave::$instance->api("/players/{$playerReferenceId}/track", 'POST', array(
      'verb'    => $verb,
      'context' => $context
    ));
    return $response->error ? $response->error : $response->data;
  }

  /**
   * Identify the current player
   *
   * @param $referenceId string The behaviour
   * @param $traits mixed Optional The player traits. It can be anything you'd like to store.
   * However, some standards attributes are the following: (email, name)
   * @return mixed The API response. It may contains unlocked rewards like badge, points, mission etc
   */
  public static function identify($playerReferenceId, $traits = null) {
    Behave::raiseIfNotInitialized();
    return Behave::$instance->api("/players/$playerReferenceId/identify", 'POST', array(
      'traits' => $traits
    ));
  }

  //////////////////////////////////////
  /// API Badges helpers             ///
  //////////////////////////////////////

  /**
   * @return mixed The current player's COMPLETED badges.
   */
  public static function fetchPlayerBadges($playerReferenceId) {
    Behave::raiseIfNotInitialized();
    $response = Behave::$instance->api("players/{$playerReferenceId}/badges");
    return $response->error ? $response->error : $response->data;
  }

  //////////////////////////////////////
  /// API Leaderboards helpers       ///
  //////////////////////////////////////

  /**
   * Fetch given leaderboard result for given player
   *
   * @param string $leaderboardId The leaderboard id. It can either be the actual leaderboard id
   * in behave's database but also the distinct id you have defined when creating the leaderboard.
   * @param string $playerId The player id (You have used using identify())
   * @param mixed $options Results options (optional)
   */
  public static function fetchLeaderboardResultForPlayer($leaderboardId, $playerId, $options = array()) {
    Behave::raiseIfNotInitialized();
    $results = Behave::fetchLeaderboardResults($leaderboardId, array(
      'context'   => array_key_exists('context', $options) ? $options['context']   : null,
      'players'   => array($playerId),
      'positions' => 'absolute' 
    ));
    return count($results) == 0 ? null : $results[0];
  }

  /**
   * Fetch all leaderboard results for given player
   *
   * @param string $playerId The player id (You have used using identify())
   * @param mixed $options Results options (optional)
   */
  public static function fetchLeaderboardResultsForPlayer($playerId, $options = array()) {
    Behave::raiseIfNotInitialized();
    $response = Behave::$instance->api("leaderboards/player-results", 'POST', array(
      'player_id'    => $playerId,
      'leaderboards' => array_key_exists('leaderboards', $options) ? $options['leaderboards'] : null,
      'max'          => array_key_exists('max', $options)          ? $options['max']          : null,
    ));
    return $response->error ? $response->error : $response->data;
  }

  /**
   * Fetch leaderboard CURRENT results
   *
   * @param string $leaderboardId The leaderboard id. It can either be the actual leaderboard id
   * in behave's database but also the distinct id you have defined when creating the leaderboard.
   * We will fetch the leaderboard in that order: Find by distinct id (if defined) > Find by id
   * @param mixed $options Results options (optional)
   */
  public static function fetchLeaderboardResults($leaderboardId, $options = array()) {
    Behave::raiseIfNotInitialized();
    $limit   = array_key_exists('limit', $options) ? min($options['limit'], 1000) : 1000;
    $offset  = array_key_exists('page', $options)  ? ($options['page'] - 1) * $limit : 0;
    $max_pos = array_key_exists('max', $options)   ? $options['max'] : 0;
    // if we want the 3 first players, we set the limit to maximum 3 instead of 1000
    if ($max_pos > 0 && $limit > $max_pos) {
      $limit = $max_pos;
    }
    $response = Behave::$instance->api("leaderboards/{$leaderboardId}/results?offset=$offset&limit=$limit", 'POST', array(
      'context'   => array_key_exists('context', $options)   ? $options['context']   : null,
      'players'   => array_key_exists('players', $options)   ? $options['players']   : null,
      'positions' => array_key_exists('positions', $options) ? $options['positions'] : 'relative' 
    ));
    return $response->error ? $response->error : count($response->data) ? $response->data : array();
  }

  /**
   * Fetch leaderboard PREVIOUS results
   *
   * @param string $leaderboardId The leaderboard id. It can either be the actual leaderboard id
   * in behave's database but also the distinct id you have defined when creating the leaderboard.
   * We will fetch the leaderboard in that order: Find by distinct id (if defined) > Find by id
   * @param mixed $options Results options (optional)
   */
  public static function fetchLeaderboardPreviousResults($leaderboardId, $options = array()) {
    Behave::raiseIfNotInitialized();
    $limit   = array_key_exists('limit', $options) ? min($options['limit'], 1000) : 1000;
    $offset  = array_key_exists('page', $options)  ? ($options['page'] - 1) * $limit : 0;
    $max_pos = array_key_exists('max', $options)   ? $options['max'] : 0;
    // if we want the 3 first players, we set the limit to maximum 3 instead of 1000
    if ($max_pos > 0 && $limit > $max_pos) {
      $limit = $max_pos;
    }
    $response = Behave::$instance->api("leaderboards/{$leaderboardId}/results/prev?offset=$offset&limit=$limit", 'POST', array(
      'context'   => array_key_exists('context', $options)   ? $options['context']   : null,
      'players'   => array_key_exists('players', $options)   ? $options['players']   : null,
      'positions' => array_key_exists('positions', $options) ? $options['positions'] : 'relative' 
    ));
    return $response->error ? $response->error : count($response->data) ? $response->data : array();
  }

  /**
   * Iterate trought leaderboard CURRENT results (paginated)
   *
   * @param  string   $name     The name of the leaderboard
   * @param  function $iterator The callback function to be called when iterating (must take 2 argument: results (array) and page (number))
   * @param  mixed    $options  Leaderboard options (optional)
   */
  public static function iterateLeaderboardResults($leaderboardId, $iterator, $options = array()) {
    Behave::raiseIfNotInitialized();
    $page    = array_key_exists('page', $options)  ? $options['page']  : 1;
    $limit   = array_key_exists('limit', $options) ? min($options['limit'], 1000) : 1000;
    $max_pos = array_key_exists('max', $options)   ? $options['max']   : 0;
    $results = Behave::fetchLeaderboardResults($leaderboardId, $options);
    $resultsCount = count($results);
    // Get total fetched
    $total = ($page - 1) * $limit + $resultsCount;
    // If above, keep only needed elements
    if ($max_pos > 0 && $total > $max_pos) {
      $results = array_slice($results, 0, $resultsCount - ($total - $max_pos));
    }
    // fire iterator
    $iterator($results, $page);
    // If still need to fetch more results
    if ($resultsCount > 0 && ($max_pos === 0 || $total < $max_pos)) {
      $options['page'] = ++$page;
      Behave::iterateLeaderboardResults($leaderboardId, $iterator, $options);              
    }
  }

  /**
   * Iterate trought leaderboard PREVIOUS results (paginated)
   *
   * @param  string   $name     The name of the leaderboard
   * @param  function $iterator The callback function to be called when iterating (must take 2 argument: results (array) and page (number))
   * @param  mixed    $options  Leaderboard options (optional)
   */
  public static function iterateLeaderboardPreviousResults($leaderboardId, $iterator, $options = array()) {
    Behave::raiseIfNotInitialized();
    $page    = array_key_exists('page', $options)  ? $options['page']  : 1;
    $limit   = array_key_exists('limit', $options) ? min($options['limit'], 1000) : 1000;
    $max_pos = array_key_exists('max', $options)   ? $options['max']   : 0;
    $results = Behave::fetchLeaderboardPreviousResults($leaderboardId, $options);
    $resultsCount = count($results);
    // Get total fetched
    $total = ($page - 1) * $limit + $resultsCount;
    // If above, keep only needed elements
    if ($max_pos > 0 && $total > $max_pos) {
      $results = array_slice($results, 0, $resultsCount - ($total - $max_pos));
    }
    // fire iterator
    $iterator($results, $page);
    // If still need to fetch more results
    if ($resultsCount > 0 && ($max_pos === 0 || $total < $max_pos)) {
      $options['page'] = ++$page;
      Behave::iterateLeaderboardPreviousResults($leaderboardId, $iterator, $options);              
    }
  }

  /**
   * Create a new leaderboard
   *
   * @param  string $name    The name of the leaderboard
   * @param  mixed  $options Leaderboard options (optional)
   * @return mixed  (leaderboard) if success, string (error) if something went wrong
   */
  public static function createLeaderboard($name, $options = array()) 
  {
    Behave::raiseIfNotInitialized();
    $response = Behave::$instance->api('leaderboards', 'POST', array(
      'name'            => $name,
      'reference_id'    => $options['reference_id'],
      'type'            => $options['type'],
      'scoreType'       => $options['scoreType'],
      'timeFrame'       => $options['timeFrame'],
      'statusUpdateUrl' => $options['statusUpdateUrl'],
      'rewards'         => $options['rewards'],
      'active'          => $options['active']
    ));
    return $response->error ? $response->error : $response->data;
  }

  /**
   * Reset a leaderboard. This CANNOT be undone. Previous results will be archived.
   * Only leaderboard with all-time timeframe can be reset manually
   *
   * @param $leaderboardIdOrRefId string The leaderboard id. It can either be the actual leaderboard id
   * in behave's database but also the distinct id you have defined when creating the leaderboard.
   * We will fetch the leaderboard in that order: Find by distinct id (if defined) > Find by id
   * @return null if success, error string if something went wrong
   */
  public static function resetLeaderboard($leaderboardIdOrRefId) {
    Behave::raiseIfNotInitialized();
    $response = Behave::$instance->api("leaderboards/{$leaderboardIdOrRefId}/reset");
    return $response->error ? $response->error : null;
  }

  /**
   * Delete a leaderboard. This CANNOT be undone
   * @param $leaderboardIdOrRefId string The leaderboard id. It can either be the actual leaderboard id
   * in behave's database but also the distinct id you have defined when creating the leaderboard.
   * We will fetch the leaderboard in that order: Find by distinct id (if defined) > Find by id
   */
  public static function deleteLeaderboard($leaderboardIdOrRefId) {
    Behave::raiseIfNotInitialized();
    Behave::$instance->api("leaderboards/{$leaderboardIdOrRefId}", 'DELETE');
  }

  //////////////////////////////////////
  /// PROTECTED                      ///
  //////////////////////////////////////

  protected static function raiseIfNotInitialized() {
    if (!Behave::$instance || !Behave::$instance->secret) {
      throw new Exception('Behave::init() must be called with a valid app secret before any other methods can be used.');
    }
  }

}

?>