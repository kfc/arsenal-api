<?php

class GamecastApi extends DrupalApi {

	function getGamecast($match_nid) {

		if((int)$match_nid <= 0 ){
			return 'Invalid Data';
		}
		$return = array();
		$result = db_select('field_data_field_gamecast_events','evts');

		$result->innerJoin('field_data_field_gamecast_match', 'm', 'evts.entity_id = m.entity_id');

		$result->leftJoin('field_data_field_gamecast_event_minute', 'minute', 'minute.entity_id = evts.field_gamecast_events_value');
		$result->leftJoin('field_data_field_gamecast_event_type', 'type', 'type.entity_id = evts.field_gamecast_events_value');
		$result->leftJoin('field_data_field_gamecast_event_text', 'text', 'text.entity_id = evts.field_gamecast_events_value');
		$result->leftJoin('field_data_field_gamecast_event_photo', 'photo', 'photo.entity_id = evts.field_gamecast_events_value');
		$result->leftJoin('field_data_field_gamecast_event_video', 'video', 'video.entity_id = evts.field_gamecast_events_value');

		$result->leftJoin('taxonomy_term_data', 'term', 'term.tid = type.field_gamecast_event_type_tid');
		$result->leftJoin('field_data_field_match_event_type_code', 'type_term', 'term.tid = type_term.entity_id');
		$result->leftJoin('field_data_field_match_event_gamecast_icon', 'icon_term', 'term.tid = icon_term.entity_id');

		$result->leftJoin('file_managed', 'file_photo', 'file_photo.fid = photo.field_gamecast_event_photo_fid');
		$result->leftJoin('file_managed', 'file_video', 'file_video.fid = video.field_gamecast_event_video_fid');
		$result->leftJoin('file_managed', 'file_icon', 'file_icon.fid = icon_term.field_match_event_gamecast_icon_fid');

		$result->addField('evts', 'field_gamecast_events_value', 'event_id');
		$result->addField('minute', 'field_gamecast_event_minute_value', 'minute');
		//$result = $result->fields('term', array('field_gamecast_event_minute_value'));
		$result->addField('text', 'field_gamecast_event_text_value', 'text');
		$result->addField('file_photo', 'uri', 'photo_url');
		$result->addField('file_video', 'uri', 'video_url');
		$result->addField('file_video', 'fid', 'video_url_fid');
		$result->addField('type_term', 'field_match_event_type_code_value', 'code');
		$result->addField('file_icon', 'uri', 'icon_url');

		$result = $result->condition('m.field_gamecast_match_nid', $match_nid)
			->orderBy('CAST(minute.field_gamecast_event_minute_value AS UNSIGNED)', 'DESC')
			->orderBy('event_id', 'DESC')
			->execute()
			->fetchAllAssoc('event_id');

		if(!empty($result)) {
			foreach($result as &$_row) {
				$_row->photo_url = $this->fileUrl($_row->photo_url);
				$_row->icon_url = $this->fileUrl($_row->icon_url);
				$_row->video_url_fid = $_row->video_url_fid;
				$_row->video_url = $this->videoUrl($_row->video_url);
				$return[] = $_row;
			}
		}
		return $this->jsonResonse($return);
	}

	function getMatchEvents($match_nid) {
		if((int)$match_nid <= 0 ){
			return 'Invalid Data';
		}
		$stats = $this->_getMatchStats($match_nid);

		$return = array(
			'result' => $stats['result'],
			'stats' => $stats['stats'],
			'current_minute' => $stats['current_minute'],
			'events' => array(
				'arsenal' => $this->_getArsenalEvents($match_nid),
				'opponent' => $this->_getOpponentEvents($match_nid)

			)
		);
		return $this->jsonResonse($return);
	}

