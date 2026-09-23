<?php

return [
	'quests' => [
		1 => [
			'title' => 'Basic supplies',
			'description' => 'To expand your homeworld, you first need enough resources. Mines produce them. Upgrade your metal and crystal mines to secure a steady supply. Mines also consume a lot of energy, which you can produce with solar plants.',
			'solution' => '<ul>
 <li>Click "Buildings" in the left menu.</li>
 <li>Select the Solar Plant.</li>
 <li>Click "Build" to start construction.</li>
 <li>The required resources will be deducted from your storage.</li>
 <li>Once the Solar Plant is complete, build a Metal Mine.</li>
 <li>If you started the wrong construction, click "Cancel" to get all resources back.</li>
 <li>Click a building image to learn more about it.</li>
 <li>A common starting order is: Solar Plant level 1, Metal Mine levels 1 and 2, Solar Plant level 2, Metal Mine levels 3 and 4, Solar Plant level 3, Crystal Mine levels 1 and 2.</li>
 </ul>',
		],
		2 => [
			'title' => 'Planetary defense',
			'description' => 'From the start, you need to protect your resources from raids. Rocket Launchers are a simple first defense.',
			'solution' => '<ul>
 <li>You need deuterium to build defenses.</li>
 <li>Open Buildings and upgrade your Deuterium Synthesizer to level 2.</li>
 <li>Keep your energy supply up: deuterium production consumes a lot of energy.</li>
 <li>Select the Rocket Launcher in the defense menu.</li>
 <li>Check its technology tree for the requirements you must meet.</li>
 <li>Open Buildings and select the Robotics Factory.</li>
 <li>Once you have enough deuterium, upgrade the Robotics Factory to level 2.</li>
 <li>A level 2 Robotics Factory lets you build a Shipyard.</li>
 <li>After the Shipyard is complete, build a Rocket Launcher.</li>
 </ul>',
		],
		3 => [
			'title' => 'Planetary energy supply',
			'description' => 'Keep your mines supplied with enough energy so they can operate at full capacity.',
			'solution' => '<ul>
 <li>Upgrade your mines in the Buildings menu.</li>
 <li>If you need help, consult item 1 in the additional menu.</li>
 <li>Solar Plants are the cheapest energy source. Make sure your mines always have enough energy.</li>
 <li>If energy is scarce, reduce production of a less important resource in the Resources menu so you can maximize production of the one you need most.</li>
 </ul>',
		],
		4 => [
			'title' => 'Your first ship',
			'description' => 'Ships can defend you like Rocket Launchers, but they can also attack. To develop new ships and defenses, you need a Research Lab.',
			'solution' => '<ul>
 <li>Find the Research Lab in the defense structures menu.</li>
 <li>Look up the Small Cargo in the Shipyard technology tree.</li>
 <li>Check the Combustion Drive requirements for building a Small Cargo.</li>
 <li>Once the Research Lab is complete, research Energy Technology to level 1.</li>
 <li>Then research Combustion Drive to level 2.</li>
 <li>When both researches are complete, build a Small Cargo in your Shipyard.</li>
 </ul>',
		],
		5 => [
			'title' => 'Information networks',
			'description' => 'You are not alone in the universe! Contact with other players can help you find allies and trading partners. Many players join alliances to pursue common goals.',
			'solution' => '<ul>
 <li>You can rename your planet on the overview page.</li>
 <li>At the bottom of the page, click the link to the game forum.</li>
 <li>You can see the forum URL in your browser address bar.</li>
 <li>If you started playing XNova with a friend, search for their username, click the friend icon and send a friend request. If you do not know anyone yet, send a request to a neighbor. Viewing another solar system costs 10 deuterium per system.</li>
 <li>You can found an alliance with friends or join an existing one. Use the "Alliance" menu to create one, or select an alliance and apply to join it.</li>
 </ul>',
		],
		6 => [
			'title' => 'The merchant',
			'description' => 'The merchant is a premium feature that exchanges one resource for another at a set rate. The amount you can exchange is limited by your storage capacity.',
			'solution' => '<ul>
 <li>Build resource storage facilities in the Buildings menu.</li>
 <li>Open the Merchant from the left menu.</li>
 <li>Choose the resource you want to sell to summon a new merchant.</li>
 <li>Summoning a merchant costs credits. You can earn enough by completing the fifth training quest.</li>
 <li>You can get more credits from the Officers menu.</li>
 </ul>',
		],
		7 => [
			'title' => 'Fleet operations',
			'description' => 'You can also gather resources by raiding other planets, but some are well defended. Spy on a planet first to learn more about it.',
			'solution' => '<ul>
 <li>Check the Espionage Probe technology tree in the Shipyard, meet its requirements and build a probe.</li>
 <li>Choose a target in Galaxy view and note its coordinates.</li>
 <li>Open Fleets, select the Espionage Probe and click "Next".</li>
 <li>Enter the target coordinates and click "Next".</li>
 <li>Select the Espionage mission and launch the probe.</li>
 <li>You will be redirected to the fleet overview, where you can see your active fleets.</li>
 </ul>',
		],
		8 => [
			'title' => 'The vast unknown',
			'description' => 'The universe is endless. Brave explorers venture into deep space to discover resources or even face space pirates in battle.',
			'solution' => '<ul>
 <li>Research the first level of Expedition Technology.</li>
 <li>Send a fleet to position 16 in a solar system, or click "Expedition" in the Galaxy menu.</li>
 <li>Expedition outcomes are unpredictable. Do not send your entire fleet.</li>
 </ul>',
		],
		9 => [
			'title' => 'Expand your empire',
			'description' => 'An emperor always seeks to expand. Your homeworld is a good start, but eventually it will be fully developed. Settle new planets early for more resources and building space. Travel between your planets also helps protect your ships and resources from enemy attacks.',
			'solution' => '<ul>
 <li>Build a Colony Ship.</li>
 <li>Choose a suitable planet position and send the Colony Ship there.</li>
 <li>Consider sending some resources with it to support development.</li>
 </ul>',
		],
		10 => [
			'title' => 'Debris fields',
			'description' => 'Orbital battles leave fields of metal and crystal debris from destroyed ships. Recycling these fields is an important alternative source of resources.',
			'solution' => '<ul>
 <li>Build a Recycler in the Shipyard.</li>
 <li>Select a debris field in Galaxy view.</li>
 <li>Send the Recycler there. Make sure the destination type is "Debris field", not "Planet".</li>
 </ul>',
		],
	],
	'no_quest_selected' => 'No quest selected',
	'quest_not_found' => 'Quest not found',
	'quest_not_completed' => 'Quest not completed',
	'unknown_reward_officer' => 'Unknown officer in quest reward',
	'task_research' => 'Research <b>:element</b> to level :level',
	'task_fleet' => 'Build :amount fleet units of type <b>:element</b>',
	'task_defense' => 'Build :amount defense units of type <b>:element</b>',
	'task_build' => 'Build <b>:element</b> to level :level',
	'task_rename_planet' => 'Rename a planet',
	'task_friends_count' => 'Friends in the game: :count',
	'task_ally' => 'Join an alliance with :count players',
	'task_storage' => 'Build any resource storage facility',
	'task_trade' => 'Trade resources with the merchant',
	'task_fleet_mission' => 'Send a fleet on the mission: :mission',
	'task_planets' => 'Colonized planets: :count',
	'reward_metal' => ':amount units of metal',
	'reward_crystal' => ':amount units of crystal',
	'reward_deuterium' => ':amount units of deuterium',
	'reward_credits' => ':amount credits',
	'reward_research' => '<b>:element</b> research at level :level',
	'reward_fleet' => ':amount fleet units of type <b>:element</b>',
	'reward_defense' => ':amount defense units of type <b>:element</b>',
	'reward_build' => '<b>:element</b> building at level :level',
	'reward_officer' => 'Officer <b>:officer</b> for :days days',
	'reward_storage' => '+1 level for one resource storage facility',
];
