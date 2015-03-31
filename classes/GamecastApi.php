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
		$result->leftJoin('field_data_field_match_event_type_icon', 'icon_term', 'term.tid = icon_term.entity_id');

		$result->leftJoin('file_managed', 'file_photo', 'file_photo.fid = photo.field_gamecast_event_photo_fid');
		$result->leftJoin('file_managed', 'file_video', 'file_video.fid = video.field_gamecast_event_video_fid');
		$result->leftJoin('file_managed', 'file_icon', 'file_icon.fid = icon_term.field_match_event_type_icon_fid');

		$result->addField('evts', 'field_gamecast_events_value', 'event_id');
		$result->addField('minute', 'field_gamecast_event_minute_value', 'minute');
		//$result = $result->fields('term', array('field_gamecast_event_minute_value'));
		$result->addField('text', 'field_gamecast_event_text_value', 'text');
		$result->addField('file_photo', 'uri', 'photo_url');
		$result->addField('file_video', 'uri', 'video_url');
		$result->addField('type_term', 'field_match_event_type_code_value', 'code');
		$result->addField('file_icon', 'uri', 'icon_url');

		$result = $result->condition('m.field_gamecast_match_nid', $match_nid)
			->orderBy('minute.field_gamecast_event_minute_value', 'DESC')
			->execute()
			->fetchAllAssoc('event_id');

		if(!empty($result)) {
			foreach($result as &$_row) {
				$_row->photo_url = $this->fileUrl($_row->photo_url);
				$_row->icon_url = $this->fileUrl($_row->icon_url);
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
		$return = array();
		$return['arsenal'] = $this->_getArsenalEvents($match_nid);
		$return['opponent'] = $this->_getOpponentEvents($match_nid);


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
		$result->leftJoin('file_managed', 'file_icon', 'file_icon.fid = icon_term.field_match_event_type_icon_fid');

		$result->addField('evts', 'field_match_events_value', 'event_id');
		$result->addField('minute', 'field_match_event_minute_value', 'minute');
		$result->addField('n', 'title', 'player');
		$result->addField('file_icon', 'uri', 'icon_url');
		$result->addField('type_term', 'field_match_event_type_code_value', 'code');

		$result = $result->condition('evts.entity_id', $match_nid)
			->orderBy('minute.field_match_event_minute_value', 'ASC')
			->execute()
			->fetchAll();

			if(!empty($result)) {
				foreach($result as &$_row) {
					$_row->icon_url = $this->fileUrl($_row->icon_url);
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
		$result->leftJoin('file_managed', 'file_icon', 'file_icon.fid = icon_term.field_match_event_type_icon_fid');

		$result->addField('evts', 'field_match_events_opponent_value', 'event_id');
		$result->addField('minute', 'field_match_event_opp_minute_value', 'minute');
		$result->addField('player', 'field_match_event_opp_player_value', 'player');
		$result->addField('file_icon', 'uri', 'icon_url');
		$result->addField('type_term', 'field_match_event_type_code_value', 'code');

		$result = $result->condition('evts.entity_id', $match_nid)
			->orderBy('minute', 'ASC')
			->execute()
			->fetchAll();

		if(!empty($result)) {
			foreach($result as &$_row) {
				$_row->icon_url = $this->fileUrl($_row->icon_url);
				$return[] = $_row;
			}
		}
		return $result;

	}

}