	function getMatchInfo($match_nid) {
		if((int)$match_nid <= 0 ){
			return 'Invalid Data';
		}

		$result = db_select('node', 'n');

		$result->innerJoin('field_data_field_match_opponent','opponent', 'opponent.entity_id = n.nid');
		$result->innerJoin('node','opponent_node', 'opponent_node.nid = opponent.field_match_opponent_nid');

		$result->innerJoin('field_data_field_match_season','season', 'n.nid = season.entity_id');
		$result->innerJoin('taxonomy_term_data','season_term', 'season_term.tid = season.field_match_season_tid');

		$result->innerJoin('field_data_field_match_tournament','tournament', 'n.nid = tournament.entity_id');
		$result->innerJoin('taxonomy_term_data','tournament_term', 'tournament_term.tid = tournament.field_match_tournament_tid');

		$result->innerJoin('field_data_field_match_round','round', 'n.nid = round.entity_id');

		$result->innerJoin('field_data_field_match_place','place', 'n.nid = place.entity_id');

		$result->innerJoin('field_data_field_match_start_date','date', 'n.nid = date.entity_id');

		$result->innerJoin('field_data_field_match_stadium','stadium', 'stadium.entity_id = n.nid');
		$result->innerJoin('node','stadium_node', 'stadium_node.nid = stadium.field_match_stadium_nid');

		$result->innerJoin('field_data_field_team_logo','logo', 'opponent_node.nid = logo.entity_id');
		$result->leftJoin('file_managed', 'opponent_logo', 'opponent_logo.fid = logo.field_team_logo_fid');
		$result->leftJoin('field_data_field_online_translation','online', 'online.entity_id = n.nid');
		//$result->leftJoin('node','referee_node', 'referee_node.nid = referee.field_match_referee_nid');

		//$result->leftJoin('field_data_field_match_attendance','attendance', 'n.nid = attendance.entity_id');

		//$result->leftJoin('field_data_field_match_result','result', 'n.nid = result.entity_id');



		$result->fields('n', array('title'));
		$result->addField('opponent_node', 'title', 'opponent');
		$result->addField('season_term', 'name', 'season');
		$result->addField('tournament_term', 'name', 'tournament');
		$result->addField('round', 'field_match_round_value', 'round');
		$result->addField('place', 'field_match_place_value', 'place');
		$result->addField('date', 'field_match_start_date_value', 'date_gmt');
		$result->addField('stadium_node', 'title', 'stadium');
		$result->addField('opponent_logo', 'uri', 'opponent_logo');

		$result->addField('online', 'field_online_translation_value', 'online');
		//$result->addField('referee_node', 'title', 'referee');
		//$result->addField('attendance', 'field_match_attendance_value', 'attendance');
		//$result->addField('result', 'field_match_result_value', 'result');


		$result->condition('n.nid', $match_nid);
		$result = $result->execute()
			->fetchAll();

		if(!empty($result)) {
			foreach($result as &$_row) {
				if(!empty($_row->opponent_logo))
					$_row->opponent_logo = $this->fileUrl($_row->opponent_logo);
					$_row->arsenal_logo = $this->fileUrl('public://arsenal_logo_small.gif');
					$dt = new DateTime($_row->date_gmt, new DateTimeZone('Europe/London'));
					$dt->setTimezone(new DateTimeZone('Europe/Moscow'));
					$_row->date_formatted_msk = $dt->format('d.m.Y G:i').' (мск)';
					$_row->isOnline = (!empty($_row->online));
					unset($_row->online);
			}
		}

		// Get match information that uses leftJoins. It is faster to execute separate queries
		$matchResult = db_select('field_data_field_match_result','r')
			->fields('r',array('field_match_result_value'))
			->condition('entity_id', $match_nid)
			->execute()
			->fetchField();

		$referee = db_select('field_data_field_match_referee','ref');
		$referee->innerJoin('node','referee_node', 'referee_node.nid = ref.field_match_referee_nid');
		$referee->addField('referee_node', 'title');
		$referee = $referee->condition('ref.entity_id', $match_nid)
			->execute()
			->fetchField();

		$attendance = db_select('field_data_field_match_attendance','a')
			->fields('a',array('field_match_attendance_value'))
			->condition('entity_id', $match_nid)
			->execute()
			->fetchField();


		if(is_array($result) && count($result) > 0) {
			$result = array_pop($result);
			if(is_object($result)) {
				$result->result = (!empty($matchResult) ? $matchResult : null);
				$result->referee = (!empty($referee) ? $referee : null);
				$result->attendance = (!empty($attendance) ? $attendance : null);
			}
		}
		return $this->jsonResonse($result);
	}

