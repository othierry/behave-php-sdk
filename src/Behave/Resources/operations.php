<?

return array(
	
	'operations' => array(		
		'track' => array(
			'httpMethod' => 'POST',
			'uri' => "players/{playerId}/track",
			'summary' => 'Track a behaviour',
			'responseClass' => 'ObjectOutput',
			'responseType' => 'model',
			'class' => 'Behave\\Command\\TokenAuthCommand',			
			'parameters' => array(
				'playerId' => array(
					'type' => 'string',
					'location' => 'uri',
					'required' => true					
				),
				'behaviour' => array(
					'type' => 'string',
					'sentAs' => 'verb',
					'location' => 'json',
					'required' => true
				),
				'context' => array(
					'type' => 'object',
					'location' => 'json',
					'required' => false
				),
			),
		),
		
		'identify' => array(
			'httpMethod' => 'POST',
			'uri' => "players/{playerId}/identify",
			'summary' => 'Identify a user',
			'responseClass' => 'ObjectOutput',
			'responseType' => 'model',
			'class' => 'Behave\\Command\\TokenAuthCommand',			
			'parameters' => array(
				'playerId' => array(
					'type' => 'string',
					'location' => 'uri',
					'required' => true					
				),
				'traits' => array(
					'type' => 'object',
					'location' => 'json',
					'required' => false
				),
				'timestamp' => array(
					'type' => 'integer',
					'location' => 'json',
					'required' => false
				),
			),
		),

		'fetchPlayerBadges' => array(
			'httpMethod' => 'GET',
			'uri' => "players/{playerId}/badges",
			'summary' => 'Fetch player\'s unlocked badges',
			'responseClass' => 'ArrayOutput',
			'responseType' => 'model',
			'class' => 'Behave\\Command\\TokenAuthCommand',			
			'parameters' => array(
				'playerId' => array(
					'type' => 'string',
					'location' => 'uri',
					'required' => true					
				)
			),
		),

		'fetchLeaderboardResults' => array(
			'httpMethod' => 'POST',
			'uri' => "leaderboards/{leaderboardId}/results{?offset}{&limit}",
			'summary' => 'Fetch Leaderboard Results',
			'responseClass' => 'ArrayOutput',
			'responseType' => 'model',
			'class' => 'Behave\\Command\\TokenAuthCommand',			
			'parameters' => array(
				'leaderboardId' => array(
					'type' => 'string',
					'location' => 'uri',
					'required' => true
				),
				'limit' => array(
					'type' => 'integer',
					'location' => 'query',
					'required' => true,
					'default' => 1000
				),
				'offset' => array(
					'type' => 'integer',
					'location' => 'query',
					'required' => true,
					'default' => 0
				),
				'context' => array(
					'type' => 'object',
					'location' => 'json',
					'required' => false,
				),
				'players' => array(
					'type' => 'array',
					'location' => 'json',
					'required' => false,
				),
				'playerId' => array(
					'type' => 'string',
					'location' => 'json',
					'required' => false,
					'sentAs' => 'player_id'
				),
				'positions' => array(
					'type' => 'string',
					'location' => 'json',
					'required' => true,
					'default' => 'relative'
				),				
			),
		),		

		'fetchLeaderboardResultsForPlayer' => array(
			'httpMethod' => 'POST',
			'uri' => "leaderboards/player-results",
			'summary' => 'Fetch leaderboard results for given player on given leaderboards (all by default)',
			'responseClass' => 'ArrayOutput',
			'responseType' => 'model',
			'class' => 'Behave\\Command\\TokenAuthCommand',			
			'parameters' => array(
				'playerId' => array(
					'type' => 'string',
					'location' => 'json',
					'required' => true,
					'sentAs' => 'player_id'
				),
				'leaderboards' => array(
					'type' => 'array',
					'location' => 'json',
					'required' => false,
				),
				'max' => array(
					'type' => 'integer',
					'location' => 'json',
					'required' => false
				)								
			)
		),

		'fetchLeaderboardResultForPlayer' => array(
			'extends' => 'fetchLeaderboardResultsForPlayer',
			'summary' => 'Fetch leaderboard result for the given player on given leaderboards',
			'parameters' => array(
				'leaderboardId' => array(
					'type' => 'string',
					'location' => 'json',
					'sentAs' => 'leaderboards',
					'required' => true,
				),
			)
		),

		'createBadge' => array(
			'httpMethod' => 'POST',
			'uri' => 'badges',
			'summary' => 'Create a new badge.',
			'responseClass' => 'ObjectOutput',
			'responseType' => 'model',
			'class' => 'Behave\\Command\\TokenAuthCommand',
			'parameters' => array(
				'name' => array(
					'type' => 'string',
					'location' => 'json',
					'required' => true,
				),
				'reference_id' => array(
					'type' => 'string',
					'location' => 'json',
					'required' => true,
				),
				'icon' => array(
					'type' => 'string',
					'location' => 'json',
					'required' => true,
				),
				'hint' => array(
					'type' => 'string',
					'location' => 'json',
					'required' => false,
				),
				'message' => array(
					'type' => 'string',
					'location' => 'json',
					'required' => false,
				),
				'limit' => array(
					'type' => 'integer',
					'location' => 'json',
					'required' => true,
					'default' => 0
				),
				'unique' => array(
					'type' => 'boolean',
					'location' => 'json',
					'required' => true,
					'default' => true
				),
				'active' => array(
					'type' => 'boolean',
					'location' => 'json',
					'required' => true,
					'default' => true
				),
			)
		),

		'createLeaderboard' => array(
			'httpMethod' => 'POST',
			'uri' => 'leaderboards',
			'summary' => 'Create a new leaderboard.',
			'responseClass' => 'ObjectOutput',
			'responseType' => 'model',
			'class' => 'Behave\\Command\\TokenAuthCommand',			
			'parameters' => array(
				'name' => array(
					'type' => 'string',
					'location' => 'json',
					'required' => true,
				),
				'reference_id' => array(
					'type' => 'string',
					'location' => 'json',
					'required' => true,
				),	
				'scoreType' => array(
					'type' => 'string',
					'location' => 'json',
					'false' => true,
				),
				'timeFrame' => array(
					'type' => 'string',
					'location' => 'json',
					'false' => true,
				),
				'rewards' => array(
					'type' => 'array',
					'location' => 'json',
					'false' => true,
				),
				'metadata' => array(
					'type' => 'object',
					'location' => 'json',
					'false' => false,
				),
				'active' => array(
					'type' => 'boolean',
					'location' => 'json',
					'false' => true,
				),
			)
		),

		'updateLeaderboard' => array(
			'httpMethod' => 'PUT',
			'uri' => "leaderboards/{leaderboardId}",
			'summary' => 'Update a leaderboard',
			'class' => 'Behave\\Command\\TokenAuthCommand',
			'parameters' => array(
				'leaderboardId' => array(
					'type' => 'string',
					'location' => 'uri',
					'required' => true,
				),
				'name' => array(
					'type' => 'string',
					'location' => 'json',
					'required' => false,
				),
				'reference_id' => array(
					'type' => 'string',
					'location' => 'json',
					'required' => false,
				),
				'scoreType' => array(
					'type' => 'string',
					'location' => 'json',
					'false' => false,
				),
				'timeFrame' => array(
					'type' => 'string',
					'location' => 'json',
					'false' => false,
				),
				'rewards' => array(
					'type' => 'array',
					'location' => 'json',
					'false' => false,
				),
				'metadata' => array(
					'type' => 'object',
					'location' => 'json',
					'false' => false,
				),
				'active' => array(
					'type' => 'boolean',
					'location' => 'json',
					'false' => false,
				),
			)
		),

		'deleteLeaderboard' => array(
			'httpMethod' => 'DELETE',
			'uri' => "leaderboards/{leaderboardId}",
			'summary' => 'Delete a leaderboard',
			'class' => 'Behave\\Command\\TokenAuthCommand',			
			'parameters' => array(
				'leaderboardId' => array(
					'type' => 'string',
					'location' => 'uri',
					'required' => true,
				)
			)
		),

	),

	'models' => array(
		
		'ArrayOutput' => array(
			'type' => 'array',
			'additionalProperties' => array(
				'type' => 'ObjectOutput',
				'location' => 'json'
			),
		),

		'ObjectOutput' => array(
		   'type' => 'object',
			'location' => 'json',
			'additionalProperties' => array(
				'location' => 'json'	
			)
		),
		
		'HeaderOutput' => array(
            'type' => 'object',
            'properties' => array(
                'location' => array(
                    'location' => 'header',
                    'sentAs' => 'Location',
                    'type' => 'string'
                )
            )
		),		
	),
);
