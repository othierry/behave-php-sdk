<?php

namespace Behave;

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
   * Client singleton
   */
  private static $client;

  /**
   * Designated initializer
   */
  public static function init($token) {
    Behave::$client = Client::createClient(array('app_token' => $token));
  }

  //////////////////////////////////////
  /// Guzzle Client Getter           ///
  //////////////////////////////////////

  public static function getClient() {
    if (!Behave::$client) {
      throw new Exception('Behave::init() must be called with a valid app token for the client to be created');
    }
    return Behave::$client;
  }

  ///////////////////////////////////////
  /// API Player helpers              ///
  ///////////////////////////////////////

  /**
   * Track a player's behaviour
   *
   * @param $playerId string The id of the player taking the bahavior
   * @param $verb string The behaviour
   * @param $context mixed Optional The context it which the behaviour was taken, it can contain
   * any information you need for use within your recipes.
   * @return mixed The API response. It may contains unlocked rewards like badge, points, mission etc
   */
  public static function track($playerId, $verb, $context = array()) {
    return Behave::getClient()->track(array(
      'playerId'  => $playerId,
      'behaviour' => $verb,
      'context'   => $context
    ))->get('data');
  }

  /**
   * Identify the current player
   *
   * @param $playerId string The behaviour
   * @param $traits mixed Optional The player traits. It can be anything you'd like to store.
   * However, some standards attributes are the following: (email, name)
   * @return mixed The API response. It may contains unlocked rewards like badge, points, mission etc
   */
  public static function identify($playerId, $traits = null, $timestamp = null) {
    return Behave::getClient()->identify(array(
      'playerId'  => $playerId,
      'traits'    => $traits,
      'timestamp' => $timestamp
    ))->get('data');
  }

  //////////////////////////////////////
  /// API Badges helpers             ///
  //////////////////////////////////////

  /**
   * @param $playerId string The player we need to fetch badges from
   * @return mixed The current player's COMPLETED badges.
   */
  public static function fetchPlayerBadges($playerId) {
    return Behave::getClient()->fetchPlayerBadges(array(
      'playerId' => $playerId
    ))->get('data');
  }

  /**
   * Create a new badge
   *
   * @param  string $name        The name of the badge
   * @param  string $referenceId The unique custom id you want to use to identify this badge.
   * @param  string $icon The URL pointing to the icon of the
   * @param  mixed  $options badge options (optional)
   * @return mixed  (badge) if success, string (error) if something went wrong
   */
  public static function createBadge($name, $referenceId, $icon, $options = array())
  {
    $options['name'] = $name;
    $options['reference_id'] = $referenceId;
    $options['icon'] = $icon;
    return Behave::getClient()->createBadge($options)->get('data');
  }

  //////////////////////////////////////
  /// API Leaderboards helpers       ///
  //////////////////////////////////////

  /**
   * Fetch all leaderboard results for given player
   *
   * @param string $playerId The player id (You have used using identify())
   * @param mixed $options Results options (optional)
   */
  public static function fetchLeaderboardResultsForPlayer($playerId, $options = array()) {
    $options['playerId'] = $playerId;
    return Behave::getClient()->fetchLeaderboardResultsForPlayer($options);
  }

  /**
   * Fetch given leaderboard result for given player
   *
   * @param string $leaderboardId The leaderboard id. It can either be the actual leaderboard id
   * in behave's database but also the distinct id you have defined when creating the leaderboard.
   * @param string $playerId The player id (You have used using identify())
   * @param mixed $options Results options (optional)
   */
  public static function fetchLeaderboardResultForPlayer($leaderboardId, $playerId, $options = array()) {
    $options['leaderboards'] = array($leaderboardId);
    $results = Behave::fetchLeaderboardResultsForPlayer($playerId, $options)->get('data');
    // High-level filtering here we return the first element or null (if no score for that player on that leaderboard)
    // of the results array returned by the API so we do not need to do the check at the App level.
    return count($results) == 0 ? null : $results[0];
  }

  /**
   * Create a new leaderboard
   *
   * @param  string $name        The name of the leaderboard
   * @param  string $referenceId The unique custom id you want to use to identify this leaderboard. REQUIRED when creating leaderboards from SDK
   * @param  mixed  $options Leaderboard options (optional)
   * @return mixed  (leaderboard) if success, string (error) if something went wrong
   */
  public static function createLeaderboard($name, $referenceId, $options = array()) 
  {
    $options['name'] = $name;
    $options['reference_id'] = $referenceId;
    return Behave::getClient()->createLeaderboard($options)->get('data');
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
    return Behave::getClient()->resetLeaderboard(array(
      'leaderboardId' => $leaderboardIdOrRefId,
    ));
  }

  /**
   * Delete a leaderboard. This CANNOT be undone
   * @param $leaderboardIdOrRefId string The leaderboard id. It can either be the actual leaderboard id
   * in behave's database but also the distinct id you have defined when creating the leaderboard.
   * We will fetch the leaderboard in that order: Find by distinct id (if defined) > Find by id
   */
  public static function deleteLeaderboard($leaderboardIdOrRefId) {
    return Behave::getClient()->deleteLeaderboard(array(
      'leaderboardId' => $leaderboardIdOrRefId
    ));
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
    $limit   = array_key_exists('limit', $options) ? min($options['limit'], 1000) : 1000;
    $offset  = array_key_exists('page', $options)  ? ($options['page'] - 1) * $limit : 0;
    $options['leaderboardId'] = $leaderboardId;
    $options['limit'] = $limit;
    $options['offset'] = $offset;
    return Behave::getClient()->fetchLeaderboardResults($options)->get('data');
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
    $limit   = array_key_exists('limit', $options) ? min($options['limit'], 1000) : 1000;
    $offset  = array_key_exists('page', $options)  ? ($options['page'] - 1) * $limit : 0;
    $options['leaderboardId'] = $leaderboardId;
    $options['limit'] = $limit;
    $options['offset'] = $offset;
    return Behave::getClient()->fetchLeaderboardPreviousResults($options)->get('data');
  }

  /**
   * Iterate trought leaderboard CURRENT results (paginated)
   *
   * @param  string $leaderboardId The id of the leaderboard
   * @param  function $iterator The callback function to be called when iterating (must take 2 argument: results (array) and page (number))
   * @param  mixed $options  Leaderboard options (optional)
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
    if ($resultsCount > 0 && $resultsCount === $limit && ($max_pos === 0 || $total < $max_pos)) {
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
}