	function _getMatchStats($match_nid) {
		$matchResult = db_select('field_data_field_match_result','r')
			->fields('r',array('field_match_result_value'))
			->condition('entity_id', $match_nid)
			->execute()
			->fetchField();

		$matchStats = db_select('field_data_field_statistics','r')
			->fields('r',array('field_statistics_value'))
			->condition('entity_id', $match_nid)
			->execute()
			->fetchField();


		$currentMinute = db_select('field_data_field_current_minute','r')
			->fields('r',array('field_current_minute_value'))
			->condition('entity_id', $match_nid)
			->execute()
			->fetchField();

		return array('result' => $matchResult, 'stats' => $matchStats, 'current_minute' => $currentMinute);


	}


	function getMatchSquads($match_nid) {
		if((int)$match_nid <= 0 ){
			return 'Invalid Data';
		}

		$squad = db_select('field_data_field_match_arsenal_squad','squad');
		$squad->innerJoin('node', 'n', 'n.nid = squad.field_match_arsenal_squad_nid');
		$squad->leftJoin('field_data_field_person_number', 'number', 'number.entity_id = n.nid');
		$squad->addField('n', 'title', 'player');
		$squad->addField('n', 'nid', 'player_nid');
		$squad->addField('number', 'field_person_number_value', 'number');

		$squad->condition('squad.entity_id',$match_nid);

		$squad->orderBy('squad.delta','ASC');
		$squad = $squad->execute()->fetchAll();

		$subs = db_select('field_data_field_match_arsenal_squad_subs','subs');
		$subs->innerJoin('node', 'n', 'n.nid = subs.field_match_arsenal_squad_subs_nid');
		$subs->leftJoin('field_data_field_person_number', 'number', 'number.entity_id = n.nid');
		$subs->addField('n', 'title', 'player');
		$subs->addField('n', 'nid', 'player_nid');
		$subs->addField('number', 'field_person_number_value', 'number');

		$subs->condition('subs.entity_id',$match_nid);

		$subs->orderBy('subs.delta','ASC');
		$subs = $subs->execute()->fetchAll();


		$opponentSquad = db_select('field_data_field_match_opponent_squad','squad');
		$opponentSquad->addField('squad', 'field_match_opponent_squad_value', 'squad');
		$opponentSquad->condition('squad.entity_id',$match_nid);
		$opponentSquad = $opponentSquad->execute()->fetchField();
		if(!empty($opponentSquad)) {
			$opponentSquad = preg_split('/(\\r)?\\n/', $opponentSquad);
		}
		if($opponentSquad) {
			$opponentSquad = array_map(function($player) {
				return array('player' => $player);
			}, $opponentSquad);
		}

		$arsenal = (!empty($squad) && !empty($subs) ? array_merge($squad, $subs) : null);
		if($arsenal != null) {
			array_map(function(&$player) {
					$player->link = url('node/'.$player->player_nid, array('absolute'=>true));
			}, $arsenal);
		}

			$return = array(
			'arsenal' => $arsenal,
			'opponent' => (!empty($opponentSquad) ? $opponentSquad : null)
		);

		return $this->jsonResonse($return);

	}

	function _getArsenalEvents($match_nid) {
		$result = db_select('field_data_field_match_events','evts');

		$result->leftJoin('field_data_field_match_event_minute', 'minute', 'minute.entity_id = evts.field_match_events_value');
		$result->leftJoin('field_data_field_match_event_event_type', 'type', 'type.entity_id = evts.field_match_events_value');
		$result->leftJoin('field_data_field_match_event_player', 'player', 'player.entity_id = evts.field_match_events_value');
		$result->innerJoin('node', 'n', 'n.nid = player.field_match_event_player_nid');
		$result->leftJoin('taxonomy_term_data', 'term', 'term.tid = type.field_match_event_event_type_tid');
		$result->leftJoin('field_data_field_match_event_type_code', 'type_term', 'term.tid = type_term.entity_id');
		$result->leftJoin('field_data_field_match_event_type_icon', 'icon_term', 'term.tid = icon_term.entity_id');
		$result->leftJoin('field_data_field_match_event_gamecast_icon', 'gamecast_icon_term', 'term.tid = gamecast_icon_term.entity_id');
		$result->leftJoin('file_managed', 'file_icon', 'file_icon.fid = icon_term.field_match_event_type_icon_fid');
		$result->leftJoin('file_managed', 'gamecast_icon', 'gamecast_icon.fid = gamecast_icon_term.field_match_event_gamecast_icon_fid');

		$result->addField('evts', 'field_match_events_value', 'event_id');
		$result->addField('minute', 'field_match_event_minute_value', 'minute');
		$result->addField('n', 'title', 'player');
		$result->addField('n', 'nid', 'player_nid');
		$result->addField('file_icon', 'uri', 'icon_url');
		$result->addField('gamecast_icon', 'uri', 'gamecast_icon_url');
		$result->addField('type_term', 'field_match_event_type_code_value', 'code');

		$result = $result->condition('evts.entity_id', $match_nid)
			->orderBy('CAST (minute.field_match_event_minute_value AS UNSIGNED)', 'ASC')
			->execute()
			->fetchAll();

			if(!empty($result)) {
				foreach($result as &$_row) {
					$_row->icon_url = $this->fileUrl($_row->icon_url);
					$_row->gamecast_icon_url = $this->fileUrl($_row->gamecast_icon_url);
					$return[] = $_row;
				}
			}
			return $result;

	}

	function _getOpponentEvents($match_nid) {
		$result = db_select('field_data_field_match_events_opponent','evts');

		$result->leftJoin('field_data_field_match_event_opp_minute', 'minute', 'minute.entity_id = evts.field_match_events_opponent_value');
		$result->leftJoin('field_data_field_match_event_opp_event_type', 'type', 'type.entity_id = evts.field_match_events_opponent_value');
		$result->leftJoin('field_data_field_match_event_opp_player', 'player', 'player.entity_id = evts.field_match_events_opponent_value');
		$result->leftJoin('taxonomy_term_data', 'term', 'term.tid = type.field_match_event_opp_event_type_tid');
		$result->leftJoin('field_data_field_match_event_type_code', 'type_term', 'term.tid = type_term.entity_id');
		$result->leftJoin('field_data_field_match_event_type_icon', 'icon_term', 'term.tid = icon_term.entity_id');
		$result->leftJoin('field_data_field_match_event_gamecast_icon', 'gamecast_icon_term', 'term.tid = gamecast_icon_term.entity_id');
		$result->leftJoin('file_managed', 'file_icon', 'file_icon.fid = icon_term.field_match_event_type_icon_fid');
		$result->leftJoin('file_managed', 'gamecast_icon', 'gamecast_icon.fid = gamecast_icon_term.field_match_event_gamecast_icon_fid');


		$result->addField('evts', 'field_match_events_opponent_value', 'event_id');
		$result->addField('minute', 'field_match_event_opp_minute_value', 'minute');
		$result->addField('player', 'field_match_event_opp_player_value', 'player');
		$result->addField('file_icon', 'uri', 'icon_url');
		$result->addField('gamecast_icon', 'uri', 'gamecast_icon_url');
		$result->addField('type_term', 'field_match_event_type_code_value', 'code');

		$result = $result->condition('evts.entity_id', $match_nid)
			->orderBy('CAST(minute AS UNSIGNED)', 'ASC')
			->execute()
			->fetchAll();

		if(!empty($result)) {
			foreach($result as &$_row) {
				$_row->icon_url = $this->fileUrl($_row->icon_url);
				$_row->gamecast_icon_url = $this->fileUrl($_row->gamecast_icon_url);
				$return[] = $_row;
			}
		}
		return $result;

	}

}